<aside id="sidebar" class="rounded-3 shadow-lg ms-2 bg-body-tertiary">

    <div class="d-flex flex-column align-items-center sidebar-logo">
        <div class="logo-icon"><a href="dashboard"><i class="bi bi-android2"></i></a></div>
        <div class="logo-name">BFMSI</div>
    </div>

    <ul class="sidebar-nav mx-2 my-0">

    <?php
    if($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Vendor" || $_SESSION["profile"] == "Inspector"){
        echo'
        <li class="sidebar-item">
            <a href="dashboard" class="sidebar-link rounded-3 has-dropdown collapsed" data-bs-toggle="collapse" data-bs-target="#marketmonitoring" aria-expanded="false" aria-controls="marketmonitoring">
                <i class="fas fa-clipboard-list"></i>
                <span>Compliance</span>
            </a>
            <ul id="marketmonitoring" class="sidebar-dropdown mx-2 list-unstyled collapse" data-bs-parent="#sidebar">';
    }
    if($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Vendor"){
        echo'
                <li class="sidebar-item">
                    <a href="registration" class="sidebar-link rounded-3"><i class="fas fa-calendar-alt"></i><span>Registration</span></a>
                </li>';
    }
    if($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Inspector"){
        echo'
                <li class="sidebar-item">
                    <a href="inspection" class="sidebar-link rounded-3"><i class="fas fa-binoculars"></i><span>Inspection</span></a>
                </li>';
    }
    if($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Vendor"){
        echo'
                <li class="sidebar-item">
                    <a href="marketlist" class="sidebar-link rounded-3"><i class="fas fa-registered"></i><span>Market list</span></a>
                </li>';
    }
        echo'
            </ul>
        </li>';
    
    if($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Vendor" || $_SESSION["profile"] == "Inspector"){
        echo'
        <li class="sidebar-item">
            <a href="market1" class="sidebar-link rounded-3 has-dropdown collapsed" data-bs-toggle="collapse" data-bs-target="#marketrating" aria-expanded="false" aria-controls="marketrating">
                <i class="fas fa-star"></i>
                <span>Rating review</span>
            </a>
            <ul id="marketrating" class="sidebar-dropdown mx-2 list-unstyled collapse" data-bs-parent="#sidebar">
                <li class="sidebar-item">
                    <a href="ratingandfeedback" class="sidebar-link rounded-3"><i class="fas fa-comment"></i><span>Rating and feedback</span></a>
                </li>
            </ul>
        </li>';
    }
    
    if($_SESSION["profile"] == "Administrator"){
        echo'
        <li class="sidebar-item">
            <a href="market2" class="sidebar-link rounded-3 has-dropdown collapsed" data-bs-toggle="collapse" data-bs-target="#marketresults" aria-expanded="false" aria-controls="marketresults">
                <i class="fas fa-mask"></i>
                <span>Inspection results</span>
            </a>
            <ul id="marketresults" class="sidebar-dropdown mx-2 list-unstyled collapse" data-bs-parent="#sidebar">
                <li class="sidebar-item">
                    <a href="inspectionreport" class="sidebar-link rounded-3"><i class="fas fa-record-vinyl"></i><span>Inspection Report</span></a>
                </li>
            </ul>
        </li>';
    }
    
    if($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Vendor"){
        echo'
        <li class="sidebar-item">
            <a href="certificate1" class="sidebar-link rounded-3 has-dropdown collapsed" data-bs-toggle="collapse" data-bs-target="#marketcertificates" aria-expanded="false" aria-controls="marketcertificates">
                <i class="fas fa-certificate"></i>
                <span>Permits</span>
            </a>
            <ul id="marketcertificates" class="sidebar-dropdown mx-2 list-unstyled collapse" data-bs-parent="#sidebar">
                <li class="sidebar-item">
                    <a href="permittracker" class="sidebar-link rounded-3"><i class="fas fa-stamp"></i><span>Permit tracking</span></a>
                </li>
                <li class="sidebar-item">
                    <a href="notifications" class="sidebar-link rounded-3"><i class="fas fa-bell"></i><span>Notifications</span></a>
                </li>
            </ul>
        </li>';
    }

        if($_SESSION["profile"] == "Administrator" || $_SESSION["profile"] == "Vendor" || $_SESSION["profile"] == "Inspector"){
            echo'
        <li class="sidebar-item">
            <a href="categorylocator" class="sidebar-link rounded-3">
                <i class="fas fa-map-marked"></i>
                <span>Category locator</span>
            </a>
        </li>
        

        <li class="sidebar-item">
                <a href="usermanagement" class="sidebar-link rounded-3">
                    <i class="fas fa-user-cog"></i>
                    <span>User management</span>
                </a>
            </li>';
        }

    ?>

    </aside>