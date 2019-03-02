
;(function($){
    $(function(){

        //监听搜索的回车事件
        $("input[data-control-action^='filter']").keypress(function(event){
            if(13 == event.which){
                $(this).parent().find('button').click();
            }
        });

        //展开/收缩左边菜单
        $("#sidebar_collspan").on('click', function() {
            if($(".sidebar").css("left") == "0px") {
                $(".sidebar").css("left", -($(".sidebar .nav-sidebar").width()-10));
                $(".main").addClass("collspan");
                $(this).find("span").removeClass().addClass("glyphicon glyphicon-chevron-right");
            } else {
                $(".sidebar").css("left", 0);
                $(".main").removeClass("collspan");
                $(this).find("span").removeClass().addClass("glyphicon glyphicon-chevron-left");
            }
        });
        window.onresize = function () {
            if($(".sidebar").css("left") != "0px") {
                $(".sidebar").css("left", -($(".sidebar .nav-sidebar").width()-10));
            }
        };
    });

    //handlebar封装，调用：$('#container').handlebars($('#template'), { name: "Alan" });
    var compiled = {};
    $.fn.handlebars = function(template, data) {
        if (template instanceof jQuery) {
            template = $(template).html();
        }
        compiled[template] = Handlebars.compile(template);
        this.html(compiled[template](data));
    };
})(jQuery);

<!-- success then keep condition useage:模板文件中jplist模块加入下面注释的代码，然后js里调用定义的方法即可-->
//<div class="text-filter-box" style="display: none;">
//    <input
//        data-path=".title"
//        data-button="#search-button-use-keep-condition"
//        type="text"
//        data-control-type="textbox"
//        data-control-action="filter"
//    />
//
//    <button
//        type="button"
//        id="search-button-use-keep-condition">
//    <span class="glyphicon glyphicon-search" aria-hidden="true"></span>
//    </button>
//</div>
var triggerKeepConditionSearch = function(id){
    var _id = id || 'search-button-use-keep-condition';
    $('#'+_id).trigger("click");
}

//保持当前的搜索条件，刷新列表数据
function global_flush_jplist(){
    if($('.list-flush-button').length <= 0){
        console.log('按钮未设置 list-flush-button 的 class ');
    }else{
        $('.list-flush-button').click();
    }
}