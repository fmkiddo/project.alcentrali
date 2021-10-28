<!DOCTYPE html>
<html lang="<?php echo isset ($locale) ? $locale : ''; ?>">
<head>
	<meta />
	<meta />
	<meta />
	<meta />
	<title><?php echo isset ($pageTitle) ? $pageTitle : ''; ?></title>
</head>
<body>
	<div>
		<?php echo isset ($pageContent) ? $pageContent : ''; ?>
	</div>
</body>
</html>
