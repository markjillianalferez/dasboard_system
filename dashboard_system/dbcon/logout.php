<?php
session_start();  // Start the session

// Unset all session variables to log out the user
session_unset();

// Destroy the session
session_destroy();

// Redirect the user to the login page or home page
header("Location: ../index.php");  // Adjust the URL to your login or home page
exit();
?>
