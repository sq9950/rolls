/**
 * @description:    tab切换插件
 * 使用实例：
 * HTML代码片段：
 *  <ul class="yundun-taber">
 *      <li data-triggerType="click"
 *          data-requestUrl="/customer/domainns/getDomainBasic"
 *          data-requestType="post"
 *          data-requestParams="domain_id=8690&id=111&name=322"
 *          data-triggerAuto="true"
 *          data-callback="callback_basic"
 *          class="active first">
 *          <a href="#basic_info" role="tab" data-toggle="tab" >概览信息</a>
 *      </li>
 *      <li data-requestType="get"
 *          data-requestUrl="/customer/domainns/manager_domain"
 *          data-requestParams="domain_id=8690&id=111&name=322"
 *          data-callback="callback_manager">
 *          <a href="#dns_manager" role="tab" data-toggle="tab">DNS解析管理</a>
 *      </li>
 *  </ul>
 *  JS中调用方式：
 *      var callback_maps = {
 *          'callback_basic': function (res) {
 *              $('#form_basic_info').html(
 *                  Mustache.render($('#domain-basic-template').html(), res)
 *              );
 *          },
 *          'callback_manager':function(res){
 *              console.log('callback_manager');
 *              console.log(res);
 *          }
 *      };
 *  1. 多元素调用：
 *      $('.yundun-taber').find('li').YundunTaber({
 *          'callback_maps': callback_maps
 *       });
 *  2. 单个元素调用（指定回调函数）：
 *      $('.yundun-taber').find('.first').YundunTaber({
 *          'callback':callback_maps.callback_basic(res)
 *      });
 * @author: 张鹏玄 | <zhangpengxuan@yundun.com>
 * @time:   2015-9-23 15:13:06
 */
;(function(win, $){

    function YundunTaber(taber_obj, options){
        var _self = this;
        var default_options = {
            triggerAuto: 'false',               //是否自动触发：true | false
            triggerType: 'click',               //触发方式：点击触发
            requestUrl: "",                     //点击taber时，读取数据URL
            requestType: 'get',                 //点击taber时，请求类型：get | post
            requestParams:{},                   //获取数据的参数
            requestBefore:function(){},         //请求前置方法
            request:function(){},               //数据请求
            requestAfter:function(){},          //请求后置方法
            callbackBefore:function(){},        //回调前置方法
            callback:function(){},              //回调方法
            callbackAfter:function(){},         //回调后置方法
            callback_maps:{},                   //回调函数映射表
            clear: function(){}                 //最后清理函数
        };
        var dom_params = {
            'triggerAuto': taber_obj.attr('data-triggerAuto'),
            'triggerType': taber_obj.attr('data-triggerType'),
            'requestUrl': taber_obj.attr('data-requestUrl'),
            'requestType': taber_obj.attr('data-requestType'),
            'requestParams': taber_obj.attr('data-requestParams'),
            'callback': taber_obj.attr('data-callback')
        };
        var _options = $.extend(true, {}, default_options, dom_params, options);
        var _events = ['click','dblclick', 'mouseenter'];  //支持的触发事件方式
        if(-1 != $.inArray(_options.triggerType, _events)){
            taber_obj.on(_options.triggerType, function(){
                if(_options.requestUrl){
                    $.ajax({
                        url:_options.requestUrl,
                        beforeSend:_options.requestBefore(),
                        complete:_options.requestAfter(),
                        type:_options.requestType,
                        data:_options.requestParams,
                        dataType:'json',
                        success:function(res){
                            $.isFunction( _options.callbackBefore) &&  _options.callbackBefore();
                            if($.isFunction( _options.callback)){
                                _options.callback(res);
                            }else if($.isFunction( _options.callback_maps[_options.callback])){
                                _options.callback_maps[_options.callback](res);
                            }
                            $.isFunction( _options.callbackAfter) &&  _options.callbackAfter();
                        }
                    });
                    $.isFunction( _options.clear) &&  _options.clear();
                }else{
                    if($.isFunction( _options.callback)){
                        _options.callback();
                    }else if($.isFunction( _options.callback_maps[_options.callback])){
                        _options.callback_maps[_options.callback]();
                    }
                    $.isFunction( _options.clear) &&  _options.clear();
                }
            });
        }else{
            alert('触发事件类型不支持');
        }

        'true' == _options.triggerAuto && taber_obj[_options.triggerType]();
        return _self;
    }


    /**
     * TAB切换主流程：
     *  1. 参数处理
     *  2. 执行发送请求前置方法
     *  3. 发送请求
     *  4. 执行发送请求后置方法
     *  5. 执行回调前置方法
     *  6. 回调
     *  7. 执行回调后置方法
     *  8. 执行最后回调函数
     * @param options
     * @returns {*}
     * @constructor
     */
    $.fn.YundunTaber = function(options) {
        if(this.length){
            return this.each(function(){
               new YundunTaber($(this), options || {});
            });
        }else{
            return new YundunTaber(this.selector, options || {});
        }
    }

})(window, jQuery);
