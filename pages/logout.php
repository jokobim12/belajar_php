<?php
require_once '../include/auth.php';

session_destroy();
header('Location: ./login.php');
exit;
?>
