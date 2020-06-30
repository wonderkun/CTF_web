<?php
function response(array $data = [], bool $success = true, string $message = ""): void
{
    $callback = $_REQUEST['callback'] ?? null;
    $_data = ['success' => $success, 'message' => $message, 'data' => $data];
    if ($callback) {
        echo sprintf("%s(%s)", $callback, json_encode($_data));
    } else {
        echo json_encode($_data);
    }
}

switch ($_SERVER['PATH_INFO']) {
    case '/qwq':
        response([
            'title' => 'uwu',
        ]);
        break;
    default:
        header(sprintf("%s 404 Not Found", $_SERVER['SERVER_PROTOCOL']));
        die('api not found.');
}
