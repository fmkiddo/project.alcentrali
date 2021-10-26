<?php include 'setup-page-header.php'; ?>
			
			<div class="page-body">
				<div class="container">
					<div class="row">
						<div class="col-12">
							<h4><?php echo isset ($text) ? $text['body-title'] : ''; ?></h4>
							<p><?php echo isset ($text) ? $text['body-description'] : ''; ?></p>
						</div>
					</div>
					
					<div class="row">
						<div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 col-xs-12">
							<form role="form" id="form-profiles" method="post" action="<?php echo isset ($formAction) ? $formAction : ''; ?>">
								<input type="hidden" name="client-src" value="<?php echo isset ($clientSource) ? $clientSource : ''; ?>" />
								<input type="hidden" name="encoded-data" value="<?php echo isset ($administration) ? $administration : ''; ?>" />
								<div class="form-group">
									<label for="admin-firstname"><?php echo isset ($text) ? $text['tfirstname'] : ''; ?></label>
									<input type="text" class="form-control" name="admin-firstname" required />
								</div>
								
								<div class="form-group">
									<label for="admin-middlename"><?php echo isset ($text) ? $text['tmidname'] : '';  ?></label>
									<input type="text" class="form-control" name="admin-middlename" />
								</div>
								
								<div class="form-group">
									<label for="admin-lastname"><?php echo isset ($text) ? $text['tlastname'] : '';  ?></label>
									<input type="text" class="form-control" name="admin-lastname" required />
								</div>
								
								<div class="form-group">
									<label for="admin-address1"><?php echo isset ($text) ? $text['taddress1'] : ''; ?> 1</label>
									<textarea rows="2" class="form-control" name="admin-address1" required></textarea>
								</div>
								
								<div class="form-group">
									<label for="admin-address2"><?php echo isset ($text) ? $text['taddress2'] : ''; ?> 2</label>
									<textarea rows="2" class="form-control" name="admin-address2"></textarea>
								</div>
								
								<hr>
								
								<div class="row">
									<div class="col-6">
										<button type="button" class="btn btn-primary btn-block" id="dummy-submit"><?php echo isset ($text) ? $text['tsubmit'] : ''; ?></button>
										<button type="submit" class="d-none" id="btn-submit">Fire</button>
									</div>
									
									<div class="col-6">
										<button type="reset" class="btn btn-outline-secondary btn-block"><?php echo isset ($text) ? $text['treset'] : ''; ?></button>
									</div>
								</div>
							</form>
						</div>
						
						<div class="col-xl-4 col-lg-4 col-md-4">
						</div>
					</div>
				</div>
			</div>
			
<?php include __DIR__ . "/../footer.php"; ?>

			<script>
			$('button#dummy-submit').click (function (event) {
				var form = $('form#form-profiles'),
					submitBtn = form.find (':submit'),
					validityCheck = form[0].checkValidity ();

				submitBtn.click ();
			});
			</script>

<?php include __DIR__ . "/../htmlclose.php"; ?>
