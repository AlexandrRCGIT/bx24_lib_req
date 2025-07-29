<?php
// bx24_req/autoload.php
spl_autoload_register(function ($class) {
    // Пространство имен вашей библиотеки
    $prefix = 'Bx24\\';

    // Базовый каталог для файлов классов
    $base_dir = __DIR__ . '/src/';

    // Проверяем, относится ли класс к нашему пространству имен
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Получаем относительное имя класса
    $relative_class = substr($class, $len);

    // Создаем путь к файлу
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Если файл существует, подключаем его
    if (file_exists($file)) {
        require $file;
    }
});
