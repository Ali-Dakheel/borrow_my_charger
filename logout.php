<?php
require_once __DIR__ . '/Core/bootstrap.php';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $session->destroy();
    header('Location: index.php');
    exit;
}
