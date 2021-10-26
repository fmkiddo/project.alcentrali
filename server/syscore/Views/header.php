<?php ?>
<!DOCTYPE html>
<html lang="<?php echo isset ($pageLocale) ? $pageLocale : 'id'; ?>">
	<head>
		<meta charset="<?php echo isset ($pageCharset) ? $pageCharset : 'utf-8'; ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="author" content="" />
		<title><?php echo isset ($htmlTitle) ? $htmlTitle : ''; ?></title>
		<link rel="stylesheet" href="<?php echo $basePath . $assetVendorsPath; ?>/bootstrap/css/bootstrap.min.css" />
		<link rel="stylesheet" href="<?php echo $basePath . $assetVendorsPath; ?>/fontawesome/css/all.min.css" />
		<link rel="stylesheet" href="<?php echo $basePath . $assetsPath; ?>/randomizer.css" />
		<meta name="application-name" content="" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
	</head>
	<body>
		<div class="page-container">
			<header class="page-header">
				<div class="container">
					<h2><?php echo isset ($pageTitle) ? $pageTitle : ''; ?></h2>
					<?php echo isset ($titleDesc) ? $titleDesc : ''; ?>
					
				</div>
			</header>
