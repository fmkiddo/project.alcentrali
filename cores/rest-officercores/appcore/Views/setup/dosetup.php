<?php include 'setup-page-header.php'; ?>

			<div class="page-body">
				<div class="container">
					<div class="row">
						<div class="col-12">
							<h4><?php echo isset ($text) ? $text['body-title'] : ''; ?></h4>
							<p><?php echo isset ($text) ? $text['body-description'] : '';?></p>
						</div>
					</div>
					
					<div class="row">
						<div class="col-12">
							<div class="row">
								<div class="col">
									<p><?php echo isset ($text) ? $text['message'] : ''; ?></p>
								</div>
							</div>
							<div class="row">
								<div class="col">
									<form method="<?php echo ($setupStatus == 200) ? 'get' : 'post'; ?>" role="form" id="target-redirect" action="<?php echo $formAction; ?>">
										<button type="submit" class="btn btn-primary btn-block"><?php echo isset ($text) ? $text['proceed-btn-text'] : ''; ?></button>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

<?php include __DIR__ . '/../footer.php'; ?>


<?php include __DIR__ . '/../htmlclose.php'; ?>
