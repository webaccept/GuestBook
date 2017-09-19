<?php
define('PATH', $_SERVER['DOCUMENT_ROOT']);
define('GB', PATH.'/upload/gb.xml');

spl_autoload_register(function ($class) {
    include PATH.'/clss/'.$class.'.php';
});

$obj = new ClassMain();

if (!empty($_POST)) {
    $obj->ajaxSet($_POST);
}

$obj->IncludeTemplate('index', null);

?>