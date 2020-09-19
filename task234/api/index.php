<?php
define("ROOT_DIR", dirname(__FILE__) . '/');

function autoloader($class)
{
    $dirClassNames = [
        'controller',
        'config',
        'helpers'
    ];

    foreach ($dirClassNames as $name) {
        $path = ROOT_DIR . "$name/$class.php";
        if (file_exists($path)) {
            include_once $path;
        }
    }
}

spl_autoload_register('autoloader');

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

if (!empty($_GET['route'])) {
    $parseRoute = explode('_', $_GET['route']);
    if (!empty($parseRoute)) {
        $action = ucfirst($parseRoute[0]);
        unset($parseRoute[0]);
        $controller = implode('', array_map(function ($element) {
            return ucfirst($element);
        }, $parseRoute));

        if (class_exists("$controller", true)) {
            $object = new $controller();
            $action = "action$action";
            unset($_GET['route']);
            try {
                echo $object->$action();
            } catch (Throwable $e) {
                http_response_code(500);
                echo json_encode(Response::format("0", 'Server error'));
            }
        } else {
            http_response_code(404);
            echo json_encode(Response::format("0", "Not Found " . $_GET['route']));
        }
    } else {
        http_response_code(400);
        echo json_encode(Response::format("0", "Not valid method name. For example need 'get_table_data'"));
    }
} else {
    http_response_code(400);
    echo json_encode(Response::format("0", "Incorrect URL"));
}