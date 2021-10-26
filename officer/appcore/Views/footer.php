		
		
			<footer>
			</footer>
		</div>
		<script src="<?php echo isset ($vendorAssetsPath) ? $vendorAssetsPath : ''; ?>/jquery/jquery-3.5.1.min.js"></script>
		<script src="<?php echo isset ($vendorAssetsPath) ? $vendorAssetsPath : ''; ?>/bootstrap/js/bootstrap.min.js"></script>
		<script src="<?php echo isset ($vendorAssetsPath) ? $vendorAssetsPath : ''; ?>/fontawesome/js/all.min.js"></script>
		<?php 
		if (isset ($addscripts)) 
			if (is_array($addscripts)) {
				foreach ($addscripts as $script) ?>
<script src="<?php echo $script; ?>"></script>
		<?php } else { ?>
<script src="<?php echo $addscripts; ?>"></script>
		<?php } ?>	