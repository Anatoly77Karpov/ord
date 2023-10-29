<?php

function markImage($name, $image, $mark, $local_dir) {

$font = "assets/arial.ttf";
$font_size = 8;
$angle = 0;

$width = getimagesize($image)[0];
$height = getimagesize($image)[1];

//вычисляем новые высоту и ширину, если наибольшая сторона отличается от 1000 пикселей более чем на 10%
if (abs(max([$width, $height]) / 1000 - 1) > 0.1) {
    $ratio = 1000 / max([$width, $height]);
    $width *= $ratio;
    $width = round($width);
    $height *= $ratio;
    $height = round($height);
}

//определяем размеры текста (через координаты 4х точек вокруг текста)
$size = imagettfbbox($font_size, $angle, $font, $mark);
$text_width = abs($size[4] - $size[6]);
$text_height = abs($size[1] - $size[7]);

//формируем координаты для размещения текста на изображении с отступом 7 пикселеей
$x = $width - ($size[4] - $size[6]) - 7;
$y = $height - 7;

//создаем изображение-объект для работы в библиотеке GD с учётом расширения файла
$extension = pathinfo($name, PATHINFO_EXTENSION);
if ($extension == 'png') {

    $img = imagecreatefrompng($image);
    $img = imagescale($img, $width);

} elseif ($extension == 'jpeg' or $extension == 'jpg' or $extension == 'jfif') {

    $img = imagecreatefromjpeg($image);
    $img = imagescale($img, $width);

} else {

    echo 'Неверный формат изображения, подходит только jpg/jpeg/jfif или png!';
    die();

}

//задаем цвет для текста и фона плашки
$black = imagecolorallocate($img, 0, 0, 0);
$background = imagecolorallocatealpha($img, 255, 255, 255, 96);

//размещаем полупрозрачную плашку и поверх неё текст
imagefilledrectangle($img, $x - 2, $y - $text_height - 2, $x + $text_width + 2, $y + 2, $background);
imagettftext($img, $font_size, $angle, $x, $y, $black, $font, $mark);

//формируем новое имя листовки и сохраняем её

    //определяем номер акции
    preg_match('#^(\d+)#su', $name, $match);
    $number = $match[1];

    //определяем для какой площадки производится маркировка
    if (mb_strpos($mark, 'XXX') !== false) {
        $place = 'xxx';
    } elseif (mb_strpos($mark, 'YYY') !== false) {
        $place = 'yyy';
    }

    //формируем имя промаркированной листовки
    $clean_name = mb_strstr($name, '_');//очищаем имя от номера, идущего до первого символа '_'
    $clean_name = preg_replace('#\.[^.]+$#su', '', $clean_name);//удаляем расширение файла .jpg, .png, .gif

    //сохраняем маркированную листовку на локальный диск
    if ($extension == 'png') {

        $new_name = "$local_dir/$place/$number" . "_" . "$place$clean_name.png";
        imagepng($img, $new_name);
    
    } elseif ($extension == 'jpeg' or $extension == 'jpg' or $extension == 'jfif') {
    
        $new_name = "$local_dir/$place/$number" . "_" . "$place$clean_name.jpg";
        imagejpeg($img, $new_name);
    
    }

//освобождаем память
imagedestroy($img);

return $new_name;

}
?>