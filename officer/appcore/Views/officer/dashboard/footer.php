				</div>
			</div>
		</div>
	</div>
	
	<footer>
	</footer>
<?php 
$au = isset ($assets_url) ? $assets_url : NULL;
$vc = isset ($vendor_components) ? $vendor_components : NULL;
if ($vc != NULL && $au != NULL):
	$vd = $vc['vendors_dir'];
	foreach ($vc['scripts'] as $script):
?>
	<script src="<?php echo $au . $vd . $script; ?>"></script>
<?php 
	endforeach;
endif;

$ac = isset ($assets_components) ? $assets_components : NULL;
if ($au != NULL && $ac != NULL):
	$ad = $ac['assets_dir'];
	foreach ($ac['scripts'] as $script):
?>
	<script src="<?php echo $au . $ad . $script; ?>"></script>
<?php 
	endforeach;
endif;
?>