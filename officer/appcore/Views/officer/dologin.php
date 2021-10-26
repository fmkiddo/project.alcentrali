<?php include __DIR__ . '/../header.php'; ?>

			<div class="container">
				<div class="d-flex justify-content-center h-100">
					<div class="card card-login">
						<div class="card-header">
							<h3>Log In</h3>
						</div>
						<div class="card-body">
							<form role="form" id="login-form" action="<?php echo isset ($formAction) ? $formAction : '#'; ?>">
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fa fa-user fa-fw"></i></span>
										</div>
										<input type="text" name="input-username" class="form-control" placeholder="<?php echo isset ($text) ? $text['username'] : ''; ?>" required />
									</div>
								</div>
								<div class="form-group">
									<div class="input-group">
										<div class="input-group-prepend">
											<span class="input-group-text"><i class="fa fa-key fa-fw"></i></span>
										</div>
										<input type="password" name="input-password" class="form-control" placeholder="<?php echo isset ($text) ? $text['password'] : ''; ?>" required />
 									</div>
								</div>
								<div class="form-group" style="display: none;">
									<span id="message"></span>
								</div>
								<div class="form-group">
									<button type="submit" class="btn btn-primary btn-block"><?php echo isset ($text) ? $text['login'] : ''; ?></button>
								</div>
							</form>
						</div>
						<div class="card-footer">
							<div class="d-flex justify-content-center">
								<?php echo isset ($text) ? $text['sign-up-message'] : ''; ?><a href="#">Sign Up</a>
							</div>
						</div>
					</div>
				</div>
			</div>

<?php include __DIR__ . '/../footer.php'; ?>
			<script type="text/javascript">
			$(document).ready (function () {
				var $form = $('form#login-form');
				$('input').keyup (function (e) {
					if (e.which == 13) {
						if ($(this).prop ('name') === 'input-username') $('input[name="input-password"]').focus ();
						if ($(this).prop ('name') === 'input-password') {
							$submitBtn = $('button:submit');
							$submitBtn.focus ();
							$submitBtn.click ();
						}
					}
				});

				$('button:submit').on ('click', function (event) {
					event.preventDefault ();
					var form = $(this).parents ('form#login-form'),
						validForm = form.checkFormValidity (),
						loginMsg = $('span#message');
					loginMsg.empty ();

					form.addClass ('was-validated');
					if (validForm) {
						form.find (':input').prop ('readonly', true);
						$.ajax ({
							'url': form.prop ('action'),
							'method': 'post',
							'data': form.serialize (),
							'dataType': 'json'
						}).done (function (result) {
							var resultStatus = result.status;
							if (result.status != 200) {
								loginMsg.html (result.message);
								loginMsg.addClass ('error');
								loginMsg.parent ().show ();
							} else 
								window.location.href = result.redirect_data;
						}).fail (function () {
						});
					}
					setTimeout (function () {
						form.find (':input').prop ('readonly', false);
					}, 2000);
				});
			});
			</script>

<?php include __DIR__ . '/../htmlclose.php'; ?>