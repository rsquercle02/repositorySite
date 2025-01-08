<header class="navdiv sticky-top">
    <nav class="navbar navbar-expand rounded-3 shadow-lg m-3 scroll bg-body-tertiary">
        <div class="container ms-0">
            <a class="navbar-brand" id="sidebar-toggle" href="#">
                <i class="bi bi-list"></i>
            </a>
        </div>
                        
        <div class="dropdown">
            <div class="container ms-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <!--<i class="bi bi-person-circle"></i>-->
                <?php 

							if ($_SESSION["picture"] != "") {
								
								echo '<img class="profilepicture1 rounded-5" src="'.$_SESSION["picture"].'"class="user-image">';
							
							}else{

								echo '<img class="user-image" src="views/img/users/default/anonymous.png">';
							}

						?>
						
            </div>
            <ul class="dropdown-menu dropdown-menu-end">
                <span class="hidden-xs m-3"><?php echo $_SESSION["username"]; ?></span>
                <li><a class="dropdown-item" href="logout">Log out</a></li>
            </ul>
        </div>
    </nav>
</header>