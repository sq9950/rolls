/**
 * @team Yundun FET
 * @author shiliuping@yundun.com
 * @latest 2016-11-8
 * @operator shiliuping@yundun.com
 */

/******************* 变量声明 *******************/
var stat_cell_dns = $('#stat-cell-dns .stat-value');
var stat_cell_dns_level = $('#stat-cell-dns .stat-level');
var block_stat_title_dns = $('#block-stat-title-dns .stat-value');

var stat_cell_visit = $('#stat-cell-visit .stat-value');
var stat_cell_visit_level = $('#stat-cell-visit .stat-level');
var block_stat_title_visit = $('#block-stat-title-visit .stat-value');

var stat_cell_speed = $('#stat-cell-speed .stat-value span');
var stat_cell_speed_level = $('#stat-cell-speed .stat-level span');
var block_stat_title_speed = $('#block-stat-title-speed .stat-value');

var stat_cell_ccattack = $('#stat-cell-ccAttack .stat-value');
var stat_cell_ccattack_level = $('#stat-cell-ccAttack .stat-level span');
var block_stat_title_ccattack = $('#block-stat-title-ccAttack .stat-value');

var stat_cell_wafattack = $('#stat-cell-wafAttack .stat-value');
var stat_cell_wafattack_level = $('#stat-cell-wafAttack .stat-level span');
var block_stat_title_wafattack = $('#block-stat-title-wafAttack .stat-value');

var stat_cell_ddosattack = $('#stat-cell-ddosAttack .stat-value span');
var stat_cell_ddosattack_level = $('#stat-cell-ddosAttack .stat-level span');
var block_stat_title_ddosattack = $('#block-stat-title-ddosAttack .stat-value');

var table_speed_cache = $('#table-speed-cache');
var table_speed_speed = $('#table-speed-speed');

var table_cc_ip = $('#table-cc-ip');
var table_cc_views = $('#table-cc-views');

var table_waf_ip = $('#table-waf-ip');
var table_waf_type = $('#table-waf-type');

var table_ddos_ip = $('#table-ddos-ip');
var table_ddos_views = $('#table-ddos-views');


window.onload = function() {
    /******************* 数据初始化 *******************/
    var chartDnsResolutionCtx = $('#chartDnsResolution')[0].getContext('2d');
    var chartWebsiteVisitingCtx = $('#chartWebsiteVisiting')[0].getContext('2d');
    var chartWebsiteAcceleratorCtx = $('#chartWebsiteAccelerator')[0].getContext('2d');
    var chartCcAttackCtx = $('#chartCcAttack')[0].getContext('2d');
    var chartWafAttackCtx = $('#chartWafAttack')[0].getContext('2d');
    var chartDdosAttackCtx = $('#chartDdosAttack')[0].getContext('2d');

    //DNS解析
    mrChart.chartDnsResolution = new Chart(chartDnsResolutionCtx, $.extend(true, {}, cfgTmp));
    //网站访问
    mrChart.chartWebsiteVisiting = new Chart(chartWebsiteVisitingCtx, $.extend(true, {}, cfgTmp));
    //网站加速
    mrChart.chartWebsiteAccelerator = new Chart(chartWebsiteAcceleratorCtx, $.extend(true, {}, cfgTmp));
    //CC攻击
    mrChart.chartCcAttack = new Chart(chartCcAttackCtx, $.extend(true, {}, cfgTmp));
    //WAF攻击
    mrChart.chartWafAttack = new Chart(chartWafAttackCtx, $.extend(true, {}, cfgTmp));
    //DDoS攻击
    mrChart.chartDdosAttack = new Chart(chartDdosAttackCtx, $.extend(true, {}, cfgTmp));


    /******************* 数据填充 *******************/
    if (domain_info) {
        var domainInfo = $.parseJSON(domain_info);

        // 设置域名地址
        $('.logo-wrap p').text(domainInfo.domain);

        // 防护IP portectedIP
        var portectedIPOpt = {
            url: '/customer/monthlybydomain/protectedIpByControliD',
            data: {
                control_id: domainInfo.control_id,
                user_id: domainInfo.user_id
            }
        };
        $.post(portectedIPOpt).done(function(res) {
            if (res && res.status.code == 1) {
                var htmlArr = [];
                $.each(res.data, function(i, v) {
                    htmlArr.push('<p>' + v.ip + '&nbsp;(' + v.location + ')</p>');
                });
                $('#portectedIP').html(htmlArr.join(''));
            }
        });

        if ('ns' === domainInfo.domain_type) {
            // DNS解析 chartDnsResolution
            var chartDnsResolutionOpt = {
                url: '/customer/monthlybydomain/dnsReportView',
                data: {
                    domain: domainInfo.domain,
                    monthly: domainInfo.monthly
                }
            };
            $.post(chartDnsResolutionOpt).done(function(res) {
                if (res && res.status && res.status.code == 1) {
                    //概览 dns
                    stat_cell_dns.text(res.header_data.save_mean_values);
                    stat_cell_dns_level.text('波动' + res.header_data.undulate_level);
                    // title dns
                    block_stat_title_dns.eq(0).text(res.header_data.save_mean_values);
                    block_stat_title_dns.eq(1).text(res.header_data.undulate_level);
                    block_stat_title_dns.eq(2).text(res.header_data.save_max_days);
                    block_stat_title_dns.eq(3).text(res.header_data.max_date);

                    // chart对象
                    var dataArr = [];
                    var dnsFormat = formatnumberFn(res.header_data.save_max_days);
                    $.each(res.data, function(i, v) {
                        dataArr[i] = formatNumByMaxFn(v.point, dnsFormat.substring);
                    });

                    mrChart.chartDnsResolution.data.datasets[0].data = dataArr;
                    mrChart.chartDnsResolution.data.datasets[0].label = 'DNS解析数';
                    mrChart.chartDnsResolution.options.scales.yAxes[0].scaleLabel.labelString = dnsFormat.substring;
                    mrChart.chartDnsResolution.options.scales.yAxes[0].ticks.max = dnsFormat.max;
                    mrChart.chartDnsResolution.options.scales.yAxes[0].ticks.stepSize = dnsFormat.step;
                    mrChart.chartDnsResolution.update();
                }
            });
            $('.ns-box').show();
        } else {
            $('.ns-box').hide();
        }

        // 网站访问 chartWebsiteVisiting
        var chartWebsiteVisitingOpt = {
            url: '/customer/monthlybydomain/DomainVisitView',
            data: {
                domain_id: domainInfo.id,
                domain_type: domainInfo.domain_type,
                user_id: domainInfo.user_id,
                domain: domainInfo.domain,
                monthly: domainInfo.monthly
            }
        };
        $.post(chartWebsiteVisitingOpt).done(function(res) {
            if (res && res.status.code == 1) {
                // 概览 web visit
                stat_cell_visit.text(res.header_data.save_mean_values);
                stat_cell_visit_level.text('波动' + res.header_data.undulate_level);
                //title web visit
                block_stat_title_visit.eq(0).text(res.header_data.save_mean_values);
                block_stat_title_visit.eq(1).text(res.header_data.undulate_level);
                block_stat_title_visit.eq(2).text(res.header_data.save_max_days);
                block_stat_title_visit.eq(3).text(res.header_data.max_date);

                // chart对象
                var dataArr = [];
                var visitFormat = formatnumberFn(res.header_data.save_max_days);
                $.each(res.result, function(i, v) {
                    dataArr[i] = formatNumByMaxFn(v.data.reqline, visitFormat.substring);
                });

                mrChart.chartWebsiteVisiting.data.datasets[0].data = dataArr;
                mrChart.chartWebsiteVisiting.data.datasets[0].label = '网站访问数';
                mrChart.chartWebsiteVisiting.options.scales.yAxes[0].scaleLabel.labelString = visitFormat.substring;
                mrChart.chartWebsiteVisiting.options.scales.yAxes[0].ticks.max = visitFormat.max;
                mrChart.chartWebsiteVisiting.options.scales.yAxes[0].ticks.stepSize = visitFormat.step;
                mrChart.chartWebsiteVisiting.update();
            }
        });

        // 网站加速 chartWebsiteAccelerator
        var chartWebsiteAcceleratorOpt = {
            url: '/customer/monthlybydomain/DomainSpeedView',
            data: {
                domain_id: domainInfo.id,
                domain_type: domainInfo.domain_type,
                user_id: domainInfo.user_id,
                level_flag: domainInfo.level_flag,
                domain: domainInfo.domain,
                monthly: domainInfo.monthly
            }
        };
        $.post(chartWebsiteAcceleratorOpt).done(function(res) {
            if (res && res.status.code == 1) {
                // progressBarFn($('#stat-cell-speed .critical-box'), res.header_data.flag_percent_flow);
                // 概览 web speed
                stat_cell_speed.text(res.header_data.total_percent_flow + '%');
                stat_cell_speed_level.text(res.header_data.bytessend_cf);
                //title web speed
                block_stat_title_speed.eq(0).text(res.header_data.bytessend_cf);
                block_stat_title_speed.eq(1).text(res.header_data.total_percent_flow + '%');
                block_stat_title_speed.eq(2).text(res.header_data.max_days);
                block_stat_title_speed.eq(3).text(res.header_data.max_date);

                var htmlArr = [];
                var len = 0;
                // cache top5
                if (res.top5_reqcache && res.top5_reqcache.length>1) {
                    htmlArr = [];
                    $.each(res.top5_reqcache, function(i, v) {
                        htmlArr.push('<tr><td>' + v.bytescached_f + '</td><td>' + v.date + '</td></tr>');
                    });
                    len = htmlArr.length;
                    for (var i = 0; i < 5 - len; i++) {
                        htmlArr.push('<tr><td></td><td></td></tr>');
                    }
                    table_speed_cache.html(htmlArr.join());
                }

                // speed top5
                if (res.top5_reqspeed && res.top5_reqspeed.length>1) {
                    htmlArr = [];
                    $.each(res.top5_reqspeed, function(i, v) {
                        htmlArr.push('<tr><td>' + v.percent_req + '%</td><td>' + v.date + '</td></tr>');
                    });
                    len = htmlArr.length;
                    for (var i = 0; i < 5 - len; i++) {
                        htmlArr.push('<tr><td></td><td></td></tr>');
                    }
                    table_speed_speed.html(htmlArr.join());
                }

                // chart对象
                if (res.reqcache) {
                    // 清空数据集
                    mrChart.chartWebsiteAccelerator.data.datasets = [];
                    var webAcc = formatWebsiteAcceleratorFn(res.reqcache);
                    mrChart.chartWebsiteAccelerator.data.datasets.push(webAcc.datasets.cached);
                    mrChart.chartWebsiteAccelerator.data.datasets.push(webAcc.datasets.send);
                    mrChart.chartWebsiteAccelerator.options.scales.yAxes[0].scaleLabel.labelString = webAcc.unit;
                    mrChart.chartWebsiteAccelerator.options.scales.yAxes[0].ticks.max = webAcc.max;
                    mrChart.chartWebsiteAccelerator.options.scales.yAxes[0].ticks.stepSize = webAcc.step;
                    mrChart.chartWebsiteAccelerator.update();
                }
            }
        });

        // CC攻击 chartCcAttack
        var chartCcAttackOpt = {
            url: '/customer/monthlybydomain/ccAttackView',
            data: {
                domain_id: domainInfo.id,
                domain_type: domainInfo.domain_type,
                user_id: domainInfo.user_id,
                level_flag: domainInfo.level_flag,
                domain: domainInfo.domain,
                monthly: domainInfo.monthly
            }
        };
        $.post(chartCcAttackOpt).done(function(res) {
            if (res && res.status.code == 1) {
                // progressBarFn($('#stat-cell-ccAttack .critical-box'), res.header_data.total_cc_percent);
                // 概览 cc attack
                stat_cell_ccattack.text(res.header_data.save_max_days);
                //title cc attack
                block_stat_title_ccattack.eq(0).text(res.header_data.save_max_days);
                block_stat_title_ccattack.eq(1).text(res.header_data.max_date);

                // chart对象
                var dataArr = [];
                var ccFormat = formatnumberFnCC(res.header_data.save_max_days);

                $.each(res.result, function(i, v) {
                    dataArr[i] = formatNumByMaxFnCC(v.data.ccline, ccFormat.substring);
                });

                mrChart.chartCcAttack.data.datasets[0].data = dataArr;
                mrChart.chartCcAttack.data.datasets[0].label = 'CC攻击QPS';
                mrChart.chartCcAttack.options.scales.yAxes[0].scaleLabel.labelString = ccFormat.substring;
                mrChart.chartCcAttack.options.scales.yAxes[0].ticks.max = ccFormat.max;
                mrChart.chartCcAttack.options.scales.yAxes[0].ticks.stepSize = ccFormat.step;
                mrChart.chartCcAttack.update();
            }
        });

        // CC列表 ccTopipAndUserAgant
        var ccTopipAndUserAgantOpt = {
            url: '/customer/monthlybydomain/ccTopipAndUserAgant',
            data: {
                domain_id: domainInfo.id,
                domain_type: domainInfo.domain_type,
                user_id: domainInfo.user_id,
                domain: domainInfo.domain,
                monthly: domainInfo.monthly
            }
        };
        $.post(ccTopipAndUserAgantOpt).done(function(res) {
            if (res && res.status.code == 1) {
                var htmlArr = [];
                var len = 0;
                // top_ip
                if (res.top_ip && res.top_ip.length>1) {
                    htmlArr = [];
                    $.each(res.top_ip, function(i, v) {
                        htmlArr.push('<tr><td>' + v.remote_ip + '</td><td>' + v.country + v.city + '</td><td>' + v.totalNums + '次</td></tr>');
                    });
                    len = htmlArr.length;
                    for (var i = 0; i < 5 - len; i++) {
                        htmlArr.push('<tr><td></td><td></td><td></td></tr>');
                    }
                    table_cc_ip.html(htmlArr.join(''));
                }

                //user agant
                if (res.user_agent && res.user_agent.length>1) {
                    htmlArr = [];
                    $.each(res.user_agent, function(i, v) {
                        htmlArr.push('<tr title="' + v.name + '"><td>' + v.save_name + '</td><td>' + v.value + '次</td></tr>');
                    });
                    len = htmlArr.length;
                    for (var i = 0; i < 5 - len; i++) {
                        htmlArr.push('<tr><td></td><td></td></tr>');
                    }
                    table_cc_views.html(htmlArr.join(''));
                }
            }
        });

        // WAF攻击 chartWafAttack
        var chartWafAttackOpt = {
            url: '/customer/monthlybydomain/wafAttackView',
            data: {
                domain_id: domainInfo.id,
                domain_type: domainInfo.domain_type,
                user_id: domainInfo.user_id,
                monthly: domainInfo.monthly
            }
        };
        $.post(chartWafAttackOpt).done(function(res) {
            if (res && res.status.code == 1) {
                var waf_type_name = res.totalCcWaf === 0 ? '没有' : res.topWafType[0].name;
                // 概览 waf attack
                stat_cell_wafattack.text(res.totalCcWaf + '次');
                stat_cell_wafattack_level.text(waf_type_name);
                //title waf attack
                block_stat_title_wafattack.eq(0).text(res.totalCcWaf + '次');
                block_stat_title_wafattack.eq(1).text(res.totalWaf + '种');
                block_stat_title_wafattack.eq(2).text(waf_type_name);

                var htmlArr = [];
                var len = 0;
                // 攻击IP
                if (res.topWafIp && res.totalCcWaf) {
                    htmlArr = [];
                    $.each(res.topWafIp, function(i, v) {
                        htmlArr.push('<tr><td>' + v.remote_ip + '</td><td>' + v.country + v.city + '</td><td>' + v.totalNums + '次</td></tr>');
                    });
                    len = htmlArr.length;
                    for (var i = 0; i < 5 - len; i++) {
                        htmlArr.push('<tr><td></td><td></td><td></td></tr>');
                    }
                    table_waf_ip.html(htmlArr.join());
                }

                // 攻击类型
                if (res.topWafType && res.totalCcWaf) {
                    htmlArr = [];
                    $.each(res.topWafType, function(i, v) {
                        htmlArr.push('<tr><td>' + v.name + '</td><td>' + v.count + '次</td></tr>');
                    });
                    len = htmlArr.length;
                    for (var i = 0; i < 5 - len; i++) {
                        htmlArr.push('<tr><td></td><td></td></tr>');
                    }
                    table_waf_type.html(htmlArr.join());
                }

                // chart对象
                if (res.data_f_e && res.totalCcWaf != 0) {
                    // 清空数据集
                    mrChart.chartWafAttack.data.datasets = [];
                    var wafObj = formatWafFn(res);
                    var wafFormat = formatnumberFn(wafObj.max);

                    for (var k in wafObj.datasets) {
                        if (wafObj.datasets.hasOwnProperty(k)) {
                            mrChart.chartWafAttack.data.datasets.push(wafObj.datasets[k]);
                        }
                    }
                    mrChart.chartWafAttack.options.scales.yAxes[0].scaleLabel.labelString = wafFormat.substring;
                    mrChart.chartWafAttack.options.scales.yAxes[0].ticks.max = wafFormat.max;
                    mrChart.chartWafAttack.options.scales.yAxes[0].ticks.stepSize = wafFormat.step;
                    mrChart.chartWafAttack.update();
                }
            }
        });

        // DDoS攻击 chartDdosAttack
        var chartDdosAttackOpt = {
            url: '/customer/monthlybydomain/ddosAttackView',
            data: {
                control_id: domainInfo.control_id,
                user_id: domainInfo.user_id,
                level_flag: domainInfo.level_flag,
                domain: domainInfo.domain,
                monthly: domainInfo.monthly
            }
        };
        $.post(chartDdosAttackOpt).done(function(res) {
            if (res && res.status.code == 1) {
                // progressBarFn($('#stat-cell-ddosAttack .critical-box'), res.header_data.total_ddos_percent);
                // 概览 ddos attack
                stat_cell_ddosattack.text(res.header_data.save_max_days);
                stat_cell_ddosattack_level.text(res.header_data.max_ddos_days + '天');
                //title ddos attack
                block_stat_title_ddosattack.eq(0).text(res.header_data.save_max_days);
                block_stat_title_ddosattack.eq(1).text(res.header_data.max_date);

                var htmlArr = [];
                var len = 0;
                // ddos top5 ip
                if (res.top_ddos_ip && res.top_ddos_ip.length>1) {
                    htmlArr = [];
                    $.each(res.top_ddos_ip, function(i, v) {
                        htmlArr.push('<tr><td>' + v._id + '</td><td>' + v.src_city + '</td><td>' + v.save_total_num + '</td></tr>');
                    });
                    len = htmlArr.length;
                    for (var i = 0; i < 5 - len; i++) {
                        htmlArr.push('<tr><td></td><td></td><td></td></tr>');
                    }
                    table_ddos_ip.html(htmlArr.join());
                }

                //ddos max
                if (res.header_data.max_top5 && res.header_data.max_top5.length>1) {
                    htmlArr = [];
                    $.each(res.header_data.max_top5, function(i, v) {
                        htmlArr.push('<tr><td>' + v.save_max + '</td><td>' + v.date + '</td></tr>');
                    });
                    len = htmlArr.length;
                    for (var i = 0; i < 5 - len; i++) {
                        htmlArr.push('<tr><td></td><td></td></tr>');
                    }
                    table_ddos_views.html(htmlArr.join());
                }

                // chart对象
                var dataArr = [];
                var ddosFormat = formatnumberFn(res.header_data.max_days);
                for (var k in res.data) {
                    if (res.data.hasOwnProperty(k)) {
                        dataArr.push(res.data[k].max);
                    }
                }
                mrChart.chartDdosAttack.data.datasets[0].data = dataArr;
                mrChart.chartDdosAttack.data.datasets[0].label = 'DDoS攻击带宽';
                mrChart.chartDdosAttack.options.scales.yAxes[0].scaleLabel.labelString = 'Mbps';
                mrChart.chartDdosAttack.options.scales.yAxes[0].ticks.max = ddosFormat.max;
                mrChart.chartDdosAttack.options.scales.yAxes[0].ticks.stepSize = ddosFormat.step;
                mrChart.chartDdosAttack.update();
            }
        });

        var flag = 0;
        $(document).ajaxSuccess(function(event, xhr, settings) {
            flag++

            if ((flag== 7 && domainInfo.domain_type=="cname") || (flag==8 && domainInfo.domain_type=="ns")){
                var imageIds = ['dashboard', 'dns-resolution', 'website-visiting', 'website-accelerator', 'cc-attack', 'waf-attack', 'ddos-attack'];
                if ('ns' !== domainInfo.domain_type) {
                    imageIds.splice(1, 1);
                }
                var count = 0;
                for (var i = 0; i < imageIds.length; i++) {
                    (function(idx) {
                        var file_name = imageIds[idx];
                        html2canvas($('#' + imageIds[idx]), {
                            onrendered: function(canvas) {
                                var html_canvas = canvas.toDataURL();
                                $.post('/customer/monthlybydomain/MonthlyImageByCanvas', {
                                    file_name: file_name,
                                    domain: domainInfo.domain,
                                    html_canvas: html_canvas,
                                    monthly:domainInfo.monthly
                                }, function(res) {
                                  count++;
                                  if(count == imageIds.length){
                                    $(".download-warp img").css("display","inline-block");
                                  }
                                }, 'json');
                            }
                        });
                    })(i);
                }
            }

        });


        // word
        $(".download-warp img:eq(0)").click(function(){
            $.post(
                "/customer/monthlybydomain/CheckDownloadMonthly",
                {domain:domainInfo.domain,domain_type:domainInfo.domain_type,type:"word",domain_id:domainInfo.id,monthly:domainInfo.monthly},
                function(data){
                    if(data.status.code==1){
                        window.open("/customer/monthlybydomain/MonthlyCreateWord?domain="+domainInfo.domain+"&domain_type="+domainInfo.domain_type+"&monthly="+domainInfo.monthly);
                    }else{
                        alert(data.message);
                    }
                });

        });

        // pdf
         $(".download-warp img:eq(1)").click(function(){
                $.post(
                    "/customer/monthlybydomain/CheckDownloadMonthly",
                    {domain:domainInfo.domain,domain_type:domainInfo.domain_type,type:"pdf"},
                    function(data){
                        if(data.status.code==1){
                            window.open("/customer/monthlybydomain/MonthlyCreatePdf?domain="+domainInfo.domain+"&domain_type="+domainInfo.domain_type);
                        }else{
                            alert(data.message);
                        }
                });
        });
    }
}
