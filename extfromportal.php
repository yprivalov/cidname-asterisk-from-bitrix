#!/usr/bin/php
<?php

// Функция транслитерации
function rus2translit($string) {
    $converter = array(
        'а' => 'a',   'б' => 'b',   'в' => 'v',
        'г' => 'g',   'д' => 'd',   'е' => 'e',
        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
        'и' => 'i',   'й' => 'y',   'к' => 'k',
        'л' => 'l',   'м' => 'm',   'н' => 'n',
        'о' => 'o',   'п' => 'p',   'р' => 'r',
        'с' => 's',   'т' => 't',   'у' => 'u',
        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
        'ь' => '',  'ы' => 'y',   'ъ' => '',
        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',

        'А' => 'A',   'Б' => 'B',   'В' => 'V',
        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
        'И' => 'I',   'Й' => 'Y',   'К' => 'K',
        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
        'О' => 'O',   'П' => 'P',   'Р' => 'R',
        'С' => 'S',   'Т' => 'T',   'У' => 'U',
        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
        'Ь' => '',  'Ы' => 'Y',   'Ъ' => '',
        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
    );
    return strtr($string, $converter);
}

function str2url($str) {
    // переводим в транслит
    $str = rus2translit($str);
    // в нижний регистр
    $str = strtolower($str);
    // заменям все ненужное нам на "-"
    $str = preg_replace('~[^-a-z0-9_]+~u', '-', $str);
    // удаляем начальные и конечные '-'
    $str = trim($str, "-");
    return $str;
}

// Подключаемся к БД Bitrix
$db1 = mysql_connect('server', 'user', 'password');
mysql_select_db('database_bitrix', $db1);
mysql_query('SET NAMES utf8', $db1);

// Запрашиваем Имя Фамилию и внутренний телефон активных сотрудников
$sql1 = 'SELECT concat(u.Last_Name," ",u.Name), uu.uf_phone_inner FROM b_user u join b_uts_user uu on uu.value_id = u.id WHERE uu.uf_phone_inner and active="Y"';

$res = mysql_query($sql1, $db1);

// Цикл по всем полученным записям из БД портала Bitrix
while ($row = mysql_fetch_assoc($res))
{
    // Получаем Имя и Фамилию и переводим в транслит
    $Name = rus2translit($row['concat(u.Last_Name," ",u.Name)']);
    // Получаем внутренний телефон, используем первые 4е цифры
    $Tel = substr($row['uf_phone_inner'],0,4);
    // Записываем полученный номер в cidname абонента
    $str = "/usr/sbin/asterisk -rx 'database put AMPUSER $Tel/cidname \"$Name\"'";
    $output = shell_exec($str);
}

// Для некоторых абонентов явно укажем cidname
$const1 = "/usr/sbin/asterisk -rx 'database put AMPUSER 2101/cidname \"Reception\"'";
$const2 = "/usr/sbin/asterisk -rx 'database put AMPUSER 2198/cidname \"Starshiy Smeni\"'";

$output = shell_exec($const1);
$output = shell_exec($const2);

?>
