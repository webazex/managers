<?php 
// 1. Устанавливаем заголовок Content-Type
header('Content-Type: application/json; charset=utf-8');

// 2. Данные, которые нужно вернуть
$data = [
    'status' => 'success',
    'message' => 'Data saved',
];

// 3. Кодируем в JSON и выводим
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

// 4. Завершаем скрипт
exit();
