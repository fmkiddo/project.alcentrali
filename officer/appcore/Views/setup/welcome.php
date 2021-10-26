<?php include 'setup-page-header.php'; ?>

			<div class="page-body">
				<div class="container">
					<h4><?php echo isset ($text) ? $text['body-title'] : ''; ?></h4>
					<p><?php echo isset ($text) ? $text['body-description'] : ''; ?></p>
					
					<hr />
					<form method="post" id="start-form" role="form" action="<?php echo isset ($formAction) ? $formAction : ''; ?>">
						<input type="hidden" name="client-src" value="<?php echo isset ($clientSource) ? $clientSource : ''; ?>" />
						<button type="button" id="next-button" class="btn btn-primary btn-block">
							<span></span><?php echo isset ($text) ? $text['button-start'] : ''; ?>
						</button>
					</form>
				</div>
			</div>

<?php include __DIR__ . '/../footer.php'; ?>

		<script>
		$('button#next-button').on ('click', function (evt) {
			$('form#start-form').submit ();
		});
		</script>

<?php include __DIR__ . '/../htmlclose.php';?>