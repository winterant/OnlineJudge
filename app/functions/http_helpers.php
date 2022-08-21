<?php

// 发送post请求
function send_post(string $url, array $data)
{
    $jsonStr = json_encode($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        )
    );
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return array($httpCode, $response);
}

// 发送get请求
function send_get(string $url, array $data = null)
{
    if ($data)
        $url = $url . '?' . http_build_query($data);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //参数为1表示传输数据，为0表示直接输出显示。
    curl_setopt($ch, CURLOPT_HEADER, 0);         //参数为0表示不带头文件，为1表示带头文件
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); // 关闭SSL验证
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); // 关闭SSL验证
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return array($httpCode, $response);
}
