<!DOCTYPE html>
<html lang="en">

<head>
    <title>Маркировка рекламы</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon1.ico">

    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/templatemo.css">
    <link rel="stylesheet" href="assets/css/custom.css">

    <!-- Load fonts style after rendering the layout styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="assets/css/fontawesome.min.css">
</head>

<body>
<!-- Заголовок -->
<div class="container-fluid bg-light py-5">
    <div class="col-md-6 m-auto text-center">
        <h1 class="h1">Маркировка рекламы</h1>
    </div>
</div>

<?php
//Задаём месяц по умолчанию в форме - после 20го числа отображаем следующий
if ((int) date('d') > 20) {
    $date = date('Y-m', time() + 15*24*60*60);
} else {
    $date = date('Y-m');
}
?>

<!-- Форма добавления листовок для маркировки -->
<div class="container py-5">
    <div class="row py-5">
        <form class="col-md-9 m-auto" enctype="multipart/form-data" action="" method="post" role="form">
            <div class="row">
                <div class="form-group col-md-6 mb-3">
                    <label for="org">Площадка</label><br>
                    <select class="form-control mt-1" id="org" name="org">
                        <option value="1" selected>XXX+YYY</option>
                        <option value="2">XXX</option>
                        <option value="3">YYY</option>
                    </select>
                </div>
                <div class="form-group col-md-6 mb-3">
                    <label for="month">Месяц</label>
                    <input type="month" class="form-control mt-1" id="month" name="month" value="<?=$date?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="imgs">Листовки акций</label>
                <input type="file" class="form-control mt-1" id="imgs" name="imgs[]" multiple required>
            </div>
            <div class="row">
                <div class="col text-end mt-2">
                    <button type="submit" class="btn btn-success btn-lg px-3">Добавить</button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On'); 

ini_set('max_execution_time', '10000');
set_time_limit(0);
ini_set('memory_limit', '4096M');
ignore_user_abort(true);

require 'yandexDisk.php';
require 'getJSON.php';
require 'getMark.php';
require 'databaseShell.php';
require 'markImage.php';

$db = new DatabaseShell('localhost', 'root', '', 'test');
$db_query =[];

//обрабатываем листовки после загрузки формы
if (!empty($_POST['org'])) {

    //формируем строку месяц+год (в формате "1023") для вставки в id запроса к ОРД и сохранения в БД
    $month = substr($_POST['month'], 5, 2) . substr($_POST['month'], 2, 2);

    //формируем адрес папки в Яндекс.Диск с указанием месяца и года - ".../Oct23"
    $directory = 'ORD Yandex/' . date('My', strtotime($_POST['month']));
    //добавляем на Яндекс.Диск необходимые папки, если их ещё нет
    if (!directory_exists($directory)) {
        create_directory($directory);
        create_directory($directory . "/xxx");
        create_directory($directory . "/yyy");
        create_directory($directory . "/csv");
    }

    //создаём локальную папку для месяца и вложенные папки xxx, yyy
    $local_dir = 'files/' . date('My', strtotime($_POST['month']));
    if (!is_dir($local_dir)) {
        mkdir($local_dir);
        mkdir($local_dir . "/xxx");
        mkdir($local_dir . "/yyy");
    }

    //сохраняем массив загруженных листовок
    $files = $_FILES['imgs'];
    $count = count($files['name']);

    //инициализируем массив с данными загруженных листовок
    $table = [];

    //инициализируем csv файл, куда будут записаны токены
    $csv_file = 'files/csv/table_' . date('d.m.Y_H.i') . '.csv';
    $fp = fopen($csv_file, 'w+t');
    fwrite($fp, "id;xxx;yyy;\n");

    //начинаем вывод таблицы с данными для маркировки загруженных листовок
    echo '<table class="container py-5">';

    for ($i = 0; $i < $count; $i++) {

        //загружаем на Яндекс.Диск, открываем публичный доступ и получаем адрес листовки
        load_file($directory, $files['tmp_name'][$i], $files['name'][$i]);
        publish($directory . '/' . $files['name'][$i]);
        $url = get_url($directory . '/' . $files['name'][$i]);

        //сохраняем листовку на локальном диске
        copy($files['tmp_name'][$i], "$local_dir/" . $files['name'][$i]);

        //определяем номер акции
        preg_match('#^(\d+)#su', $files['name'][$i], $match);
        $number = $match[1];

        //начинаем формировать строку для сохраненния данных в БД
        $db_query = [
            'action_id' => $number,
            'places' => $_POST['org'],
            'month' => $month,
            'media_url' => $url,
        ];

        //начинаем заполнять массив таблицы с данными для маркировки загруженных листовок
        $table[$number]['number'] = $number;
        $table[$number]['url'] = $url;

        //запрашиваем токены, заполняем массив таблицы и строку для БД
        if ($_POST['org'] == '1') {//когда акция для обеих площадок XXX+YYY

            //формируем json для запроса токена для XXX, запрашиваем и сохраняем данные для таблицы и БД
            $json = get_json('xxx', $number, $month, $url);
            $xxx_mark = get_mark($json)['mark'];
            $table[$number]['xxx'] = $xxx_mark;

            $db_query['xxx_token'] = $xxx_mark;
            $db_query['xxx_response'] = get_mark($json)['response'];

            //наносим маркировку и сохраняем маркированные листовки локально и на Яндекс.Диск
            $new_name = markImage($files['name'][$i], $files['tmp_name'][$i], $xxx_mark, $local_dir);
            load_file("$directory/xxx", $new_name, basename($new_name));

            //аналогичные действия для YYY - маркируем, сохраняем данные маркировки и маркированные листовки
            $json = get_json('yyy', $number, $month, $url);
            $yyy_mark = get_mark($json)['mark'];
            $table[$number]['yyy'] = $yyy_mark;

            $db_query['yyy_token'] = $yyy_mark;
            $db_query['yyy_response'] = get_mark($json)['response'];

            $new_name = markImage($files['name'][$i], $files['tmp_name'][$i], $yyy_mark, $local_dir);
            load_file("$directory/yyy", $new_name, basename($new_name));

        } elseif ($_POST['org'] == '2') {

            //когда акция только для XXX
            $json = get_json('xxx', $number, $month, $url);
            $xxx_mark = get_mark($json)['mark'];
            $table[$number]['xxx'] = $xxx_mark;
            $yyy_mark ='';

            $db_query['xxx_token'] = $xxx_mark;
            $db_query['yyy_token'] = '';
            $db_query['xxx_response'] = get_mark($json)['response'];
            $db_query['yyy_response'] = '';

            $new_name = markImage($files['name'][$i], $files['tmp_name'][$i], $xxx_mark, $local_dir);
            load_file("$directory/xxx", $new_name, basename($new_name));

        } elseif ($_POST['org'] == '3') {

            //когда акция только для YYY
            $json = get_json('yyy', $number, $month, $url);
            $yyy_mark = get_mark($json)['mark'];
            $table[$number]['yyy'] = $yyy_mark;
            $xxx_mark ='';

            $db_query['yyy_token'] = $yyy_mark;
            $db_query['xxx_token'] = '';
            $db_query['yyy_response'] = get_mark($json)['response'];
            $db_query['xxx_response'] = '';

            $new_name = markImage($files['name'][$i], $files['tmp_name'][$i], $yyy_mark, $local_dir);
            load_file("$directory/yyy", $new_name, basename($new_name));
        }

        //задаём ширину таблицы, если только одна площадка
        $w2 = (string) round( (95 * strlen($url) / strlen($url . $xxx_mark . $yyy_mark)), 2) . '%';
        $w3 = (string) round( (95 * strlen($xxx_mark) / strlen($url . $xxx_mark . $yyy_mark)), 2) . '%';
        $w4 = (string) round( (95 * strlen($yyy_mark) / strlen($url . $xxx_mark . $yyy_mark)), 2) . '%';

        //вывод строки таблицы по текущей листовке
        echo <<<EOT
        <tr>
            <td width="5%"><input type="text" class="form-control mt-1" id="mark" name="mark" value='$number'></td>
            <td width="$w2"><input type="text" class="form-control mt-1" id="mark" name="mark" value='$url'></td>
            <td width="$w3"><input type="text" class="form-control mt-1" id="mark" name="mark" value='$xxx_mark'></td>
            <td width="$w4"><input type="text" class="form-control mt-1" id="mark" name="mark" value='$yyy_mark'></td>
        </tr>
        EOT;

        //сохраняем данные по текущей листовке в БД и csv-файле
        $db->save('ord', $db_query);
        fwrite($fp, $number . ';' . $xxx_mark . ';' . $yyy_mark . ";\n");
    }

    //завершаем вывод таблицы, сохраняем csv на Яндекс.Диск и закрываем его
    echo '<tr><td></td><td></td><td></td><td> <a href="' . $csv_file . '">Скачать в CSV</a></td></tr>';
    echo '</table><br>';
    load_file("$directory/csv", $csv_file, basename($csv_file));
    fclose($fp);
}

?>

</body>
</html>