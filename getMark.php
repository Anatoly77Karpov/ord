<?php
const AUTH_ORD_YANDEX_TOKEN = 'y0_QwErTY';

function get_mark($json) {
    //получаем токен маркировки с помощью json-запроса в ОРД Яндекс

    //определяем для какой площадки запрашиваем токен - XXX или YYY?
    $org = '';
    $json_arr = json_decode($json, true);
    if ($json_arr['contractId'] == '12345') {
        $org = 'xxx';
    } elseif ($json_arr['contractId'] == '67890') {
        $org = 'yyy';
    }

    //задаём адрес API и заголовок запроса
    $api_url = 'https://ord-prestable.yandex.net/api/v3/creative';//тестовый API
    $http_head = [
        'accept: application/json',
        'Authorization: Bearer y0_AgAAAABu-dfwAAhgQAAAAADmg_50LhMlQ_8OQrWn9fKNgU3Wahir0Go',
        'Content-Type: application/json',
    ];

    //инициализируем CURL-запрос
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_head);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);

    //получаем ответ на CURL-запрос и проверяем на ошибки
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ( $status != 200 ) {
        die("Error: call to URL $api_url failed with status $status, response $response, curl_error " . curl_error($ch) . ", curl_errno " . curl_errno($ch));
    }
    curl_close($ch);

    //формируем текст для маркировки
    $res = json_decode($response, true);
    if ($org == 'xxx') {
        $mark = 'Реклама | ООО "XXX" | erid: ' . $res['token'];
    } elseif ($org == 'yyy') {
        $mark = 'Реклама | ООО "YYY" | erid: ' . $res['token'];
    }

    //отдаём массив из строки для маркировки и Json ответа ОРД Яндекс
    $result = [
        'mark' => $mark,
        'response' => $response,
    ];
    return $result;
}