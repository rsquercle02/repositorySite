<?php
// Start the session
session_start();

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page with a 301 status code (permanent redirect)
header("Location: portalindex.php", true, 301);
exit;
?>
