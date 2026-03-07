<?php
require_once "session_bootstrap.php";
$_SESSION = [];
session_destroy();
header('Location: login.php');
exit;
?>