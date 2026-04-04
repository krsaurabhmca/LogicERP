<?php
/**
 * Logout Handler
 * LogicERP Modular Framework
 */
session_start();
session_destroy();
header("Location: login.php?msg=Logged out successfully");
exit();
?>
