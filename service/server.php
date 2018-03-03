<?php
    $uri = $_SERVER['REQUEST_URI'];
    $method = $_SERVER['REQUEST_METHOD'];
    $paths = explode('/', $uri);
    require '../functions.php';
    session_start();
?>
