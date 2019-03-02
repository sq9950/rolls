/**
 * @team Yundun FET
 * @author shiliuping@yundun.com
 * @latest 2016/8/16
 * @operator shiliuping@yundun.com
 *
 * @description jquery-clipboard-1.0.js  jQuery插件 兼容性复制
 * @depend clipboard.js v1.5.12          仅支持IE9+ 不支持Safari
 * @depend jquery.js
 * eg:
<script src="js/jquery-1.11.1.min.js"></script>
<!--[if gte IE 9]><!--><script src="js/clipboard.min.js"></script><!--<![endif]-->
<script src="js/jquery-clipboard-1.0.js"></script>
 */
(function ($) {
    $.fn.extend({
        clipboard: function (options) {
            var defaults = {
                attr: "",
                success: null,
                error: null
            };
            var opts = $.extend(defaults, options);

            if (window.clipboardData) {
                return this.each(function () {
                    var $obj = $(this), text = $obj.attr(opts.attr);
                    var eObj = {
                        action: "copy",
                        text: text,
                        trigger: $obj[0],
                        clearSelection: function () {
                            window.clipboardData.clearData();
                        }
                    };
                    try {
                        $obj.on("click", function () {
                            window.clipboardData.clearData();
                            window.clipboardData.setData("Text", text);
                            if (opts.success) opts.success(eObj);
                        });
                    } catch (err) {
                        if (opts.error) {
                            eObj.text = err;
                            opts.error(eObj);
                        }
                    }
                });
            } else {
                var clipboard = new Clipboard(this.selector, {
                    text: function (trigger) {
                        return trigger.getAttribute(opts.attr);
                    }
                });
                if (clipboard) {
                    clipboard.on('success', function (e) {
                        if (opts.success) opts.success(e);
                        e.clearSelection();
                    });

                    clipboard.on('error', function (e) {
                        if (opts.error) {
                            e.text = e.trigger.getAttribute(opts.attr);
                            opts.error(e);
                        }
                    });
                }
                return this;
            }
        }
    });
})(jQuery);