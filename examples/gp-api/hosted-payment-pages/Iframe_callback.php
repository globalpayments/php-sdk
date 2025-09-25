<?php
    $PHP_input = file_get_contents('php://input');
    $jsonData = json_decode($PHP_input, true);
    error_log("iframe_callback.php file_get_contents('php://input')  data:" . print_r($jsonData, true));
    if($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
        
        $jsonData = json_decode($_POST, true);

        error_log("iframe_callback.php POST request received with data: " . print_r($jsonData, true));
    }
?>