<?php
session_start();
session_destroy();
header('Location: parent_login.php');
exit;
?>
