
;(function($){
    $.YundunTools = {
        //jplist插件，获取数据并渲染分页方法
        'render_jplist': function (options){
            var default_options = {
                itemsBox: '.list',
                itemPath: '.list-item',
                panelPath: '.jplist-panel',
                jplist_page: $('#jplist-page-area'),
                container_list: $('#node_list_container'),
                jplist_template: $('#jplist-template'),
                dataSource:{
                    type: 'server',
                    ajax:{
                        url:"",
                        dataType: 'json',
                        type: 'POST',
                        data:{}
                    },
                    //查询数据后，渲染页面默认方法
                    render:function(dataItem, statuses){
                        var template = Handlebars.compile(_options.jplist_template.html());
                        _options.container_list.html(template(dataItem.content));
                        _options.dataSource.callback();
                    },
                    //渲染页面后，默认回调方法
                    callback:function(){
                        //调用开关插件
                        $("input[name=status]").bootstrapSwitch();
                        $('input[name="status"]').on('switchChange.bootstrapSwitch', function(event, state) {
                            $.post(
                                $(this).data('url'),
                                {
                                    id: $(this).attr('data-id'),
                                    status: state ? 1 : 0
                                },
                                function(res){
                                    layer.msg(res.info, {icon:res.status});
                                    if(res.status == 0){
                                        this.render_jplist(options);
                                    }
                                }
                            );
                        });
                    }
                }
            };
            var _options = $.extend(true, {}, default_options, options);
            //防止按钮的重复绑定
            _options.jplist_page.find('button[type="button"]').off('click');
            _options.jplist_page.jplist({
                itemsBox: _options.itemsBox,
                itemPath: _options.itemPath,
                panelPath: _options.panelPath
                , dataSource: {
                    type: _options.dataSource.type
                    , server: {
                        //ajax settings
                        ajax: {
                            url: _options.dataSource.ajax.url
                            , dataType: _options.dataSource.ajax.dataType
                            , type: _options.dataSource.ajax.type
                            , data: _options.dataSource.ajax.data
                        }
                    }
                    , render: function (dataItem, statuses) {
                        _options.dataSource.render(dataItem, statuses);
                    }
                }

            });
        },

        //格式化json代码
        formatJson: function (txt,compress/*是否为压缩模式*/){/* 格式化JSON源码(对象转换为JSON文本) */
            var indentChar = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            if(/^\s*$/.test(txt)){
                alert('数据为空,无法格式化! ');
                return;
            }
            try{var data=eval('('+txt+')');}
            catch(e){
                console.log('数据源语法错误,格式化失败! 错误信息: '+e.description,'err');
                return;
            };
            var draw=[],last=false,This=this,line=compress?'':'<br/>',nodeCount=0,maxDepth=0;

            var notify=function(name,value,isLast,indent/*缩进*/,formObj){
                nodeCount++;/*节点计数*/
                for (var i=0,tab='';i<indent;i++ )tab+=indentChar;/* 缩进HTML */
                tab=compress?'':tab;/*压缩模式忽略缩进*/
                maxDepth=++indent;/*缩进递增并记录*/
                if(value&&value.constructor==Array){/*处理数组*/
                    draw.push(tab+(formObj?('"<span class="text-danger">'+name+'</span>" : '):'')+'['+line);/*缩进'[' 然后换行*/
                    for (var i=0;i<value.length;i++)
                        notify(i,value[i],i==value.length-1,indent,false);
                    draw.push(tab+']'+(isLast?line:(','+line)));/*缩进']'换行,若非尾元素则添加逗号*/
                }else   if(value&&typeof value=='object'){/*处理对象*/
                    draw.push(tab+(formObj?('"<span class="text-danger">'+name+'</span>" : '):'')+'&nbsp;{'+line);/*缩进'{' 然后换行*/
                    var len=0,i=0;
                    for(var key in value)len++;
                    for(var key in value)notify(key,value[key],++i==len,indent,true);
                    draw.push(tab+'&nbsp;}'+(isLast?line:(','+line)));/*缩进'}'换行,若非尾元素则添加逗号*/
                }else{
                    if(typeof value=='string')value='"<span class="text-success">'+value+'</span>"';
                    draw.push(tab+(formObj?('"<span class="text-danger">'+name+'</span>" : <span class="text-success">'):'')+value+(isLast?'</span>':'</span>,')+line);
                };
            };
            var isLast=true,indent=0;
            notify('',data,isLast,indent,false);
            return draw.join('');
        },

    };

    /**
     *     注册通用handlebars帮助函数
     */
    Handlebars.registerHelper("handlebarsFormatDefault", function (value, default_value, options) {
        if (undefined == value || '' == value) {
            return default_value;
        } else {
            return value;
        }
    });

    Handlebars.registerHelper('in', function(ele,  arr, options) {
        if (arguments.length < 2) {
            throw new Error('in 操作符必须传递3个参数');
        }else if(! arr instanceof Array){
            throw new Error('in操作的第二个参数必须是数组！');
        }
        if(-1 == $.inArray(ele, arr)){
            return options.inverse(this);
        } else {
            return options.fn(this);
        }
    });

    /**
     * 比较操作
     * 示例：  {{#compare 'tinyint' '==' type}}
     *           aaaaaa
     *       {{else}}
     *          bbbb
     *       {{/compare}}
     */
    Handlebars.registerHelper('compare', function(left, operator, right, options) {
        if (arguments.length < 3) {
            throw new Error('Handlerbars Helper "compare" needs 2 parameters');
        }
        var operators = {
            '==':     function(l, r) {return l == r; },
            '===':    function(l, r) {return l === r; },
            '!=':     function(l, r) {return l != r; },
            '!==':    function(l, r) {return l !== r; },
            '<':      function(l, r) {return l < r; },
            '>':      function(l, r) {return l > r; },
            '<=':     function(l, r) {return l <= r; },
            '>=':     function(l, r) {return l >= r; },
            'typeof': function(l, r) {return typeof l == r; }
        };

        if (!operators[operator]) {
            throw new Error('Handlerbars Helper "compare" doesn\'t know the operator ' + operator);
        }

        var result = operators[operator](left, right);

        if (result) {
            return options.fn(this);
        } else {
            return options.inverse(this);
        }
    });

    //Handlebars.registerHelper('helperMissing', function(/* [args, ] options */) {
    //    var options = arguments[arguments.length - 1];
    //    if(arguments.length > 1){
    //        throw new Handlebars.Exception('未注册的函数: ' + options.name);
    //    }else{
    //        throw new Handlebars.Exception('未定义的变量: ' + options.name);
    //    }
    //});

    Handlebars.registerHelper("formatDomainStatus",function(status_desc){
        var display_html = '',
            display_class = {
                '待激活'        :   'default',
                '禁用'        :   'danger',
                '审核通过'     :   'success',
                '审核中'       :    'info',
                '暂停'        :   'default',
                '审核未通过'     :   'warning',
                '回源'        :   'info',
                '未开启'       :   'info',
                '启用'        :    'info',
                '锁定'        :   'info',
                '解锁'        :   'info',
                '向导第二步'   :   'info',
                '向导第三步'   :   'info',
            };
        if(status_desc){
            display_html = '<span class="label label-'+display_class[status_desc]+'">'+status_desc+'</span>';
        }
        return display_html;
    });

    Handlebars.registerHelper("formatDnsStatus",function(dns_set_desc){
        var display_html = '',
            display_class = {
                '未检测到DNS'  :   'danger',
                '未接入'   :  'danger',
                '已接入'   :   'success'
            };
        if(dns_set_desc){
            //if('未检测到DNS' == dns_set_desc){dns_set_desc = '未接入'}
            display_html = '<span class="label label-'+display_class[dns_set_desc]+'">'+dns_set_desc+'</span>';
        }
        return display_html;
    });

    Handlebars.registerHelper("formatDnsUseStatus",function(dns_use_desc){
        var display_html = '',
            display_class = {
                '暂停解析'  :   'danger',
                '已启用'   :  'info',
                '已同步'   :   'success'
            };
        if(dns_use_desc){
            display_html = '<span class="label label-'+display_class[dns_use_desc]+'">'+dns_use_desc+'</span>';
        }
        return display_html;
    });

    Handlebars.registerHelper("formatRsyncDesc",function(rsync_desc,  options){
        var display_html = '',
            display_class = {
                '否(0)'  :   'danger',
                '是(1)'   :  'success',
                '否(2)'   :  'danger',
                '同步中(3)'   :   'warning',
                '4'   :   'info',
                '10' : 'info',
                '未知(17)' : 'default',

            };
        if(rsync_desc && display_class[rsync_desc]){
            display_html = '<span class="label label-'+display_class[rsync_desc]+'">'+rsync_desc+'</span>';
        }
        return display_html;
    });
})(jQuery);