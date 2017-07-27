<?php
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);

// 星期二 星期三 星期四 星期五 星期六 早上8点爬一下

$xingqi = date('w', time());
if ($xingqi == 0 || $xingqi == 1) {
    exit;
}

$websites = require_once __DIR__ . '/my_stock.php';

$ti = array('最新交易日', '</span>&nbsp;&nbsp; 主力成本', '年', '月', '日', '&nbsp;&nbsp;该股为<span class="f_red">', '元');
foreach ($ti as &$v) {
    $v = iconv("UTF-8", "GB2312//IGNORE", $v);
}
$weiruokongpan = iconv("UTF-8", "GB2312//IGNORE", '微弱控盘');
$qingdukongpan = iconv("UTF-8", "GB2312//IGNORE", '轻度控盘');
$zhongdukongpan = iconv("UTF-8", "GB2312//IGNORE", '中度控盘');
$gaodukongpan = iconv("UTF-8", "GB2312//IGNORE", '高度控盘');
$qiangliekongpan = iconv("UTF-8", "GB2312//IGNORE", '强烈控盘');

foreach ($websites as $temp) {
    list ($url, $zh_name) = $temp;
    list($exec_time_usec_1, $exec_time_sec_1) = explode(' ', microtime());
    $php_exec_time_start = ((float)$exec_time_usec_1 + (float)$exec_time_sec_1);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36");
    $contents = curl_exec($ch);
    curl_close($ch);

    $start = strpos($contents, 'text_01') + 9;
    $end = strpos($contents, '</p>', $start);
    $line = substr($contents, $start, $end - $start);
    $line2 = str_replace($ti, ' ', $line);

    $a = explode(' ', $line2);
    if (count($a) < 8) {
        continue;
    }

    list($_, $year, $month, $date, $empty, $kongpan, $zhulichengben, $empty2) = $a;
    
    if ($kongpan == $weiruokongpan) {
        $kp = 1;
        $lookkp = 'wei_ruo';
    } elseif ($kongpan == $qingdukongpan) {
        $kp = 2;
        $lookkp = 'qing_du';
    } elseif ($kongpan == $zhongdukongpan) {
        $kp = 3;
        $lookkp = 'zhong_du';
    } elseif ($kongpan == $gaodukongpan) {
        $kp = 4;
        $lookkp = 'gao_du';
    } elseif ($kongpan == $qiangliekongpan) {
        $kp = 5;
        $lookkp = 'qiang_lie';
    } else {
        continue;
    }

    $stock_id = str_replace(array('http://stockdata.stock.hexun.com/zlkp/s', '.shtml'), '', $url);

    if (file_exists(__DIR__ . "/{$stock_id}.txt")) {
        $oldContents = file_get_contents(__DIR__ . "/{$stock_id}.txt");
        if (strpos($oldContents, "$year-$month-$date") !== false) {
            continue;
        }
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://quote.stock.hexun.com/stockdata/stock_quote.aspx?stocklist=' . $stock_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Safari/537.36");
    $contents = curl_exec($ch);
    curl_close($ch);
    // $contents = "dataArr = [['000002','万 科Ａ',14.50,1.97,14.22,14.09,14.54,14.03,999341.49,1430580813,1.03,3.59,0.73]];indexdataArr = [];NewQuoteListPage.GetData(dataArr,indexdataArr);"
    $contents2 = explode(']]', str_replace(array("dataArr = [[", "'"), '', $contents))[0];
    list($id, $name, $currentPrice, $zhang_die_fu, $zuo_shou, $jin_kai, $zui_gao, $zui_di, $cheng_jiao_liang, $cheng_jiao_e, $huan_shou_percent, $zhen_fu_percent, $liang_bi) = explode(',', $contents2);
    
    list($exec_time_usec_2, $exec_time_sec_2) = explode(' ', microtime());
    $exec_time_end = ((float)$exec_time_usec_2 + (float)$exec_time_sec_2);
    $runTime = round(($exec_time_end - $php_exec_time_start) * 1000, 5);
    file_put_contents(__DIR__ . "/{$stock_id}.txt", "$year-$month-$date, $kp, $lookkp, $zhulichengben, $currentPrice, $zhang_die_fu, $zuo_shou, $jin_kai, $zui_gao, $zui_di, $cheng_jiao_liang, $cheng_jiao_e, $huan_shou_percent, $zhen_fu_percent, $liang_bi, $runTime\r\n", 8);

    file_put_contents(__DIR__ . '/log.txt', "/{$stock_id}.txt" . "\r\n", 8);
    
    // wait for 0.2 seconds
    usleep(200000);
}

exit;