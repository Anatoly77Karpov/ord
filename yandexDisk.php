<?php

//авторизационный токен Яндекс.Диска
const AUTH_YANDEX_DISK_TOKEN = 'y0_qWErTy';

function directory_exists($name) {

    $http_head = [
        'accept: application/json',
        'Authorization: OAuth ' . AUTH_YANDEX_DISK_TOKEN,
    ];

    $path = urlencode("/$name");
    $api_url = 'https://cloud-api.yandex.net/v1/disk/resources?path=' . $path;


    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $response = curl_exec($ch);
    $response = json_decode($response, true);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ( $status == 200 ) {
        return true;
    } elseif ( $status == 404 ) {
        return false;
    } else {
        echo "Не получилось проверить наличие папки $name на Яндекс.Диске, полученная ошибка: " . $response['error'];
        return true;
    }
    curl_close($ch);
}

function create_directory($name) {

    $http_head = [
        'accept: application/json',
        'Authorization: OAuth ' . AUTH_YANDEX_DISK_TOKEN,
    ];

    $path = urlencode("/$name");
    $api_url = 'https://cloud-api.yandex.net/v1/disk/resources?path=' . $path;


    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_head);
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ( $status != 201 ) {
        die("Error: call to URL $api_url failed with status $status, response $response, curl_error " . curl_error($ch) . ", curl_errno " . curl_errno($ch));
    }
    curl_close($ch);
}

function load_file($directory, $file, $name) {
    //параметры: директория на Диске, адрес загружаемого файла и имя после загрузки

    $http_head = [
        'accept: application/json',
        'Authorization: OAuth ' . AUTH_YANDEX_DISK_TOKEN,
    ];

    //1 этап. Запрашиваем адрес загрузки методом GET
    $path = urlencode($directory . "/" . $name);
    $api_url = 'https://cloud-api.yandex.net/v1/disk/resources/upload?path=' . $path;

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ( $status != 200 ) {
        die("Error: call to URL $api_url failed with status $status, response $response, curl_error " . curl_error($ch) . ", curl_errno " . curl_errno($ch));
    }
    curl_close($ch);

    $response = json_decode($response, true);

    //2 этап. Загружаем файл методом PUT
    if (empty($response['error'])) {

        $fp = fopen($file, 'r');

        $ch = curl_init($response['href']);
        curl_setopt($ch, CURLOPT_PUT, true);
        curl_setopt($ch, CURLOPT_UPLOAD, true);
        curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file));
        curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);

        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($status != 201) {
            die("Error: failed with status $status, curl_error " . curl_error($ch) . ", curl_errno " . curl_errno($ch));
        }
        curl_close($ch);
    }
}

function publish($file) {
    //делаем файл на Яндекс.Диск публично доступным методом PUT

    $http_head = [
        'accept: application/json',
        'Authorization: OAuth ' . AUTH_YANDEX_DISK_TOKEN,
    ];

    $path = urlencode($file);
    $api_url = 'https://cloud-api.yandex.net/v1/disk/resources/publish?path=' . $path;

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_head);
    curl_setopt($ch, CURLOPT_PUT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ( $status != 200 ) {
        die("Error: call to URL $api_url failed with status $status, response $response, curl_error " . curl_error($ch) . ", curl_errno " . curl_errno($ch));
    }
    curl_close($ch);
}

function get_url($file) {
    //получаем адрес публичного файла методом GET

    $http_head = [
        'accept: application/json',
        'Authorization: OAuth ' . AUTH_YANDEX_DISK_TOKEN,
    ];

    $path = urlencode($file);
    $api_url = 'https://cloud-api.yandex.net/v1/disk/resources?path=' . $path;

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $http_head);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ( $status != 200 ) {
        die("Error: call to URL $api_url failed with status $status, response $response, curl_error " . curl_error($ch) . ", curl_errno " . curl_errno($ch));
    }
    curl_close($ch);

    $response = json_decode($response, true);
    return $response['public_url'];
}