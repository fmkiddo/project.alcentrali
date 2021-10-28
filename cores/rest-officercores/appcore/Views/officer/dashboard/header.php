<?php include 'html-header.php';?>

	<div id="wrapper">
		<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="dashboard-sidebar">
			<a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
				<div class="sidebar-brand-icon rotate-n-15">
					<i class="fas fa-laugh-wink fa-fw"></i>
				</div>
				<div class="sidebar-brand-text mx-3"><?php echo isset ($appname) ? $appname : '---App Name---'; ?></div>
			</a>
			
			<hr class="sidebar-divider my-0">
			
			<li class="nav-item">
				<a class="nav-link" href="index">
					<i class="fas fa-fw fa-tachometer-alt"></i>
					<span><?php echo isset ($text) ? $text['dashboard'] : 'Dashboard'; ?></span>
				</a>
			</li>
			
			<li class="nav-item">	
				<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
					aria-expanded="true" aria-controls="collapseTwo">
					<i class="fas fa-fw fa-database"></i>
					<span><?php echo isset ($text) ? $text['systemdata'] : 'System Data'; ?></span>
				</a>
				<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#dashboard-sidebar">
					<div class="bg-white py-2 collapse-inner rounded">
						<h6 class="collapse-header"><?php echo isset ($text) ? $text['systemdata'] : 'System Data'; ?></h6>
						<a class="collapse-item" href="client">Client</a>
						<a class="collapse-item" href="api">API</a>
					</div>
				</div>
			</li>
			
			<li class="nav-item">
				<a class="nav-link" href="officer">
					<i class="fas fa-users fa-fw"></i>
					<span><?php echo isset ($text) ? $text['officer'] : 'Officers'; ?></span>
				</a>
			</li>
			
			<li class="nav-item">
				<a class="nav-link" href="profile">
					<i class="fas fa-user fa-fw"></i>
					<span><?php echo isset ($text) ? $text['profile'] : 'Profile'; ?></span>
				</a>
			</li>
			
			<li class="nav-item">
				<a id="logout" class="nav-link">
					<i class="fas fa-sign-out-alt fa-fw"></i>
					<span><?php echo isset ($text) ? $text['logout'] : 'Logout'; ?></span>
				</a>
			</li>
			
			<hr class="sidebar-divider d-none d-md-block">
			
			<div class="text-center d-none d-md-inline">
				<button class="rounded-circle border-0" id="sidebar-toggle"></button>
			</div>
		</ul>
		
		<div id="content-wrapper" class="d-flex flex-column">
			<div id="content">
				<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
					<button id="sidebar-toggle-top" class="btn btn-link d-md-none rounded-circle mr-3">
						<i class="fa fa-bars"></i>
					</button>
					
					<form class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
						<div class="input-group">
							<input type="text" class="form-control bg-light border-0 small" placeholder="<?php echo isset ($text) ? $text['ph-search'] : 'Search for...'; ?>"
									aria-label="Search" aria-describedby="basic-addon2" />
							<div class="input-group-append">
								<button class="btn btn-primary" type="button">
									<i class="fas fa-search fa-sm"></i>
								</button>
							</div>
						</div>
					</form>
					
					<ul class="navbar-nav ml-auto">
						<li class="nav-item dropdown no-arrow d-sm-none">
							<a class="nav-link dropdown-toggle" href="#" id="search-dropdown" role="button"
									data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fas fa-search fa-fw"></i>
							</a>
							<div class="dropdown-menu dropdown-menu-right p-3 shadown animated--grow-in"
									aria-labelledby="search-dropdown">
								<form class="form-inline mr-auto w-100 navbar-search">
									<div class="input-group">
										<input type="text" class="form-control bg-light border-0 small" placeholder="<?php echo isset ($text) ? $text['ph-search'] : 'Search for...'; ?>"
												aria-label="Search" aria-describedby="basic-addon2" />
										<div class="input-group-append">
											<button class="btn btn-primary" type="button">
												<i class="fas fa-search fa-sm"></i>
											</button>
										</div>
									</div>
								</form>
							</div>
						</li>
						
					 	<li class="nav-item dropdown no-arrow mx-1">
					 		<a class="nav-link dropdown-toggle" href="#" id="alerts-dropdown" role="button"
					 				data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					 			<i class="fas fa-bell fa-fw"></i>
<?php
if (isset ($new_notif_alerts)):
	$newAlertsCount = count ($new_notif_alerts);
	if ($newAlertsCount > 0)
?>
								<span class="badge badge-danger badge-counter"><?php echo $newAlertsCount; ?></span>
<?php
endif;
?>
					 		</a>
					 		
					 		<div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
					 				aria-labelledby="alerts-dropdown">
<?php 
if (!isset ($new_notif_alerts) || count ($new_notif_alerts) == 0):
?>
								<a class="dropdown-item text-center small text-gray-800" href="#">
									<?php echo isset ($no_alerts) ? $no_alerts : 'There are no more alerts!'; ?>
								</a>
<?php 
else:
	
endif;
?>
					 			<a class="dropdown-item text-center small text-gray-500" href="#">Show all alerts</a>
					 		</div>
						</li>
						
						<li class="nav-item dropdown no-arrow mx-1">
							<a class="nav-link dropdown-toggle" href="#" id="messages-dropdown" role="button"
									data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="fas fa-envelope fa-fw"></i>
<?php 
if (isset ($new_notif_messages)):
	$newMsgsCount = count ($new_notif_messages);
	if ($newMsgsCount > 0)
?>
								<span class="badge badge-danger badge-counter"><?php echo $newMsgsCount; ?></span>
<?php 
endif;
?>
							</a>
							
							<div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
									aria-labelledby="messages-dropdown">
<?php 
if (!isset ($new_notif_messages) || count ($new_notif_messages) == 0):
?>
								<a class="dropdown-item text-center small text-gray-800" href="#"><?php echo isset ($no_msgs) ? $no_msgs : 'There are no more unread messages!'; ?></a>
<?php 
else:
endif;
?>
								<a class="dropdown-item text-center small text-gray-500" href="#">Show all messages</a>
							</div>
						</li>
						
						<li class="nav-item dropdown no-arrow">
							<a class="nav-link dropdown-toggle" href="#" id="profile-dropdown" role="button"
									data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo (isset ($text) ? $text['hitext'] : 'Helo') . ', ' . (isset ($profilename) ? $profilename : '--Your Name--'); ?></span>
								<img class="img-profile rounded-circle" src="" />
							</a>
							
							<div class="dropdown-menu dropdown-menu-right shadow animated-grow--in"
									aria-labelledby="profile-dropdown">
								<a class="dropdown-item" href="#">
									<i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profile
								</a>
								<a class="dropdown-item" href="#">
									<i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i> Settings
								</a>
								<a class="dropdown-item" href="#">
									<i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i> Activity Logs
								</a>
								<div class="dropdown-divider"></div>
								<a id="logout" class="dropdown-item" role="button">
									<i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> <?php echo isset ($text) ? $text['logout'] : 'Logout'; ?>

								</a>
							</div>
						</li>
					</ul>
				</nav>

				<div class="container-fluid">
				
					<!-- Page Heading -->
					<div class="d-sm-flex align-items-center justify-content-between mb-4">
						<h1 class="h3 mb-0 text-gray-800"><?php echo isset ($text) ? $text['title'] : '--- Page Title ---'; ?></h1>
						<!-- 
						<a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
							<i class="fas fa-download fa-sm text-white-50"></i> Generate Report
						</a>
						
						<div>
						</div>
						-->
					</div>