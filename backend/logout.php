<?php
require_once __DIR__ . '/../config/database.php';
initSession();
session_destroy();
header("Location: login.php");
exit();
?>
