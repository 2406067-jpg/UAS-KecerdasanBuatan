<?php
session_start();
$_SESSION = [];
session_unset();
session_destroy();

// Balikin ke gerbang login
header("Location: login.php");
exit;