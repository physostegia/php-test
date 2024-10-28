<?php

require 'vendor/autoload.php'; // Подключение автозагрузчика Composer

use League\Csv\Reader;

// Загружаем CSV-документ из файла
$csv = Reader::createFromPath('сетка.csv', 'r');
$csv->setHeaderOffset(0);

$records = $csv->getRecords(); // Итератор, содержащий массивы записей

foreach ($records as $record) {
    print_r($record); // Вывод каждой записи
}
