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
							<form method="post" role="role" id="form-administrator" action="<?php echo isset ($formAction) ? $formAction : ''; ?>">
								<input type="hidden" name="client-src" value="<?php echo isset ($clientSource) ? $clientSource : ''; ?>" />
								<div class="form-group">
									<label for="admin-username"><?php echo isset ($text) ? $text['tusername'] : ''; ?></label>
									<input type="text" class="form-control" name="admin-username" data-description="<?php echo isset ($text) ? $text['tuserdesc'] : ''; ?>" required />
								</div>
								<div class="form-group">
									<label for="admin-email"><?php echo isset ($text) ? $text['temail'] : ''; ?></label>
									<input type="email" class="form-control" name="admin-email" data-description="<?php echo isset ($text) ? $text['tuserdesc'] : ''; ?>" required />
								</div>
								<div class="form-group">
									<label for="admin-phone"><?php echo isset ($text) ? $text['tphone'] : ''; ?></label>
									<input type="tel" class="form-control" name="admin-phone" pattern="[0]{1}[0-9]*" data-description="<?php echo isset ($text) ? $text['tuserdesc'] : ''; ?>" required />
								</div>
								<div class="form-group">
									<label for="admin-password"><?php echo isset ($text) ? $text['tpassword'] : ''; ?></label>
									<input type="password" class="form-control" name="admin-password" data-description="<?php echo isset ($text) ? $text['tuserdesc'] : ''; ?>" required />
								</div>
								<div class="form-group">
									<label for="admin-pconfirm"><?php echo isset ($text) ? $text['tpconfirm'] : ''; ?></label>
									<input type="password" class="form-control" name="admin-pconfirm" data-description="<?php echo isset ($text) ? $text['tuserdesc'] : ''; ?>" required />
								</div>
								<hr />
								<div class="row">
									<div class="col-6">
										<button type="button" id="dummy-submit" class="btn btn-primary btn-block"><?php echo isset ($text) ? $text['tnext'] : ''; ?></button>
										<button type="submit" class="btn btn-primary btn-block d-none"></button>
									</div>
									
									<div class="col-6">
										<button type="reset" class="btn btn-outline-secondary btn-block"><?php echo isset ($text) ? $text['treset'] : ''; ?></button>
									</div>
								</div>
							</form>
						</div>
						
						<div class="col-xl-4 col-lg-4 col-md-4" id="input-description">
						</div>
					</div>
				</div>
			</div>

<?php include __DIR__ . '/../footer.php'; ?>

		<script>

		$.onEnterKeyDown = function () {
			$('button#dummy-submit').click ();
		};
		
		$('input').hover (function (evt) {
			$('div#input-description').empty ();
			$('<p/>', {
				"class": "input-description",
				"text": $(this).attr ('data-description')
			}).appendTo ($('div#input-description'));
		});
		
		$('button#dummy-submit').click (function (evt) {
			var form = $('form#form-administrator'),
				submitBtn = form.find ('button:submit'),
				validityCheck = form[0].checkValidity ();

			if (!validityCheck) submitBtn.click ();
			else {
				var message, title, inputValid = false;
				form.find ('input').each (function () {
					var name = $(this).prop ('name');
					switch (name) {
						default:
							$message	= '<?php echo isset ($text) ? $text['message1'] : ''; ?>';
							$title		= '<?php echo isset ($text) ? $text['title1'] : ''; ?>';
							break;
						case 'admin-password':
						case 'admin-pconfirm':
							var ipassword = $('input[name="admin-password"]');
							var ipconfirm = $('input[name="admin-pconfirm"]');
							if (ipassword.val () == ipconfirm.val ()) {
								inputValid = true;
							} else {
								inputValid = false;
								message = '<?php echo isset ($text) ? $text['message2'] : ''; ?>';
								title	= '<?php echo isset ($text) ? $text['title2'] : ''; ?>'; 
							}
							break;
					}
				});

				if (inputValid) submitBtn.click ();
				else {
					$.showMessageDialog (title, message, 'form-alert', modalSize.medium, messageType.ok);
					$('button:reset').click ();
				}
			}
		});
		</script>

<?php include __DIR__ . '/../htmlclose.php'; ?>