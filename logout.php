<?php
session_start();
session_destroy();

// Cookie'leri temizle
setcookie('remember_user', '', time() - 3600, '/');
setcookie('remember_token', '', time() - 3600, '/');

header("Location: login.php");
exit();
?>