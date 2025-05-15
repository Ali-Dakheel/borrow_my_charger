<?php
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Session.php';

use Core\Database;
use Core\Session;
date_default_timezone_set('Asia/Bahrain');
// Start a session
$session = new Session();   
$db = new Database();
