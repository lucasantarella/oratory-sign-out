<?php

$file = file_get_contents(__DIR__ . "/schemas.sql");

$tables = explode(";\n", $file);

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

foreach ($tables as $table) {
    $name = get_string_between($table, "CREATE TABLE `", "` (");
    $table = ltrim($table, "\n");
    file_put_contents(__DIR__ . "/oratory_sign_out_" . $name . ".sql", $table . ";");
}