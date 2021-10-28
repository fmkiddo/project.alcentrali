<!DOCTYPE html>
<html lang="<?php echo isset ($locale) ? $locale : ''; ?>">
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
		<meta />
		<meta />
		<meta />
		<title></title>
		<link rel="stylesheet" href="<?php echo isset ($vendorAssetsPath) ? $vendorAssetsPath : ''; ?>/bootstrap/css/bootstrap.min.css" />
		<link rel="stylesheet" href="<?php echo isset ($vendorAssetsPath) ? $vendorAssetsPath : ''; ?>/fontawesome/css/all.min.css" />
		<link rel="stylesheet" href="<?php echo isset ($vendorAssetsPath) ? $vendorAssetsPath : ''; ?>/fonts/fondamento/stylesheet.css" />
		<link rel="stylesheet" href="<?php echo isset ($vendorAssetsPath) ? $vendorAssetsPath : ''; ?>/fonts/roboto-mono/stylesheet.css" />
		<?php 
		if (isset ($addrels)) 
			if (is_array($addrels)) {
				foreach ($addrels as $addrel) ?>
<link rel="stylesheet" href="<?php echo $addrel; ?>" />
		<?php } else { ?>
<link rel="stylesheet" href="<?php echo $addrels; ?>" />
		<?php } ?>
		
	</head>
	
	<body>
		<div class="fluid-container">
			