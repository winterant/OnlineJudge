// ==============  json 值编码 =================
function json_value_base64(data, additional_data, recursive = false) {
    for (var k in data)
        if (Object.prototype.toString.call(data[k]) === '[object Object]')
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
function disabledSubmitButton(dom, disabledText, second = 15) {
    var that = $(dom)
    that.attr({ "disabled": "disabled" });	   //控制按钮为禁用
    var originText = that.text()
    that.text(disabledText + "(" + second + ")");
    second--;
    var intervalObj = setInterval(function () {
        that.text(disabledText + "(" + second + ")");
        second--;
        if (second == 0) {
            that.text(originText);
            that.removeAttr("disabled");//将按钮可用
            clearInterval(intervalObj);/* 清除已设置的setInterval对象 */
        }
    }, 1000);
}
