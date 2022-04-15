<?php
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);

$websites = require_once 'my_stock.php';

$mmmmm = date('m');
if (isset($_GET['m'])) {
    $mmmmm = $_GET['m'];
}

$huizong = array();
foreach ($websites as $temp) {
    list ($url, $zh_name) = $temp;
    $stock_id = str_replace(array('http://stockdata.stock.hexun.com/zlkp/s', '.shtml'), '', $url);
    $stock_name = $zh_name;
    
    $huizong[$stock_id][] = $stock_name;

    $data = [];
    if (file_exists("$stock_id.txt")) {
        $h = fopen("$stock_id.txt", 'r');
        while(!feof($h)) {
            $line = fgets($h);

            if (strlen($line) > 0) {
                if ($line{0} == 2) {
                    $aaaa = explode(', ', $line);
                    if (count($aaaa) == 16) {
                        $data[] = $aaaa;
                    }
                }
            }
        }
        fclose($h);
    }
    
    $zhulikongpanData = [];
    
    $appeared = array();
    foreach ($data as $d) {
        list ($date, $kp, $lookkp, $zhulichengben, $currentPrice, $zhang_die_fu, $zuo_shou, $jin_kai, $zui_gao, $zui_di, $cheng_jiao_liang, $cheng_jiao_e, $huan_shou_percent, $zhen_fu_percent, $liang_bi, $runTime) = $d;
        if (isset($appeared[$date])) {
            continue;
        }
        if (substr($date, 0, 4) != date('Y')) {
            continue;
        }
        if (substr($date, 5, 2) != $mmmmm) {
            continue;
        }
        
        if ($kp == 1) {
            $zhulikongpanData[] = substr($date, 0) . ' <span style="color:#FFE6E6">微弱</span>';
        } else if ($kp == 2) {
            $zhulikongpanData[] = substr($date, 0) . ' <span style="color:#FFDBDB">轻度</span>';
        } else if ($kp == 3) {
            $zhulikongpanData[] = substr($date, 0) . ' <span style="color:#FFCDCD">中度</span>';
        } else if ($kp == 4) {
            $zhulikongpanData[] = substr($date, 0) . ' <span style="color:#FFACAC">高度</span>';
        } else if ($kp == 5) {
            $zhulikongpanData[] = substr($date, 0) . ' <span style="color:#FF9595">强烈</span>';
        }
    }
    
    $zhulikongpanData = join('<br>', $zhulikongpanData);
    
    $huizong[$stock_id][] = $zhulikongpanData;
}

echo <<<HTML
<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>汇总</title>
</head>
<body>
HTML;

echo "<table border='1'>";
foreach ($huizong as $stock_id => $arr) {
    echo "<tr>";
    echo "<td>{$arr[0]}</td>";
    echo "<td>{$arr[1]}</td>";
    echo "</tr>";
}
echo "</table></body></html>";
