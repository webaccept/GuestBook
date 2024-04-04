<?php
define('PATH', $_SERVER['DOCUMENT_ROOT']);
define('GB', PATH . '/upload/gb.xml');

spl_autoload_register(function ($class) {
    $path = PATH . '/classes/' . $class . '.php';
    if (file_exists($path)) {
        include $path;
    }
});

$obj = new MainClass();

if (!empty($_POST)) {
    $obj->ajaxSet($_POST);
}

try {
    $obj->IncludeTemplate('index');
} catch (Exception $e) {
}