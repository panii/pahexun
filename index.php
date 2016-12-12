<?php
error_reporting(E_ALL);
date_default_timezone_set('Asia/Shanghai');
set_time_limit(0);

$websites = require_once 'my_stock.php';

if (!isset($_GET['id'])) {
    foreach ($websites as $temp) {
        list ($url, $zh_name) = $temp;
        $stock_id = str_replace(array('http://stockdata.stock.hexun.com/zlkp/s', '.shtml'), '', $url);
        
        $a[] = "<a href='/index.php?id=$stock_id'>$zh_name ($stock_id)</a>";
    }
    $a = join("<br><br>", $a) . '<br><br><br><br><br><br>';
    echo <<<HTML
<!DOCTYPE html>
<html id="htmltag" >
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>list</title>
</head>
<body>
$a
</body>
</html>
HTML;
} else {
    $stock_id = $_GET['id'];
    foreach ($websites as $temp) {
            list ($url, $zh_name) = $temp;
            $stock_id2 = str_replace(array('http://stockdata.stock.hexun.com/zlkp/s', '.shtml'), '', $url);
            
            if ($stock_id2 == $stock_id) {
                $stock_name = $zh_name;
            }
        }
        
        if (!isset($stock_name)) {
            echo <<<HTML
<!DOCTYPE html>
<html id="htmltag" >
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>detail $stock_id</title>
</head>
<body>
配置不存在
</body>
</html>
HTML;
            exit;
    }
    if (!file_exists("$stock_id.txt")) {
        echo <<<HTML
<!DOCTYPE html>
<html id="htmltag" >
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>Detail $stock_id</title>
</head>
<body>
数据不存在
</body>
</html>
HTML;
    } else {
        $data = [];
        $h = fopen("$stock_id.txt", 'r');
        while(!feof($h)) {
            $line = fgets($h);

            if (strlen($line) > 0) {
                if ($line{0} == 2) {
                    $data[] = explode(', ', $line);
                }
            }
        }
        fclose($h);
        
        $dateData = [];
        $kData = [];
        $chengjiaoeData = [];
        $chengjiaoliangData = [];
        $zhulikongpanData = [];
        $zhulichengbenData = [];
        $huanshouData = [];
        $zhenfuData = [];
        $liangbiData = [];
        
        $appeared = array();
        foreach ($data as $d) {
            list ($date, $kp, $lookkp, $zhulichengben, $currentPrice, $zhang_die_fu, $zuo_shou, $jin_kai, $zui_gao, $zui_di, $cheng_jiao_liang, $cheng_jiao_e, $huan_shou_percent, $zhen_fu_percent, $liang_bi, $runTime) = $d;
            if (isset($appeared[$date])) {
                continue;
            }
            $appeared[$date] = true;
            $dateData[] = '"' . str_replace('-' , '/', $date) . '"';
            $kData[] = "[$jin_kai, $currentPrice, $zui_di, $zui_gao]";
            $chengjiaoeData[] = $cheng_jiao_e;
            $chengjiaoliangData[] = $cheng_jiao_liang;
            $zhulikongpanData[] = $kp;
            $zhulichengbenData[] = $zhulichengben;
            $huanshouData[] = $huan_shou_percent;
            $zhenfuData[] = $zhen_fu_percent;
            $liangbiData[] = $liang_bi;
        }
        
        $kData = join(',', $kData);
        $dateData = join(',', $dateData);
        $chengjiaoeData = join(',', $chengjiaoeData);
        $chengjiaoliangData = join(',', $chengjiaoliangData);
        $zhulikongpanData = join(',', $zhulikongpanData);
        $zhulichengbenData = join(',', $zhulichengbenData);
        $huanshouData = join(',', $huanshouData);
        $zhenfuData = join(',', $zhenfuData);
        $liangbiData = join(',', $liangbiData);

        $dataZoomStart = 0;
        
        echo <<<HTML
<!DOCTYPE html>
<head>
    <meta charset="utf-8">
    <title>Detail $stock_id $stock_name</title>
</head>
<body>
    <div id="KKK" class="main" style='height:230px;margin-bottom:1px;padding-bottom:0;border-bottom-width:0'></div>
    <div id="CHENGJIAOE" class="main" style='height:105px;padding-top:1px;border-top-width:0'></div>
    <div id="CHENGJIAOLIANG" class="main" style='height:105px;padding-top:1px;border-top-width:0'></div>
    <div id="ZHULIKONGPAN" class="main" style='height:100px;padding-top:1px;border-top-width:0'></div>
    <div id="ZHULICHENGBEN" class="main" style='height:90px;padding-top:1px;border-top-width:0'></div>
    <div id="HUANSHOU" class="main" style='height:80px;padding-top:1px;border-top-width:0'></div>
    <div id="ZHENFU" class="main" style='height:80px;padding-top:1px;border-top-width:0'></div>
    <div id="LIANGBI" class="main" style='height:80px;padding-top:1px;border-top-width:0'></div>
    <script src="http://echarts.baidu.com/build/dist/echarts.js"></script>
    <script type="text/javascript">
        require.config({
            paths: {
                echarts: 'http://echarts.baidu.com/build/dist'
            }
        });
        
        require(
            [
                'echarts',
                'echarts/chart/line',
                'echarts/chart/bar',
                'echarts/chart/k',
            ],
            function (echarts) {                
var axisData = [
    $dateData
];

Legend = ['K线图','成交金额(万)','成交量','主力控盘','主力成本','换手','振幅','量比'];

option_k = {
    title : {
        text: '$stock_name'
    },
    tooltip : {
        trigger: 'axis',
        showDelay: 100,
        formatter: function (params) {
            var res = params[0].name;
            res += '<br/>' + params[0].seriesName;
            res += '<br/>  开盘 : ' + params[0].value[0] + '  最高 : ' + params[0].value[3];
            res += '<br/>  收盘 : ' + params[0].value[1] + '  最低 : ' + params[0].value[2];
            return res;
        }
    },
    legend: {
        data:Legend
    },
    toolbox: {
        show : true,
        feature : {
            // mark : {show: true},
            // dataZoom : {show: true},
            magicType : {show: true, type: ['line', 'bar']}
            // restore : {show: true},
            // saveAsImage : {show: true}
        }
    },
    dataZoom : {
        y: 250,
        show : true,
        realtime: true,
        start : $dataZoomStart,
        end : 100
    },
    grid: {
        x: 80,
        y: 40,
        x2:20,
        y2:25
    },
    xAxis : [
        {
            type : 'category',
            boundaryGap : true,
            axisTick: {onGap:false},
            splitLine: {show:false},
            data : axisData
        }
    ],
    yAxis : [
        {
            type : 'value',
            scale:true,
            boundaryGap: [0.05, 0.05],
            splitArea : {show : true}
        }
    ],
    series : [
        {
            name:'$stock_name',
            type:'k',
            data:[ // 开盘，收盘，最低，最高
                $kData
            ]
        },
        {
            name:'成交金额(万)',
            type:'bar',
            symbol: 'none',
            data:[]
        },
        {
            name:'成交量',
            type:'bar',
            data:[]
        },
        {
            name:'主力控盘',
            type:'bar',
            data:[]
        },
        {
            name:'主力成本',
            type:'line',
            data:[]
        },
        {
            name:'换手',
            type:'bar',
            data:[]
        },
        {
            name:'振幅',
            type:'bar',
            data:[]
        },
        {
            name:'量比',
            type:'line',
            data:[]
        }
    ]
};
kChart = echarts.init(document.getElementById("KKK"));
kChart.setOption(option_k, true);

option_chengjiaoe = {
    tooltip : {
        trigger: 'axis',
        showDelay: 100,
        formatter: function (params) {
                    var res = params[0].name;
                    res += '<br/>' + params[0].seriesName;
                    res += '<br/>' + Math.round(params[0].value/10000);
                    return res;
                }
    },
    legend: {
        y : -30,
        data:Legend
    },
    toolbox: {
        y : -30,
        show : true,
        feature : {
            // mark : {show: true},
            // dataZoom : {show: true},
            // dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']}
            // restore : {show: true},
            // saveAsImage : {show: true}
        }
    },
    dataZoom : {
        show : true,
        realtime: true,
        start : $dataZoomStart,
        end : 100
    },
    grid: {
        x: 80,
        y:5,
        x2:20,
        y2:40
    },
    xAxis : [
        {
            type : 'category',
            position:'top',
            boundaryGap : true,
            axisLabel:{show:false},
            axisTick: {onGap:false},
            splitLine: {show:false},
            data : axisData
        }
    ],
    yAxis : [
        {
            type : 'value',
            scale:true,
            splitNumber: 3,
            boundaryGap: [0.05, 0.05],
            axisLabel: {
                formatter: function (v) {
                    return Math.round(v/10000) + ' 万'
                }
            },
            splitArea : {show : true}
        }
    ],
    series : [
        {
            name:'成交金额(万)',
            type:'bar',
            symbol: 'none',
            data:[$chengjiaoeData],
            markLine : {
                symbol : 'none',
                itemStyle : {
                    normal : {
                        color:'#1e90ff',
                        label : {
                            show:false
                        }
                    }
                },
                data : [
                    {type : 'average', name: '平均值'}
                ]
            }
        }
    ]
};

chengjiaoeChart = echarts.init(document.getElementById('CHENGJIAOE'));
chengjiaoeChart.setOption(option_chengjiaoe);

option_chengjiaoliang = {
    tooltip : {
        trigger: 'axis',
        showDelay: 100
    },
    legend: {
        y : -30,
        data:Legend
    },
    toolbox: {
        y : -30,
        show : true,
        feature : {
            // mark : {show: true},
            // dataZoom : {show: true},
            // dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']}
            // restore : {show: true},
            // saveAsImage : {show: true}
        }
    },
    dataZoom : {
        y:200,
        show : true,
        realtime: true,
        start : $dataZoomStart,
        end : 100
    },
    grid: {
        x: 80,
        y:5,
        x2:20,
        y2:30
    },
    xAxis : [
        {
            type : 'category',
            position:'bottom',
            boundaryGap : true,
            axisTick: {onGap:false},
            splitLine: {show:false},
            data : axisData
        }
    ],
    yAxis : [
        {
            type : 'value',
            scale:true,
            splitNumber:3,
            boundaryGap: [0.05, 0.05],
            splitArea : {show : true}
        }
    ],
    series : [
        {
            name:'成交量',
            type:'bar',
            symbol: 'none',
            data:[$chengjiaoliangData],
            markLine : {
                symbol : 'none',
                itemStyle : {
                    normal : {
                        // color:'#1e90ff',
                        label : {
                            show:false
                        }
                    }
                },
                data : [
                    {type : 'average', name: '平均值'}
                ]
            }
        }
    ]
};

chengjiaoliangChart = echarts.init(document.getElementById('CHENGJIAOLIANG'));
chengjiaoliangChart.setOption(option_chengjiaoliang);

option_zhulikongpan = {
    tooltip : {
        trigger: 'axis',
        showDelay: 100,
        formatter: function (params) {
                    var res = params[0].name;
                    res += '<br/>' + params[0].seriesName;
                    if (params[0].value == 1) {
                        res += '<br/>' + '微弱';
                    } else if (params[0].value == 2) {
                        res += '<br/>' + '轻度';
                    } else if (params[0].value == 3) {
                        res += '<br/>' + '中度';
                    } else if (params[0].value == 4) {
                        res += '<br/>' + '高度';
                    } else if (params[0].value == 5) {
                        res += '<br/>' + '强烈';
                    }
                    return res;
                }
    },
    legend: {
        y : -30,
        data:Legend
    },
    toolbox: {
        y : -30,
        show : true,
        feature : {
            // mark : {show: true},
            // dataZoom : {show: true},
            // dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']}
            // restore : {show: true},
            // saveAsImage : {show: true}
        }
    },
    dataZoom : {
        y:200,
        show : true,
        realtime: true,
        start : $dataZoomStart,
        end : 100
    },
    grid: {
        x: 80,
        y:5,
        x2:20,
        y2:30
    },
    xAxis : [
        {
            type : 'category',
            position:'bottom',
            boundaryGap : true,
            axisTick: {onGap:false},
            splitLine: {show:false},
            data : axisData
        }
    ],
    yAxis : [
        {
            type : 'value',
            scale:true,
            splitNumber:3,
            boundaryGap: [0.05, 0.05],
            splitArea : {show : true}
        }
    ],
    series : [
        {
            name:'主力控盘',
            type:'bar',
            symbol: 'none',
            data:[$zhulikongpanData]
        }
    ]
};

zhulikongpanChart = echarts.init(document.getElementById('ZHULIKONGPAN'));
zhulikongpanChart.setOption(option_zhulikongpan);

option_zhulichengben = {
    tooltip : {
        trigger: 'axis',
        showDelay: 100
    },
    legend: {
        y : -30,
        data:Legend
    },
    toolbox: {
        y : -30,
        show : true,
        feature : {
            // mark : {show: true},
            // dataZoom : {show: true},
            // dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']}
            // restore : {show: true},
            // saveAsImage : {show: true}
        }
    },
    dataZoom : {
        y:200,
        show : true,
        realtime: true,
        start : $dataZoomStart,
        end : 100
    },
    grid: {
        x: 80,
        y:5,
        x2:20,
        y2:30
    },
    xAxis : [
        {
            type : 'category',
            position:'bottom',
            boundaryGap : true,
            axisTick: {onGap:false},
            splitLine: {show:false},
            data : axisData
        }
    ],
    yAxis : [
        {
            type : 'value',
            scale:true,
            splitNumber:3,
            boundaryGap: [0.05, 0.05],
            splitArea : {show : true}
        }
    ],
    series : [
        {
            name:'主力成本',
            type:'line',
            symbol: 'none',
            data:[$zhulichengbenData],
            markLine : {
                symbol : 'none',
                itemStyle : {
                    normal : {
                        // color:'#1e90ff',
                        label : {
                            show:false
                        }
                    }
                },
                data : [
                    {type : 'average', name: '平均值'}
                ]
            }
        }
    ]
};

zhulichengbenChart = echarts.init(document.getElementById('ZHULICHENGBEN'));
zhulichengbenChart.setOption(option_zhulichengben);

option_huanshou = {
    tooltip : {
        trigger: 'axis',
        showDelay: 100
    },
    legend: {
        y : -30,
        data:Legend
    },
    toolbox: {
        y : -30,
        show : true,
        feature : {
            // mark : {show: true},
            // dataZoom : {show: true},
            // dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']}
            // restore : {show: true},
            // saveAsImage : {show: true}
        }
    },
    dataZoom : {
        y:200,
        show : true,
        realtime: true,
        start : $dataZoomStart,
        end : 100
    },
    grid: {
        x: 80,
        y:5,
        x2:20,
        y2:30
    },
    xAxis : [
        {
            type : 'category',
            position:'bottom',
            boundaryGap : true,
            axisTick: {onGap:false},
            splitLine: {show:false},
            data : axisData
        }
    ],
    yAxis : [
        {
            type : 'value',
            scale:true,
            splitNumber:3,
            boundaryGap: [0.05, 0.05],
            splitArea : {show : true}
        }
    ],
    series : [
        {
            name:'换手',
            type:'bar',
            symbol: 'none',
            data:[$huanshouData]
        }
    ]
};

huanshouChart = echarts.init(document.getElementById('HUANSHOU'));
huanshouChart.setOption(option_huanshou);

option_zhenfu = {
    tooltip : {
        trigger: 'axis',
        showDelay: 100
    },
    legend: {
        y : -30,
        data:Legend
    },
    toolbox: {
        y : -30,
        show : true,
        feature : {
            // mark : {show: true},
            // dataZoom : {show: true},
            // dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']}
            // restore : {show: true},
            // saveAsImage : {show: true}
        }
    },
    dataZoom : {
        y:200,
        show : true,
        realtime: true,
        start : $dataZoomStart,
        end : 100
    },
    grid: {
        x: 80,
        y:5,
        x2:20,
        y2:30
    },
    xAxis : [
        {
            type : 'category',
            position:'bottom',
            boundaryGap : true,
            axisTick: {onGap:false},
            splitLine: {show:false},
            data : axisData
        }
    ],
    yAxis : [
        {
            type : 'value',
            scale:true,
            splitNumber:3,
            boundaryGap: [0.05, 0.05],
            splitArea : {show : true}
        }
    ],
    series : [
        {
            name:'振幅',
            type:'bar',
            symbol: 'none',
            data:[$zhenfuData]
        }
    ]
};

zhenfuChart = echarts.init(document.getElementById('ZHENFU'));
zhenfuChart.setOption(option_zhenfu);

option_liangbi = {
    tooltip : {
        trigger: 'axis',
        showDelay: 100
    },
    legend: {
        y : -30,
        data:Legend
    },
    toolbox: {
        y : -30,
        show : true,
        feature : {
            // mark : {show: true},
            // dataZoom : {show: true},
            // dataView : {show: true, readOnly: false},
            magicType : {show: true, type: ['line', 'bar']}
            // restore : {show: true},
            // saveAsImage : {show: true}
        }
    },
    dataZoom : {
        y:200,
        show : true,
        realtime: true,
        start : $dataZoomStart,
        end : 100
    },
    grid: {
        x: 80,
        y:5,
        x2:20,
        y2:30
    },
    xAxis : [
        {
            type : 'category',
            position:'bottom',
            boundaryGap : true,
            axisTick: {onGap:false},
            splitLine: {show:false},
            data : axisData
        }
    ],
    yAxis : [
        {
            type : 'value',
            scale:true,
            splitNumber:3,
            boundaryGap: [0.05, 0.05],
            splitArea : {show : true}
        }
    ],
    series : [
        {
            name:'量比',
            type:'line',
            symbol: 'none',
            data:[$liangbiData]
        }
    ]
};

liangbiChart = echarts.init(document.getElementById('LIANGBI'));
liangbiChart.setOption(option_liangbi);



kChart.connect([chengjiaoliangChart, zhulikongpanChart, zhulichengbenChart, chengjiaoeChart, huanshouChart, zhenfuChart, liangbiChart]);
chengjiaoliangChart.connect([kChart, zhulikongpanChart, zhulichengbenChart, chengjiaoeChart, huanshouChart, zhenfuChart, liangbiChart]);
zhulikongpanChart.connect([chengjiaoliangChart, kChart, zhulichengbenChart, chengjiaoeChart, huanshouChart, zhenfuChart, liangbiChart]);
zhulichengbenChart.connect([chengjiaoliangChart, zhulikongpanChart, kChart, chengjiaoeChart, huanshouChart, zhenfuChart, liangbiChart]);
chengjiaoeChart.connect([chengjiaoliangChart, zhulikongpanChart, zhulichengbenChart, kChart, huanshouChart, zhenfuChart, liangbiChart]);
huanshouChart.connect([chengjiaoliangChart, zhulikongpanChart, zhulichengbenChart, chengjiaoeChart, kChart, zhenfuChart, liangbiChart]);
zhenfuChart.connect([chengjiaoliangChart, zhulikongpanChart, zhulichengbenChart, chengjiaoeChart, huanshouChart, kChart, liangbiChart]);
liangbiChart.connect([chengjiaoliangChart, zhulikongpanChart, zhulichengbenChart, chengjiaoeChart, huanshouChart, zhenfuChart, kChart]);


setTimeout(function (){
    window.onresize = function () {
        kChart.resize();
        chengjiaoliangChart.resize();
        zhulikongpanChart.resize();
        zhulichengbenChart.resize();
        chengjiaoeChart.resize();
        huanshouChart.resize();
        zhenfuChart.resize();
        liangbiChart.resize();
    }
},200)
            }
        );
    </script>
</body>
HTML;
    }
}