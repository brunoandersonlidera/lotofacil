<?php
require_once realpath(__DIR__ . '/../includes/auth.php');
session_destroy();
header('Location: login.php');
exit;
?>