<?php
define('PATH', $_SERVER['DOCUMENT_ROOT']);
define('GB', PATH.'/upload/gb.xml');

spl_autoload_register(function ($class) {
    $path = PATH . '/clss/' . $class . '.php';
    if (file_exists($path)) {
        include $path;
    }
});

$obj = new ClassMain();

if (!empty($_POST)) {
    $obj->ajaxSet($_POST);
}

// проверка отправки git
$obj->IncludeTemplate('index', null);
?>