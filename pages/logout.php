<?php
// pages/logout.php
// User navigates to this URL to log out, so it belongs in pages/
session_start();
session_unset();
session_destroy();
header("Location: ../home.php");
exit;
