<?php
  session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="templatestyle.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
</head>
<body>
    
    <?php
        $userProfile = $_SESSION['profile'];
        
        if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] == "ok"){
            echo '<div class="wrapper">';
            include "sidebar.php";
            echo '<div class="main">';
            include "header.php";
            echo '<div class="scrollcontent">';
            $adminRoutes = ['dashboard', 'registration', 'inspection', 'marketlist', 'ratingandfeedback', 'inspectionreport', 'permittracker', 'notifications', 'categorylocator', 'usermanagement'];
            $inspectorRoutes = ['dashboard', 'inspection', 'ratingandfeedback', 'inspectionreport', 'categorylocator', 'usermanagement'];
            $vendorRoutes = ['dashboard', 'registration', 'marketlist', 'ratingandfeedback', 'permittracker', 'notifications', 'categorylocator', 'usermanagement'];
            if (isset($_GET["route"])) {
                $route = $_GET["route"];
                if ($userProfile == 'Administrator' && in_array($route, $adminRoutes)) {
                include "admin/" . $route . ".php";
                } elseif ($userProfile == 'Inspector' && in_array($route, $inspectorRoutes)) {
                include "inspector_user/" . $route . ".php";
                } elseif ($userProfile == 'Vendor' && in_array($route, $vendorRoutes)) {
                include "vendor_user/" . $route . ".php";
                /*} elseif ($_GET["route"] == 'logout') {
                include "".$_GET["route"].".php";*/
                } else {
                include "modules/404.php";
                }
            } else {
                include "dashboard.php";
            }

             include "footer.php";
             echo '</div>';
             echo '</div>';
             echo '</div>';


        }else{
        include "login.php";
        }
            
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="templatescript.js"></script>

</body>
</html>