<?php include "header.php"; ?>

					<div class="row gutters-sm">
						<div id="form-officer" class="col-xl-4 col-lg-4 mb-3" style="display: none;">
							<div class="card">
								<div class="card-body">
									<form id="form-officer" role="form" method="post">
										<div class="form-group">
											<label for="username"><?php echo isset ($text) ? $text['text-username'] : 'Username'; ?> :</label>
											<div class="input-group">
												<input type="text" name="username" class="form-control" required />
											</div>
										</div>
										<div class="form-group">
											<label for="email"><?php echo isset ($text) ? $text['text-email'] : 'Email'; ?> :</label>
											<div class="input-group">
												<input type="email" name="email" class="form-control" required />
											</div>
										</div>
										<div class="form-group">
											<label for="phone"><?php echo isset ($text) ? $text['text-phone'] : 'Phone'; ?> :</label>
											<div class="input-group">
												<input type="tel" name="phone" class="form-control" required />
											</div>
										</div>
										<div class="form-group">
											<label for="password"><?php echo isset ($text) ? $text['text-password'] : 'Password'; ?> :</label>
											<div class="input-group">
												<input type="password" name="password" class="form-control" required />
											</div>
										</div>
										<div class="form-group">
											<label for="confirm"><?php echo isset ($text) ? $text['text-confirm'] : 'Confirm Password'; ?> :</label>
											<div class="input-group">
												<input type="password" name="confirm" class="form-control" required />
											</div>
										</div>
										<span id="form-message"></span>
										<hr />
										<div class="text-right">
											<button type="submit" class="btn btn-outline-success" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['submit'] : 'Submit'; ?>">
												<i class="fas fa-check fa-fw"></i> <span class="d-none d-md-inline"><?php echo isset ($text) ? $text['submit'] : 'Submit'; ?></span>
											</button>
											<button type="reset" name="reset" class="btn btn-outline-secondary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['reset'] : 'Reset'; ?>">
												<i class="fas fa-undo fa-fw"></i> <span class="d-none d-md-inline"><?php echo isset ($text) ? $text['reset'] : 'Reset'; ?></span>
											</button>
											<button type="reset" name="cancel" class="btn btn-outline-primary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['cancel'] : 'Cancel'; ?>">
												<i class="fas fa-ban fa-fw"></i> <span class="d-none d-md-inline"><?php echo isset ($text) ? $text['cancel'] : 'Cancel'; ?></span>
											</button>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div id="master-officer" class="col-xl-12 col-lg-12 mb-3">
							<div class="card">
								<div class="card-body">
									<div class="d-flex justify-content-between">
										<h4><?php echo isset ($text) ? $text['title-master'] : 'Master Officers'; ?></h4>
										<button type="button" name="add-officer" class="btn btn-primary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['add-officer'] : 'Add Officer'; ?>">
											<i class="fas fa-plus fa-fw"></i> <p class="d-none d-md-inline"><?php echo isset ($text) ? $text['add-officer'] : 'Add Officer'; ?></p>
										</button>
									</div>
									<hr />
									<div class="mt-4">
										<table id="masterDataOfficer" class="table table-striped table-bordered">
											<thead>
												<tr>
<?php 
foreach ($thead as $thid => $th):
?>
													<th class="text-center"><?php echo $th; ?></th>
<?php 
endforeach;
?>
												</tr>
											</thead>
											<tbody>
<?php 
foreach ($tbody as $row):
?>
												<tr style="cursor: pointer;">
<?php 
	foreach ($row as $tdid => $td):
		if ($tdid == 'id'):
?>
													<td class="text-center"><input type="radio" name="id" value="<?php echo $td; ?>" /></td>
<?php	else: ?>
													<td><?php echo $td; ?></td>
<?php 
		endif; 
	endforeach;
?>
												</tr>
<?php 
endforeach;
?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
<?php include "footer.php"; ?>
	<script type="text/javascript">
	$(document).ready (function () {
		var enableLoading = false;
		$(function () {
			$('table#masterDataOfficer').DataTable ({
				'ordering'		: true,
				'pageLength'	: 25
			});
		});
		
		$('tr').click (function () {
			var inputId = $(this).find ('input[name="id"]');
			inputId.prop ('checked', true);
			if (enableLoading) {
				var tds = $(this).children ();
				$form = $('form#form-officer');
				$form.find ('input:password').prop ('required', false);
				$form.clearForm ();
				$.each (tds, function (key, data) {
					var inputName = (key == 1 ? 'username' : (key == 2 ? 'email' : (key == 3 ? 'phone' : '')));
					if (inputName.length > 0) {
						var selectedInput = $('input[name="' + inputName + '"]');
						if (inputName === 'username') selectedInput.prop ('readonly', true);
						selectedInput.val ('');
						selectedInput.val (data.innerHTML);
					}
				});
			}
		});

		$('button:submit').click (function (e) {
			e.preventDefault ();
			var $form = $('form#form-officer'),
				$inputUsername = $('input[name="username"]'),
				$inputPswd = $('input[name="password"]'),
				$inputCnfm = $('input[name="confirm"]'),
				readyToSubmit = false;
			$form.addClass ('was-validated');
			readyToSubmit = false;
			if ($inputUsername.prop ('readonly')) readyToSubmit = true;
			else {
				readyToSubmit = $form.validateForm ();
				if (readyToSubmit) readyToSubmit = $inputPswd.val () === $inputCnfm.val ();
				if (!readyToSubmit) {
					console.log ('password does not match');
					$inputCnfm.val ('');
					$formMessage = $('#form-message');
					$formMessage.text ('Password does not match!');
					$formMessage.addClass ('text-danger');
				}
			}

			if (readyToSubmit) $form.submit ();
		});

		$('button[name="add-officer"]').click (function (e) {
			enableLoading = true;
			$('div#master-officer').removeClass ('col-xl-12 col-lg-12').addClass ('col-xl-8 col-lg-8');
			$('div#form-officer').show ();
		});

		$('button[name="reset"]').click (function (e) {
			if ($('input[name="username"]').prop ('readonly')) $('input[name="username"]').prop ('readonly', false);
			$('input[name="username"]').prop ('readonly', false);
			$('form#form-officer').removeClass ('was-validated');
			$('form#form-officer').find ('input').prop ('required', true);
			$formMessage = $('#form-message');
			$formMessage.text ('');
			$formMessage.removeClass ('text-danger');
		});

		$('button[name="cancel"]').click (function (e) {
			enableLoading = false;
			$('button[name="reset"]').click ();
			$('div#master-officer').removeClass ('col-xl-8 col-lg-8').addClass ('col-xl-12 col-lg-12');
			$('div#form-officer').hide ();
			var checkedRadio = $('table#masterDataOfficer').find ('input[type="radio"]:checked');
			if (checkedRadio.length == 1) checkedRadio.prop ('checked', false);
		});
	});
	</script>

<?php include "html-footer.php"; ?>
