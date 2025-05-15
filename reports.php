<?php
require_once __DIR__ . '/Core/bootstrap.php';

use Core\Database;

$db = new Database();
$error = '';
$results = [];
$query = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $query = $_POST['query'] ?? '';
    
    try {
        $db->query($query);
        $results = $db->findAll();
    } catch (PDOException $e) {
        $error = 'Database Error: ' . $e->getMessage();
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

require_once 'Views/admin/reports.phtml';