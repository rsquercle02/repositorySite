<?php
session_start();

//$_SESSION['id'] = '25';
//$_SESSION['profile'] = 'Administrator';
//$_SESSION['barangayRole'] = 'Secretary';
//$_SESSION['loggedIn'] = 'ok';

if (!isset($_SESSION['id'])) {
  header("Location: index.php");
  exit();
}

if (isset($_SESSION['status']) && $_SESSION['status'] == "Inactive") {
  header("Location: inactive.html");
  exit();
}

if (!isset($_SESSION['profile']) || !in_array($_SESSION['profile'], ['Super Administrator', 'Administrator', 'User'])) {
  header("Location: unauthorizeduser.html");
  exit();
}



?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" href="assets/images/unified-lgu-logo.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.6.0/css/fontawesome.min.css">
    <link rel ="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <title>BFMS</title>

    <!-- Simple bar CSS (for scvrollbar)-->
    <link rel="stylesheet" href="css/simplebar.css">
    <!-- Fonts CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Overpass:ital,wght@0,100;0,200;0,300;0,400;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <!-- Icons CSS -->
    <link rel="stylesheet" href="css/feather.css">
    <!-- App CSS -->
    <link rel="stylesheet" href="css/main.css">   
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
    .avatar-initials {
    width: 165px;
    height: 165px;
    border-radius: 50%;
    display: flex;
    margin-left: 8px;
    justify-content: center;
    align-items: center;
    font-size: 50px;
    font-weight: bold;
    color: #fff;
    
    }

    .avatar-initials-min {
    width: 40px;
    height: 40px;
    background: #75e6da;
    border-radius: 50%;
    display: flex;
    margin-left: 8px;
    justify-content: center;
    align-items: center;
    font-size: 14px;
    font-weight: bold;
    color: #fff;
    
  }

    .upload-icon {
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  cursor: pointer;
  font-size: 24px;
  color: #fff;
  opacity: 0;
  transition: opacity 0.3s ease-in-out;
  background-color: #333;
  padding: 10px;
  border-radius: 50%;
  z-index: 1;
}

.avatar-img:hover .upload-icon {
  opacity: 1;
}

.avatar-img {
  position: relative;
  transition: background-color 0.3s ease-in-out;
}

.avatar-img:hover {
  background-color: #a0f0e6;
}

</style>
  
  </head>

    
    <div class="loader-mask">
        <div class="loader">
            <div></div>
            <div></div>
        </div>
    </div>
    
 
  <body class="vertical  light">
    <div class="wrapper">
      <nav class="topnav navbar navbar-light">
        <button type="button" class="navbar-toggler text-muted mt-2 p-0 mr-3 collapseSidebar">
          <i class="fe fe-menu navbar-toggler-icon"></i>
        </button>

        <ul class="nav">

          <li class="nav-item dropdown">
            <span class="nav-link text-muted pr-0 avatar-icon" href="#" id="navbarDropdownMenuLink" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="avatar avatar-sm mt-2">
  <div class="avatar-img rounded-circle avatar-initials-min text-center position-relative">
    <!-- <img class="rounded-circle" src="users/admin1/122.jpg"> -->
    <?php 
    if ($_SESSION["picture"] != "") {
      echo '<img class="rounded-circle" src="'.$_SESSION["picture"].'">';
    }else{
      echo '<img class="rounded-circle" src="assets/images/anonymous.svg">';
    }
    ?>
  </div>
</span>
</span>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
              <a class="dropdown-item" href="#"><i class="fe fe-user"></i>&nbsp;&nbsp;&nbsp;Profile</a>
              <a class="dropdown-log-out" href="logout"><i class="fe fe-log-out"></i>&nbsp;&nbsp;&nbsp;Log Out</a>
            </div>    
          </li>
        </ul>
      </nav>


      <aside class="sidebar-left border-right bg-white " id="leftSidebar" data-simplebar>
        <a href="#" class="btn collapseSidebar toggle-btn d-lg-none text-muted ml-2 mt-3" data-toggle="toggle">
          <i class="fe fe-x"><span class="sr-only"></span></i>
        </a>

        <nav class="vertnav navbar-side navbar-light">
          <!-- nav bar -->
          <div class="w-100 mb-4 d-flex">
            <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="dashboard">
              
                
                <img src="assets/images/unified-lgu-logo.png" width="45">
              

            <div class="brand-title">
            <br>
              <span>LGU4 - Barangay Food Market Safety</span>
            </div>
                       
            </a>

          </div>

          <!--Sidebar ito-->
          <?php
          if((isset($_SESSION["profile"]) && $_SESSION["profile"] == "Super Administrator") || (isset($_SESSION["profile"]) && $_SESSION["profile"] == "Administrator")|| (isset($_SESSION["profile"]) && $_SESSION["profile"] == "User")){
            echo'
          <ul class="navbar-nav active flex-fill w-100 mb-2">
            <li class="nav-item dropdown">
              <a class="nav-link" href="dashboard">
              <i class="fas fa-chart-line"></i>
                <span class="ml-3 item-text">BFMS Dashboard</span>
              </a>
            </li>
          </ul>

          <p class="text-muted-nav nav-heading mt-4 mb-1">
          <span style="font-size: 10.5px; font-weight: bold; font-family: "Inter", sans-serif;">MAIN COMPONENTS</span>
          </p>';
          }

          if(isset($_SESSION["profile"]) && $_SESSION["profile"] == "Administrator"){
          echo'
          <ul class="navbar-nav flex-fill w-100 mb-2">
            <li class="nav-item w-100">
              <a class="nav-link" href="safetymonitoring">
              <i class="fa-solid fa-clipboard-list"></i>
                <span class="ml-3 item-text">Safety monitoring</span>
              </a>
            </li>
          </ul>

          <ul class="navbar-nav flex-fill w-100 mb-2">
            <li class="nav-item w-100">
              <a class="nav-link" href="concernslist">
              <i class="fa-solid fa-clipboard-list"></i>
                <span class="ml-3 item-text">Concerns List</span>
              </a>
            </li>
          </ul>';
          }

          if(isset($_SESSION["profile"]) && ($_SESSION['profile'] == 'Administrator' || $_SESSION["profile"] == "User")){
            echo'
            <ul class="navbar-nav flex-fill w-100 mb-2">
              <li class="nav-item w-100">
                <a class="nav-link" href="reportlist">
                <i class="fa-solid fa-clipboard-list"></i>
                  <span class="ml-3 item-text">Report list</span>
                </a>
              </li>
            </ul>';
            }

          if (isset($_SESSION['profile']) && ($_SESSION['profile'] == 'Super Administrator' || $_SESSION['profile'] == 'Administrator')) {
            echo'
            <ul class="navbar-nav flex-fill w-100 mb-2">
            <li class="nav-item dropdown">
              <a class="nav-link" href="tracking">
              <i class="fa-solid fa-chart-simple"></i>
                <span class="ml-3 item-text">Tracking</span>
              </a>
            </li>
          </ul>
          ';
          }

          if (isset($_SESSION['profile']) && $_SESSION['profile'] == 'Super Administrator') {
          echo'
          <ul class="navbar-nav flex-fill w-100 mb-2">
            <li class="nav-item dropdown">
              <a class="nav-link" href="reportlist">
              <i class="fa-solid fa-clipboard-list"></i>
                <span class="ml-3 item-text">Report list</span>
              </a>
            </li>
          </ul>

          <ul class="navbar-nav flex-fill w-100 mb-2">
            <li class="nav-item dropdown">
              <a class="nav-link" href="usermanagement">
              <i class="fa-solid fa-user"></i>
                <span class="ml-3 item-text">User Management</span>
              </a>
            </li>
          </ul>';
        }
        ?>
        </nav>
      </aside>

      <main role="main" class="main-content">
        <!--For Notification header naman ito-->

        <div class="modal fade modal-notif modal-slide" tabindex="-1" role="dialog" aria-labelledby="defaultModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="defaultModalLabel">Notifications</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>


              <div class="modal-body">
  <div class="list-group list-group-flush my-n3">
   
      <div class="col-12 mb-4">
        <div class="alert alert-success alert-dismissible fade show" role="alert" id="notification">
          <img class="fade show" src="../../assets/images/unified-lgu-logo.png" width="35" height="35">
          <strong style="font-size:12px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></strong> 
          <button type="button" class="close" data-dismiss="alert" aria-label="Close" onclick="removeNotification()">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div> <!-- /. col -->

    <div id="no-notifications" style="display: none; text-align:center; margin-top:10px;">No notifications</div>
  </div> <!-- / .list-group -->
 
</div>

<div class="modal-footer">
<button type="button" class="btn btn-secondary btn-block" onclick="clearAllNotifications()">Clear All</button>
              </div>
            </div>
          </div>
        </div>



      <!--YOUR CONTENTHERE-->
      <?php
        $userProfile = $_SESSION['profile'];
        $barangayRole = $_SESSION['barangayRole'];
        
        if(isset($_SESSION["loggedIn"]) && $_SESSION["loggedIn"] == "ok"){
            echo '<div class="scrollcontent">';
            $superadminRoutes = ['dashboard', 'safetymonitoring', 'reportlist', 'tracking', 'usermanagement'];
            $adminRoutes = ['dashboard', 'safetymonitoring', 'concernslist', 'reportlist', 'tracking', 'usermanagement'];
            $userRoutes = ['dashboard', 'reportlist', 'ratingandfeedback', 'inspectionreport', 'categorylocator', 'usermanagement'];
            if (isset($_GET["route"])) {
                $route = basename($_GET["route"]);
                if ($_SESSION['profile'] == 'Super Administrator' && $_SESSION['barangayRole'] == 'Captain' && in_array($route, $superadminRoutes)) {
                  include "superadmin_pages/" . $route . ".html";
                } else if ($_SESSION['profile'] == 'Administrator' && $_SESSION['barangayRole'] == 'Secretary' && in_array($route, $adminRoutes)) {
                include "admin_pages/" . $route . ".html";
                } else if ($_SESSION['profile'] == 'User' && $_SESSION['barangayRole'] == 'Kagawad 1' && in_array($route, $userRoutes)) {       
                include "user_pages/k1/" . $route . ".html";
                } else if ($_SESSION['profile'] == 'User' && $_SESSION['barangayRole'] == 'Kagawad 2' && in_array($route, $userRoutes)) {  
                include "user_pages/k2/" . $route . ".html";
                } else if ($_SESSION['profile'] == 'User' && $_SESSION['barangayRole'] == 'Kagawad 3' && in_array($route, $userRoutes)) {
                include "user_pages/k3/" . $route . ".html";
                } elseif ($_GET["route"] == 'logout') {
                include "".$_GET["route"].".php";
                } else {
                include "modules/404.php";
                }
            } else {
              // Include the appropriate page based on the user profile
                if ($userProfile == 'Administrator') {
                  include "admin_pages/dashboard.html";
              } elseif ($userProfile == 'Super Administrator') {
                  include "superadmin_pages/dashboard.html";  // You can change the path as needed
              } else if ($_SESSION['profile'] == 'User' && $_SESSION['barangayRole'] == 'Kagawad 1') {       
                include "user_pages/k1/dashboard.html";
              } else if ($_SESSION['profile'] == 'User' && $_SESSION['barangayRole'] == 'Kagawad 2') {  
                include "user_pages/k2/dashboard.html";
              } else if ($_SESSION['profile'] == 'User' && $_SESSION['barangayRole'] == 'Kagawad 3') {
                include "user_pages/k3/dashboard.html";
              }
            }
             include "footer.php";
             echo '</div>';
        }else{
        include "login.php";
        }
      ?>

      
      
      
  <!-- Include jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- <script src="js/jquery.min.js"></script> -->
  <!-- <script src="js/popper.min.js"></script> -->
  <!-- <script src="js/moment.min.js"></script> -->
  <script src="js/bootstrap.min.js"></script>
  <script src="js/simplebar.min.js"></script>
  <!-- <script src='js/daterangepicker.js'></script> -->
  <!-- <script src='js/jquery.stickOnScroll.js'></script> -->
  <!-- <script src="js/tinycolor-min.js"></script>
  <script src="js/d3.min.js"></script>
  <script src="js/topojson.min.js"></script>
  <script src="js/Chart.min.js"></script>
  <script src="js/gauge.min.js"></script>
  <script src="js/jquery.sparkline.min.js"></script>
  <script src="js/apexcharts.min.js"></script>
  <script src="js/apexcharts.custom.js"></script> -->
  <!-- <script src='js/jquery.mask.min.js'></script> -->
  <!-- <script src='js/select2.min.js'></script>
  <script src='js/jquery.steps.min.js'></script>
  <script src='js/jquery.validate.min.js'></script>
  <script src='js/jquery.timepicker.js'></script>
  <script src='js/dropzone.min.js'></script>
  <script src='js/uppy.min.js'></script>
  <script src='js/quill.min.js'></script> -->
  <script src="js/apps.js"></script>
  <script src="js/preloader.js"></script>
  <!-- <script src="js/jquery-3.6.0.min.js" crossorigin="anonymous"></script> -->
  <script src="js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
  <!-- <script src='js/jquery.dataTables.min.js'></script> -->
  <!-- <script src='js/dataTables.bootstrap4.min.js'></script> -->
  <!-- <script src='js/session-timeout.js'></script> -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  </body>
</html>

