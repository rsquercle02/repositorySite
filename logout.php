<?php
// Start the session
session_start();

// Destroy the session
session_unset();
session_destroy();

// Set headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");  // Prevent cache
header("Pragma: no-cache");  // Prevent cache
header("Expires: 0");  // Set expiration date to past

// Redirect to login page with a 301 status code (permanent redirect)
header("Location: login.php", true, 301);
exit;
?>
