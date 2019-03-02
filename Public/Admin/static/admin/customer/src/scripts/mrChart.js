/**
 * @team Yundun FET
 * @author shiliuping@yundun.com
 * @latest 2016-11-8
 * @operator shiliuping@yundun.com
 */

/******************* 初始化数据模板 *******************/
var mrChart = {}; //月报Chart对象
var dateArr = dateLabelsFn(start_time, end_time);

// Chart数据模板
var datasetsTmp = {
    data: [], //Chart数据
    label: 'Tip',
    fill: true,
    lineTension: 0.5,
    borderWidth: 2,
    borderCapStyle: 'butt',
    borderDash: [],
    borderDashOffset: 0.0,
    borderJoinStyle: 'miter',
    pointBackgroundColor: '#fff',
    pointBorderWidth: 0.5,
    pointRadius: 3,
    pointHoverRadius: 1.5,
    pointHitRadius: 4,
    pointHoverBorderWidth: 0.5,
    spanGaps: false,
    borderColor: 'rgba(50, 152, 226, 1)',
    backgroundColor: 'rgba(66, 175, 236, 0.3)',
    pointBorderColor: 'rgba(75,192,192,1)',
    pointHoverBackgroundColor: 'rgba(75,192,192,1)',
    pointHoverBorderColor: 'rgba(220,220,220,1)'
};
// Chart总模板
var dataTmp = {
    labels: dateArr, //Chart横坐标值
    datasets: [datasetsTmp]
};

// 配置纵坐标模板
var yAxesTmp = {
    scaleLabel: {
        display: true,
        labelString: '单位'
    },
    ticks: {
        max: 100000,
        min: 0,
        stepSize: 20000,
        beginAtZero: true,
        maxTicksLimit: 5
    }
};
// 配置总模板
var optionsTmp = {
    title: {
        display: false,
        text: '报表的标题'
    },
    legend: {
        display: true
    },
    tooltips: {
        mode: 'label',
        callbacks: {}
    },
    scales: {
        yAxes: [yAxesTmp],
        xAxes: [{
            ticks: {
                maxTicksLimit: 6 //横坐标间隔
            }
        }]
    }
};

// Chart 配置文件模板
var cfgTmp = {
    type: 'line',
    data: dataTmp,
    options: optionsTmp
};

/******************* 公共方法 *******************/
//自定义 时间数组和初始数据
function dateLabelsFn(start_time, end_time) {
    var date = new Date(start_time.split('.').join('-'));
    var last_date = new Date(end_time.split('.').join('-'));

    var year = date.getFullYear();
    var month = date.getMonth() + 1;

    var tmpArr = [];

    var day;
    for (var i = 1; i <= last_date.getDate(); i++) {
        if (i < 10) {
            day = '0' + i;
        } else {
            day = i;
        }
        tmpArr.push(year + "-" + month + "-" + day);
    }

    return tmpArr;
}

//获取格式化后的最大值和单位和步数大小
function formatnumberFn(max_values) {
    var max = 0;
    var format_max = {};
    if (typeof max_values !== 'string') max_values = '' + max_values;
    if (max_values.indexOf('次') != -1) {
        if (max_values.indexOf('亿次') != -1) {
            max = max_values.substring(0, max_values.indexOf('亿次'));
            format_max = formatmaxByNumFn(max, '亿次');
        } else if (max_values.indexOf('万次') != -1) {
            max = max_values.substring(0, max_values.indexOf('万次'));
            format_max = formatmaxByNumFn(max, '万次');
        } else {
            max = max_values.substring(0, max_values.indexOf('次'));
            format_max = formatmaxByNumFn(max, '次');
        }
    } else {
        format_max = formatmaxByNumFn(max_values, '次');
    }
    return format_max;
}

// 处理最大值 返回单位和步数
function formatmaxByNumFn(max, position) {
    var last_max = 0;
    positionStr = position;
    if (max > 10 && max <= 100) {
        last_max = Math.round(max) % 20;
        if (last_max == 0) {
            max = Math.round(max) + 20;
        } else {
            max = Math.round(max) + (20 - last_max);
        }
    } else if (max > 100 && max <= 1000) {
        last_max = Math.round(max) % 200;
        if (last_max == 0) {
            max = Math.round(max) + 200;
        } else {
            max = Math.round(max) + (200 - last_max);
        }
    } else if (max > 1000 && max <= 10000) {
        last_max = Math.round(max) % 2000;
        if (last_max == 0) {
            max = Math.round(max) + 2000;
        } else {
            max = Math.round(max) + (2000 - last_max);
        }
    } else if (max > 10000 && max <= 100000) {
        last_max = Math.round(max) % 20000;
        if (last_max == 0) {
            max = Math.round(max) + 20000;
        } else {
            max = Math.round(max) + (20000 - last_max);
        }
    } else if (max > 100000 && max <= 1000000) {
        last_max = Math.round(max) % 200000;
        if (last_max == 0) {
            max = Math.round(max) + 200000;
        } else {
            max = Math.round(max) + (200000 - last_max);
        }
    }  else if (max > 1000000 && max <= 2000000) {
        last_max = Math.round(max) % 400000;
        if (last_max == 0) {
            max = Math.round(max) + 400000;
        } else {
            max = Math.round(max) + (400000 - last_max);
        }
    }  else if (max > 3000000 && max <= 4000000) {
        last_max = Math.round(max) % 800000;
        if (last_max == 0) {
            max = Math.round(max) + 800000;
        } else {
            max = Math.round(max) + (800000 - last_max);
        }
    }  else if (max > 4000000 && max <= 5000000) {
        last_max = Math.round(max) % 1000000;
        if (last_max == 0) {
            max = Math.round(max) + 1000000;
        } else {
            max = Math.round(max) + (1000000 - last_max);
        }
    }  else if (max > 5000000 && max <= 6000000) {
        last_max = Math.round(max) % 1200000;
        if (last_max == 0) {
            max = Math.round(max) + 1200000;
        } else {
            max = Math.round(max) + (1200000 - last_max);
        }
    }  else if (max > 6000000 && max <= 7000000) {
        last_max = Math.round(max) % 1400000;
        if (last_max == 0) {
            max = Math.round(max) + 1400000;
        } else {
            max = Math.round(max) + (1400000 - last_max);
        }
    } else if (max > 7000000 && max <= 8000000) {
        last_max = Math.round(max) % 1600000;
        if (last_max == 0) {
            max = Math.round(max) + 1600000;
        } else {
            max = Math.round(max) + (1600000 - last_max);
        }
    }else {
        if (position.toString() == '亿次') {
            last_max = (Math.round(max) * 10000) % 2000;
            if (last_max == 0) {
                max = Math.round(max * 10000) + 2000;
            } else {
                max = Math.round(max * 10000) + (2000 - last_max);
            }
            positionStr = '万次';
        } else if (position.toString() == '万次') {
            last_max = (Math.round(max) * 10000) % 2000;
            if (last_max == 0) {
                max = Math.round(max * 10000) + 2000;
            } else {
                max = Math.round(max * 10000) + (2000 - last_max);
            }
            positionStr = '次';
        } else {
            max = 100;
        }
    }
    return {
        substring: positionStr,
        max: max,
        step: Math.floor(max / 5)
    };
}

//根据单位 格式化数值
function formatNumByMaxFn(num, option) {
    if (option == '亿次') {
        return Math.ceil(num / (10000 * 10000));
    } else if (option == '万次') {
        return Math.ceil(num / (10000));
    } else {
        return num;
    }
}

function formatNum(num) {
  if (num > 10000) {
    var res = Math.round(num / 10000) + '万次'
    return res
  } else {
    return num + '次'
  }
}

// 格式化 ddos 攻击宽带值
function formatDddosFn(max) {
    if (max < 1024) {
        return max + 'Mbps';
    } else if (max >= 1024) {
        return Math.ceil(max / 1024) + 'Gbps';
    }
}

function gennerItem(label) {
    var red = Math.floor(Math.random() * 256)
    var green = Math.floor(Math.random() * 256)
    var blue = Math.floor(Math.random() * 256)
    return {
        label: label,
        fill: false,
        borderColor: 'rgba(' + red + ',' + green + ',' + blue + ' , 1)',
        backgroundColor: 'rgba(' + red + ',' + green + ',' + blue + ' , 0.3)',
        pointBorderColor: 'rgba(' + red + ',' + green + ',' + blue + ' , 1)',
        pointHoverBackgroundColor: 'rgba(' + red + ',' + green + ',' + blue + ' , 1)',
        data: []
    }
}
// waf数据集合格式化
function formatWafFn(data) {
    if (!data.data_f_e.length) return 0
    var wafDatasets = {}
    var tmpArr = []
    $.each(data.data_f_e, function(i, v) {
        var idx = 0
        for (var k in v.data) {
            if (v.data.hasOwnProperty(k)) {
                if (tmpArr[idx] == null) {
                    tmpArr[idx] = []
                }
                tmpArr[idx].push(v.data[k])
                if(!wafDatasets.hasOwnProperty(k)) wafDatasets[k] = {}
                if(!wafDatasets[k].hasOwnProperty('label')) {
                    wafDatasets[k] = $.extend({}, datasetsTmp, gennerItem(data.typeDesc[k]))
                }

                wafDatasets[k].data.push(v.data[k])
                idx++
            }
        }
    });

    return {
        max: Math.max.apply(null, tmpArr.join(',').split(',')),
        datasets: wafDatasets
    };
}

// 网站加速数据集合格式化
function formatWebsiteAcceleratorFn(obj) {
    var webAccDatasets = {
        send: {
            label: '总流量',
            fill: false,
            borderColor: 'rgba(217, 122, 129, 1)',
            backgroundColor: 'rgba(217, 122, 129, 0.3)',
            pointBorderColor: 'rgba(217, 122, 129, 1)',
            pointHoverBackgroundColor: 'rgba(217, 122, 129, 1)',
            data: []
        },
        cached: {
            label: '缓存流量',
            borderColor: 'rgba(90, 177, 239, 1)',
            backgroundColor: 'rgba(90, 177, 239, 0.3)',
            pointBorderColor: 'rgba(90, 177, 239, 1)',
            pointHoverBackgroundColor: 'rgba(90, 177, 239, 1)',
            data: []
        }
    };
    //初始化datasets
    webAccDatasets.cached = $.extend({}, datasetsTmp, webAccDatasets.cached);
    webAccDatasets.send = $.extend({}, datasetsTmp, webAccDatasets.send);

    $.each(obj.data, function(i, v) {
        v.bytessend_cf = v.bytessend_cf || v.bytescached_cf;
        webAccDatasets.cached.data.push(Math.ceil(v.bytescached_cf / 1024 / 1024));
        webAccDatasets.send.data.push(Math.ceil(v.bytessend_cf / 1024 / 1024));
    });
    var unit = 'Mbps';
    var max = Math.max.apply(null, [obj.max_bytescache_f_days, obj.max_bytessend_f_days]);
    max = Math.ceil(max / 1024 / 1024);
    var step = Math.ceil(max / 5);

    return {
        unit: unit,
        max: max,
        step: step,
        datasets: webAccDatasets
    };
}

// 计算进度条显示情况
// function progressBarFn($box, percent) {
//     var int_percent = percent * 100;
//     var color = {
//         info: '#0a7edb',
//         warn: '#ff9600',
//         danger: '#ff4200'
//     };
//     var selColor = color.info;
//     if (int_percent > 10000) {
//         $box.removeClass().addClass('critical-box over');
//         return;
//     } else if (int_percent >= 9000 && int_percent <= 10000) {
//         $box.removeClass().addClass('critical-box danger');
//         selColor = color.danger;
//     } else if (int_percent >= 8000 && int_percent < 9000) {
//         $box.removeClass().addClass('critical-box warn');
//         selColor = color.warn;
//     } else {
//         $box.removeClass().addClass('critical-box info');
//         selColor = color.info;
//     }
//     $box.find('span').text(percent.toFixed(2) + '%');
//     var progressBar = $box.find('.progressbar')[0];
//     var bar = new ProgressBar.Line(progressBar, {
//         strokeWidth: 4,
//         easing: 'easeInOut',
//         duration: 1000,
//         color: selColor,
//         trailColor: '#ccc',
//         trailWidth: 4,
//         svgStyle: {
//             width: '100%',
//             height: '100%'
//         },
//         text: {
//             style: {
//                 transform: null
//             },
//             autoStyleContainer: false
//         },
//         step: function(state, bar) {
//             var barValue = bar.value();
//             bar.setText((barValue * 100).toFixed(2) + ' %');
//             bar.path.setAttribute('stroke', selColor);
//             bar.text.style.right = Math.min((1 - barValue) * 240, 240) + 'px';
//         }
//     });
//     bar.animate(int_percent / 10000);
// }



//获取格式化后的最大值和单位和步数大小
function formatnumberFnCC(max_values) {
    var max = 0;
    var format_max = {};
    if (typeof max_values !== 'string') max_values = '' + max_values;
    if (max_values.indexOf('QPS') != -1) {
        if (max_values.indexOf('亿QPS') != -1) {
            max = max_values.substring(0, max_values.indexOf('亿QPS'));
            format_max = formatmaxByNumFn(max, '亿QPS');
        } else if (max_values.indexOf('万QPS') != -1) {
            max = max_values.substring(0, max_values.indexOf('万QPS'));
            format_max = formatmaxByNumFn(max, '万QPS');
        } else {
            max = max_values.substring(0, max_values.indexOf('QPS'));
            format_max = formatmaxByNumFn(max, 'QPS');
        }
    } else {
        format_max = formatmaxByNumFn(max_values, 'QPS');
    }
    return format_max;
}

// 处理最大值 返回单位和步数
function formatmaxByNumFnCC(max, position) {
    var last_max = 0;
    positionStr = position;
    if (max > 10 && max <= 100) {
        last_max = Math.round(max) % 20;
        if (last_max == 0) {
            max = Math.round(max) + 20;
        } else {
            max = Math.round(max) + (20 - last_max);
        }
    } else if (max > 100 && max <= 1000) {
        last_max = Math.round(max) % 200;
        if (last_max == 0) {
            max = Math.round(max) + 200;
        } else {
            max = Math.round(max) + (200 - last_max);
        }
    } else if (max > 1000 && max <= 10000) {
        last_max = Math.round(max) % 2000;
        if (last_max == 0) {
            max = Math.round(max) + 2000;
        } else {
            max = Math.round(max) + (2000 - last_max);
        }
    } else if (max > 10000 && max <= 100000) {
        last_max = Math.round(max) % 20000;
        if (last_max == 0) {
            max = Math.round(max) + 20000;
        } else {
            max = Math.round(max) + (20000 - last_max);
        }
    } else if (max > 100000 && max <= 1000000) {
        last_max = Math.round(max) % 200000;
        if (last_max == 0) {
            max = Math.round(max) + 200000;
        } else {
            max = Math.round(max) + (200000 - last_max);
        }
    } else {
        if (position.toString() == '亿QPS') {
            last_max = (Math.round(max) * 10000) % 2000;
            if (last_max == 0) {
                max = Math.round(max * 10000) + 2000;
            } else {
                max = Math.round(max * 10000) + (2000 - last_max);
            }
            positionStr = '万QPS';
        } else if (position.toString() == '万QPS') {
            last_max = (Math.round(max) * 10000) % 2000;
            if (last_max == 0) {
                max = Math.round(max * 10000) + 2000;
            } else {
                max = Math.round(max * 10000) + (2000 - last_max);
            }
            positionStr = 'QPS';
        } else {
            max = 100;
        }
    }
    return {
        substring: positionStr,
        max: max,
        step: Math.floor(max / 5)
    };
}

//根据单位 格式化数值
function formatNumByMaxFnCC(num, option) {
    if (option == '亿QPS') {
        return Math.ceil(num / (10000 * 10000));
    } else if (option == '万QPS') {
        return Math.ceil(num / (10000));
    } else {
        return num;
    }
}
