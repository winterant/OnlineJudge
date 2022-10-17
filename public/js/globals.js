// ==============  json 值编码 =================
function json_value_base64(data, additional_data = {}, recursive = false) {
    for (var k in data)
        if (Object.prototype.toString.call(data[k]) === '[object Object]'
            || Object.prototype.toString.call(data[k]) === '[object Array]')
            data[k] = json_value_base64(data[k], null, true)
        else
            data[k] = Base64.encode(data[k]);
    if (recursive) // 递归的子对象，直接返回
        return data
    for (var k in additional_data)
        data[k] = additional_data[k];
    data['_encode'] = 'base64'
    return data
}

// =================== 提交按钮点击后倒计时不可用
function disabledSubmitButton(dom, disabledText, second = 10) {
    if (second <= 0)
        return

    var originText = $(dom).text()
    $(dom).attr({ "disabled": "disabled" });	   //控制按钮为禁用

    var f = () => {
        if (second <= 0) {
            $(dom).text(originText);
            $(dom).removeAttr("disabled");//将按钮可用
            clearInterval(intervalObj);/* 清除已设置的setInterval对象 */
            return f;
        }
        $(dom).text(disabledText + "(" + second + ")");
        second--;
        return f;
    }
    var intervalObj = setInterval(f(), 1000);
}

// ====================== 将指定dom的文本复制到系统剪贴板 ====================
function copy_text(dom) {
    $("body").append('<textarea id="copy_temp">' + $(dom).html() + '</textarea>');
    $("#copy_temp").select();
    document.execCommand("Copy");
    $("#copy_temp").remove();
    Notiflix.Notify.Success('Replicated');
}

// ==================== textarea自动高度 ================
// For example: <textarea autoheight></textarea>
$(function () {
    $.fn.autoHeight = function () {
        function autoHeight(elem) {
            elem.style.height = 'auto';
            elem.scrollTop = 0; //防抖动
            elem.style.height = elem.scrollHeight + 2 + 'px';
        }

        this.each(function () {
            autoHeight(this);
            $(this).on('input', function () {
                autoHeight(this);
            });
        });
    }
    $('textarea[autoHeight]').autoHeight();
})
