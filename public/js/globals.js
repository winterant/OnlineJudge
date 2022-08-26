// ==============  对表单字段的值进行base64编码，并返回json格式
function form_json_base64(form, api_token) {
    var data = {}
    var formArray = $(form).serializeArray()
    for (var i in formArray) {
        if (data[formArray[i].name] !== undefined) {
            if (!data[formArray[i].name].push) {
                data[formArray[i].name] = [data[formArray[i].name]];
            }
            data[formArray[i].name].push(Base64.encode(formArray[i].value || ''));// encode base64
        } else {
            data[formArray[i].name] = Base64.encode(formArray[i].value || '');// encode base64
        }
    }
    data['_encode'] = 'base64'
    data['api_token'] = api_token
    return data
}
