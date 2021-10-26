<!DOCTYPE html>
<html lang="<?php echo isset ($locale) ? $locale : 'id'; ?>">
<head>
	<meta charset="utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="description" content="" />
	<meta name="author" content="" />
	<title></title>
<?php 
$au = isset ($assets_url) ? $assets_url : NULL;
$vc = isset ($vendor_components) ? $vendor_components : NULL;
if ($au != NULL && $vc != NULL):
	$vd = $vc['vendors_dir'];
	foreach ($vc['styles'] as $style):
?>
	<link rel="stylesheet" type="text/css" href="<?php echo $au . $vd . $style; ?>" />
<?php 
	endforeach;
endif;
$ac = isset ($assets_components) ? $assets_components : NULL;
if ($au != NULL && $ac != NULL):
	$ad = $ac['assets_dir'];
	foreach ($ac['styles'] as $style):
?>
	<link rel="stylesheet" type="text/css" href="<?php echo $au . $ad . $style; ?>" />
<?php 		
	endforeach;
endif;
?>
</head>

<body>