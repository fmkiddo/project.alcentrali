<!DOCTYPE html>
<html lang="<?php echo isset ($appLocale) ? $appLocale : 'en-US'; ?>">
	<head>
		<meta charset="<?php echo isset ($appCharset) ? $appCharset : 'UTF-8'; ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<title></title>
		<link rel="stylesheet" href="<?php echo isset ($assetsFolder) ? $assetsFolder : ''; ?>/vendors/bootstrap/css/bootstrap.min.css" />
		<link rel="stylesheet" href="<?php echo isset ($assetsFolder) ? $assetsFolder : ''; ?>/vendors/fontawesome/css/all.min.css" />
		<link rel="stylesheet" href="<?php echo isset ($assetsFolder) ? $assetsFolder : ''; ?>/css/setup-theme.css" />
	</head>
	<body>
		<div id="container-fluid">
			<header class="setup-header alert alert-bloodred">
				<div class="container">
					<nav class="d-flex justify-content-between">
						<div class="nav-brand">
							<a href="https://www.rizckyfm.com">
								<img src="" alt="Rizcky N. Ardhy" />
							</a>
						</div>
						
						<div class="nav navbar">
							
						</div>
					</nav>
				</div>
			</header>
			
			<div class="setup-title">
			
			</div>
			
			<div class="setup-body">
			
			</div>
			
			<footer class="setup-footer">
			
			</footer>
		</div>
	
		<div class="modal fade" tabindex="-1" role="dialog" aria-labelled="fmkModelDialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title"></h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					
					<div class="modal-body">
					</div>
					
					<div class="modal-footer">
					</div>
				</div>
			</div>
		</div>
		
		<script src="<?php echo isset ($assetsFolder) ? $assetsFolder : ''; ?>/vendors/jquery/jquery-3.4.1.min.js"></script>
		<script src="<?php echo isset ($assetsFolder) ? $assetsFolder : ''; ?>/vendors/bootstrap/js/bootstrap.min.js"></script>
		<script src="<?php echo isset ($assetsFolder) ? $assetsFolder : ''; ?>/vendors/fontawesome/js/all.min.js"></script>
		<script src="<?php echo isset ($assetsFolder) ? $assetsFolder : ''; ?>/js/myScripts.js"></script>
		<script type="text/javascript">
		</script>
	</body>
</html>