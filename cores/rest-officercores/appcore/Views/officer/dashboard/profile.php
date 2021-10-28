<?php include "header.php"; ?>

					<div class="row gutters-sm">
						<div class="col-md-4 mb-3">
							<div class="card">
								<div class="card-body">
									<div class="d-flex flex-column align-items-center text-center">
										<img src="https://bootdey.com/img/Content/avatar/avatar7.png" alt="Admin" class="rounded-circle" width="150">
                    					<div class="mt-3">
                    						<h4><?php echo isset ($profiles) ? $profiles['fullname'] : '---Your Full Name---'; ?></h4>
                    						<p class="text-secondary mb-1"><?php echo isset ($officername) ? $officername : 'your_username'; ?></p>
                    						<p class="text-secondary mb-1">FMKiddo System Administrator</p>
                    						<p class="text-muted font-size-sm"><?php echo isset ($profiles) ? $profiles['address2'] : '---Your Address---'; ?></p>
                    						<hr />
                    						<div class="text-center">
                    							<button type="button" id="changepassword" class="btn btn-primary">
                    								<i class="fas fa-lock fa-fw"></i> Change Password
                    							</button>
                    						</div>
                    					</div>
									</div>
								</div>
							</div>
							
							<div id="form-passwordchange" class="card mt-3 animated">
								<div class="card-body">
									<form id="form-passwordchange" role="form">
										<p>Change Your Password</p>
										<hr />
										<div class="row form-group">
											<div class="col-sm-4">
												<label for="old-password"><small><?php echo isset ($text) ? $text['oldpswd'] : 'Old Password'; ?> :</small></label>
											</div>
											<div class="col-sm-8">
												<div class="input-group">
													<input type="password" class="form-control" name="old-password" required />
												</div>
											</div>
										</div>
										
										<div class="row form-group">
											<div class="col-sm-4">
												<label for="new-password"><small><?php echo isset ($text) ? $text['newpswd'] : 'New Password'; ?> :</small></label>
											</div>
											<div class="col-sm-8">
												<div class="input-group">
													<input type="password" class="form-control" name="new-password" required />
												</div>
											</div>
										</div>
										
										<div class="row form-group">
											<div class="col-sm-4">
												<label for="cnf-password"><small><?php echo isset ($text) ? $text['retype'] : 'Re-Type Password'; ?> :</small></label>
											</div>
											<div class="col-sm-8">
												<div class="input-group">
													<input type="password" class="form-control" name="cnf-password" required />
												</div>
											</div>
										</div>
										<div style="display: none;">
											<p class="text-danger" id="pswd-msgs"></p>
										</div>
										<hr />
										<div class="text-right">
											<button type="submit" class="btn btn-primary">
												<i class="fas fa-save fa-fw"></i> <?php echo isset ($text) ? $text['pswdchange'] : 'Change Password'; ?>

											</button>
											<button id="cancel-changepassword" type="reset" class="btn btn-outline-primary">
												<i class="fas fa-redo fa-fw"></i> <?php echo isset ($text) ? $text['cancel'] : 'Cancel'; ?>

											</button>
										</div>
									</form>
								</div>
							</div>
						</div>
						<div class="col-md-8">
							<div id="profile" class="card mb-3">
								<div class="card-body">
									<div class="d-flex justify-content-between">
<?php if (isset ($keys) && isset ($profiles)) { ?>
										<h3 class="h3"><?php echo $profiles['fullname']; ?></h3>
										<span>
											<button class="btn btn-primary" type="button" id="edit-profile">
												<i class="fas fa-edit fa-fw"></i> <?php echo isset ($text) ? $text['editprofile'] : 'Edit Profile'; ?>

											</button>
										</span>
									</div>
									<hr />
<?php 
	foreach ($keys as $key => $profiletext) {
?>
									<div class="row">
										<div class="col-sm-3">
											<h6 class="mb-0"><?php echo $profiletext; ?></h6>
										</div>
										<div class="col-sm-9 text-secondary">
											<?php echo $profiles[$key]; ?>

										</div>
									</div>
<?php 
		if ($key != 'address2') {?>
									<hr />
<?php 	}
	}
}
?>
								</div>
							</div>
							
							<div id="form-profile" class="card mb-3">
								<div class="card-body">
									<form id="form-profile" role="form">
										<div class="d-flex justify-content-between">
											<h3 class="h3"><?php echo isset ($text) ? $text['formprofile'] : 'Edit Your Profile'; ?></h3>
											<span>
												<button type="button" id="cancel-edit" class="btn btn-outline-primary">
													<i class="fas fa-times fa-fw"></i> <?php echo isset ($text) ? $text['cancel'] : 'Cancel'; ?>
													
												</button>
											</span>
										</div>
										<hr />
<?php if (isset ($keys) && isset ($profiles)) {
	foreach ($keys as $key => $profiletext) { ?>
										<div class="form-group">
											<label for="<?php echo $key; ?>"><?php echo $profiletext?> :</label>
											<div class="input-group">
<?php 	if ($key == 'address1' || $key == 'address2') { ?>
												<textarea rows="2" name="<?php echo $key; ?>" class="form-control"><?php echo $profiles[$key]; ?></textarea>
<?php 	} else { ?>
												<input type="<?php echo ($key == 'phone' ? 'tel' : ($key == 'email' ? 'email' : 'text')); ?>" name="<?php echo $key; ?>" class="form-control" value="<?php echo $profiles[$key]; ?>" />
<?php 	} ?>
											</div>
										</div>
<?php 	} 
} ?>
										<div>
											<span id="profile-message"></span>
										</div>
										<hr />
										<div class="text-right">
											<button class="btn btn-primary" type="submit">
												<i class="fas fa-save fa-fw"></i> <?php echo isset ($text) ? $text['save'] : 'Save'; ?>

											</button>
											<button class="btn btn-outline-secondary" type="reset">
												<i class="fas fa-redo fa-fw"></i> <?php echo isset ($text) ? $text['reset'] : 'Reset'; ?>

											</button>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
<?php include "footer.php"; ?>

		<script type="text/javascript">
		$('div#form-profile').hide ();
		$('div#form-passwordchange').hide ();
		$('button#edit-profile').click (function (e) {
			$('div#profile').fadeOut (200);
			setTimeout (function () {
				$('div#form-profile').fadeIn (200);
			}, 1000);
		});
		$('button#cancel-edit').click (function (e) {
			$('div#form-profile').fadeOut(200);
			setTimeout (function () {
				$('div#profile').fadeIn (200);
			}, 1000);
		});
		$('button#changepassword').click (function (e) {
			$('div#form-passwordchange').slideDown ();
		});
		$('button#cancel-changepassword').click (function (e) {
			$('div#form-passwordchange').slideUp ();
		});
		var formcpwd	= $('form#form-passwordchange'),
			cpwdbtn 	= formcpwd.find ('button:submit');
		cpwdbtn.click (function (e) {
			e.preventDefault ();
			formcpwd.disableForm ();
			$.ajax ({
				'url': '<?php echo isset ($formpswdaction) ? $formpswdaction : ''; ?>',
				'method': 'put',
				'data': JSON.stringify (formcpwd.serializeArray ()),
				'contentType': 'application/json',
				'dataType': 'json'
			}).done (function (result) {
				var msgs = $('p#pswd-msgs');
				msgs.empty ();
				if (result.status == 200) {
					msgs.text (result.message);
					msgs.removeClass ('text-danger');
					msgs.addClass ('text-success');
					formcpwd.addClass ('was-validated');
					setTimeout (function () {
						formcpwd.find (':input:password').each (function () {
							$(this).val ('');
						});

						msgs.text ('');
						msgs.removeClass ('text-success')
						msgs.addClass ('text-danger');
						msgs.parent ('div').hide ();
						formcpwd.removeClass ('was-validated');
						formcpwd.enableForm ();
						$('button#cancel-changepassword').click ();
					}, 3000);
				} else {
					msgs.text (result.message);
					msgs.parent ('div').show ();
					formcpwd.removeClass ('was-validated');
					switch (result.status) {
						default:
							break;
						case 400:
							formcpwd.find ('[name="old-password"]').val ('');
							break;
						case 404:
							break;
						case 403:
						case 412:
							formcpwd.find (':input').each (function () {
								$(this).val ('');
							});
							break;
					}
					formcpwd.addClass ('was-validated');
					formcpwd.enableForm ();
				}
			}).fail (function () {
			});
		});

		var formprofile	= $('form#form-profile'),
			sprfbtn		= formprofile.find ('button:submit');
		sprfbtn.click (function (e) {
			e.preventDefault ();

			$.ajax ({
				'url': '<?php echo isset ($formpupdtaction) ? $formpupdtaction : ''; ?>',
				'method': 'put',
				'data': JSON.stringify (formprofile.serializeArray ()),
				'dataType': 'json',
				'contentType': 'application/json'
			}).done (function (result) {
				if (result.status == 200)
					window.location.reload ();
				else 
					$('span#profile-message').empty ().text (result.message);
			}).fail (function () {
			});
		});
		</script>

<?php include "html-footer.php"; ?> 