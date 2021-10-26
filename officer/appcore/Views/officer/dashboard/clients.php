<?php include "header.php" ?>
					<div class="row gutters-sm">
						<div id="form-client" class="col-xl-4 col-lg-4 mb-3" style="display: none;">
							<div class="card">	
								<div class="card-body">
									<form id="form-clients" role="form" method="post" action="client-processing">
										<section id="id-section">
											<div class="form-group">
												<label for="client-name"><?php echo isset ($text) ? $text['clientname'] : 'Client Name'; ?> :</label>
												<div class="input-group">
													<input type="text" name="client-name" class="form-control" required />
												</div>
											</div>
											<div class="form-group">
												<label for="client-passcode"><?php echo isset ($text) ? $text['clientpasscode'] : 'Client Passcode'; ?></label>
												<div class="input-group">
													<input type="password" name="client-passcode" class="form-control" required />
												</div>
											</div>
											<div class="form-group">
												<label for="client-key"><?php echo isset ($text) ? $text['clientkey'] : 'Client Key'; ?> :</label>
												<div class="input-group">
													<input type="text" name="client-key" class="form-control" readonly="readonly" required />
													<div class="input-group-append">
														<button type="button" name="generate-key" class="btn btn-outline-secondary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['generatekey'] : 'Generate Key'; ?>">
															<i class="fas fa-circle-notch fa-fw"></i>
														</button>
													</div>
												</div>
											</div>
											<div class="form-group">
												<label for="client-api"><?php echo isset ($text) ? $text['clientapi'] : 'API Select'; ?> :</label>
												<div class="input-group">
													<select name="client-api" class="form-control">
														<option value="justselected" disabled="disabled" selected="selected"><?php echo isset ($text) ? $text['select'] : '--- Select ---'; ?></option>
<?php 
if (isset ($listApi)) 
	foreach ($listApi as $api):
?>
														<option value="<?php echo $api->apicode; ?>"><?php echo $api->apiname; ?></option>
<?php 
	endforeach;
?>
													</select>
												</div>
											</div>
										</section>
										<section id="detail-section" style="display: none;">
											<ul class="nav nav-tabs nav-justified" role="tabbed-form" id="client-formtab">
												<li class="nav-item">
													<a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="true"><?php echo isset ($text) ? $text['text-tabprofile'] : 'Client Profile'; ?></a>
												</li>
												<li class="nav-item">
													<a class="nav-link" id="dbconfig-tab" data-toggle="tab" href="#dbconfig" role="tab" aria-controls="dbconfig" aria-selected="false"><?php echo isset ($text) ? $text ['text-tabdatabase'] : 'Database Configuration'; ?></a>
												</li>
											</ul>
											<div class="tab-content" id="client-formtab-content">
												<div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
													<div class="form-group">
														<label for="client-codename"><?php echo isset ($text) ? $text['text-clientcode'] : 'Client Code'; ?> :</label>
														<div class="input-group">
															<input type="text" name="client-codename" class="form-control" readonly="readonly" required />
															<div class="input-group-append">
																<button type="button" name="generate-clientcode" class="btn btn-outline-secondary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['text-generatecc'] : 'Generate Client Code'; ?>">
																	<i class="fas fa-circle-notch fa-fw"></i>
																</button>
															</div>
														</div>
													</div>
													<div class="form-group">
														<label for="client-fullname"><?php echo isset ($text) ? $text['text-fullname'] : 'Corporate Name';?> :</label>
														<div class="input-group">
															<input type="text" name="client-fullname" class="form-control" required />
														</div>
													</div>
													<div class="form-group">
														<label for="client-address"><?php echo isset ($text) ? $text['text-address'] : 'Address'; ?> :</label>
														<div class="input-group">
															<textarea name="client-address" class="form-control" rows="2" required></textarea>
														</div>
													</div>
													<div class="form-group">
														<label for="client-npwp"><?php echo isset ($text) ? $text['text-npwp'] : 'NPWP'; ?> :</label>
														<div class="input-group">
															<input type="text" name="client-npwp" class="form-control" required />
														</div>
													</div>
													<div class="form-group">
														<label for="client-picname"><?php echo isset ($text) ? $text['text-picname'] : 'PIC Name'; ?> :</label>
														<div class="input-group">
															<input type="text" name="client-picname" class="form-control" required />
														</div>
													</div>
													<div class="form-group">
														<label for="client-picemail"><?php echo isset ($text) ? $text['text-picemail'] : 'PIC Email'; ?> :</label>
														<div class="input-group">
															<input type="email" name="client-picemail" class="form-control" required />
														</div>
													</div>
													<div class="form-group">
														<label for="client-picphone"><?php echo isset ($text) ? $text['text-picphone'] : 'PIC Phone'; ?> :</label>
														<div class="input-group">
															<input type="tel" name="client-picphone" class="form-control" required />
														</div>
													</div>
												</div>
												<div class="tab-pane fade" id="dbconfig" role="tabpanel" aria-labelledby="dbconfig-tab">
													<div class="form-group">
														<label for="dbname"><?php echo isset ($text) ? $text['text-dbname'] : 'Database Name'; ?> :</label>
														<div class="input-group">
															<input type="text" name="dbname" class="form-control" readonly="readonly" required />
															<div class="input-group-append">
																<button type="button" name="generate-dbname" class="btn btn-outline-secondary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['text-generatedbname'] : 'Generate Client DBName'; ?>">
																	<i class="fas fa-circle-notch fa-fw"></i>
																</button>
															</div>
														</div>
													</div>
													<div class="form-group">
														<label for="dbuser"><?php echo isset ($text) ? $text['text-dbuser'] : 'Database User'; ?> :</label>
														<div class="input-group">
															<input type="text" name="dbuser" class="form-control" readonly="readonly" required />
														</div>
													</div>
													<div class="form-group">
														<label for="dbpswd"><?php echo isset ($text) ? $text['text-dbpswd'] : 'Database Password'; ?> :</label>
														<div class="input-group">
															<input type="password" name="dbpswd" class="form-control" required />
															<div class="input-group-append">
																<button type="button" name="generate-password" class="btn btn-outline-secondary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['text-generatepswd'] : 'Generate Database Password'; ?>">
																	<i class="fas fa-circle-notch fa-fw"></i>
																</button>
															</div>
														</div>
													</div>
													<div class="form-group">
														<label for="dbcnfm"><?php echo isset ($text) ? $text['text-dbcnfm'] : 'Database Confirm Password'; ?> :</label>
														<div class="input-group">
															<input type="password" name="dbcnfm" class="form-control" required />
														</div>
													</div>
													<div class="form-group">
														<label for="dbprefix"><?php echo isset ($text) ? $text['text-dbprefix'] : 'Table Prefix'; ?></label>
														<div class="input-group">
															<input type="text" name="dbprefix" class="form-control" maxlength="5" required />
															<div class="input-group-append">
																<button type="button" name="generate-dbprefix" class="btn btn-outline-secondary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['text-generatedbprefix'] : 'Generate Table Prefix'; ?>">
																	<i class="fas fa-circle-notch fa-fw"></i>
																</button>
															</div>
														</div>
													</div>
												</div>
											</div>
										</section>
										<hr />
										<section class="text-right">
											<button type="submit" class="btn btn-outline-success" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['text-btnsubmit'] : 'Submit'; ?>">
												<i class="fas fa-undo fa-fw"></i> <span class="d-none d-md-inline"><?php echo isset ($text) ? $text['text-btnsubmit'] : 'Submit'; ?></span>
											</button>
											<button type="reset" class="btn btn-outline-secondary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['text-btnreset'] : 'Reset'; ?>">
												<i class="fas fa-undo fa-fw"></i> <span class="d-none d-md-inline"><?php echo isset ($text) ? $text['text-btnreset'] : 'Reset'; ?></span>
											</button>
											<button type="button" name="cancel" class="btn btn-outline-primary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['text-btncancel'] : 'Cancel'; ?>">
												<i class="fas fa-ban fa-fw"></i> <span class="d-none d-md-inline"><?php echo isset ($text) ? $text['text-btncancel'] : 'Cancel'; ?></span>
											</button>
										</section>
									</form>
								</div>
							</div>
						</div>
						<div id="master-data-client" class="col-xl-12 col-lg-12 mb-3">
							<div class="card">
								<div class="card-body">
									<div class="d-flex justify-content-between">
										<h3><?php echo isset ($text) ? $text['text-mclient'] : 'Master Clients'; ?></h3>
										<span>
											<button type="button" name="register-client" class="btn btn-outline-secondary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['text-regclient'] : 'Register Client'; ?>">
												<i class="fas fa-plus-circle fa-fw"></i> <span class="d-none d-md-inline"><?php echo isset ($text) ? $text['text-regclient'] : 'Register Client'; ?></span>
											</button>
										</span>
									</div>
									<hr style="margin: 1rem 0;" />
									<table id="masterDataClients" class="table table-striped table-bordered" style="cursor: pointer;">
										<thead>
											<tr>
<?php 
if (isset ($theaders))
	foreach ($theaders as $thead):
?>												
												<th><?php echo $thead; ?></th>
<?php 
	endforeach;
?>
											</tr>
										</thead>
										<tbody>
<?php 
if (isset ($listClients))
	foreach ($listClients as $row):
?>
											<tr id="<?php echo $row['cac']?>">
<?php 
		foreach ($row as $key => $value):
			if ($key !== 'cac'):
?>
												<td><?php echo $value; ?></td>
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
<?php include "footer.php" ?>
	<script type="text/javascript">
		$(document).ready (function (e) {
			var $form = $('form#form-clients'),
				$clientName = $('input[name="client-name"]');
			$clientName.change (function (e) {
				$(this).removeClass ('is-valid is-invalid');
			});

			$(function () {
				$('table#masterDataClients').DataTable ({
					'ordering'		: true,
					'pageLength'	: 25
				});
			});

			$.fn.generateData = function ($sendData) {
				var $this = $(this);
				$.ajax ({
					'method': 'post',
					'data': $sendData,
					'dataType': 'json'
				}).done (function (result) {
					if (result.status != 200) ;
					else {
						var $runName = $this.prop ('name');
						switch ($runName) {
							default:
								break;
							case 'generate-key':
		 						var $clientKey = $('input[name="client-key"]');
		 						$clientKey.val ('');
		 						if (result.status == 200) $clientKey.val (result.generated);
		 						else {
		 							$clientKey.addClass ('is-invalid');
		 							$clientKey.prop ('placeholder', 'Error!');
		 						}
		 						break;
							case 'generate-clientcode':
								var $clientCode = $('input[name="client-codename"]');
								$clientCode.val ('');
								if (result.status == 200) $clientCode.val (result.generated);
								else {
									$clientCode.addClass ('is-invalid');
									$clientCode.prop ('placeholder', 'Error!');
								}
								break;
							case 'generate-dbname':
								var $clientDbname = $('input[name="dbname"]'),
									$clientDbuser = $('input[name="dbuser"]');
								$clientDbname.val ('');
								$clientDbuser.val ('');
								if (result.status == 200) {
									$clientDbname.val (result.generated);
									$clientDbuser.val (result.generated);
								} else {
									$clientDbname.addClass ('is-invalid');
									$clientDbname.prop ('placeholder', 'Error!');
								}
								break;
							case 'generate-password':
								var $clientDbpswd = $('input[name="dbpswd"]'),
									$clientDbcnfm = $('input[name="dbcnfm"]');
								$clientDbpswd.val ('');
								$clientDbcnfm.val ('');
								if (result.status == 200) {
									$clientDbpswd.focus ();
									$clientDbpswd.attr ('type', 'text');
									$clientDbpswd.focusout (function (e) {
										$(this).attr ('type', 'password');
									});
									$clientDbpswd.val (result.generated);
									$clientDbcnfm.val (result.generated);
								} else {
									$clientDbpswd.addClass ('is-invalid');
									$clientDbpswd.prop ('placeholder', 'Error!');
								}
								break;
							case 'generate-dbprefix':
								var $clientDbPrefix = $('input[name="dbprefix"]');
								if (result.status == 200) {
									$clientDbPrefix.focus ();
									$clientDbPrefix.val (result.generated);
								} else {
									$clientDbPrefix.addClass ('is-invalid');
									$clientDbPrefix.prop ('placeholder', 'Error!');
								}
								break;
						}
					}
					console.log ($this.prop ('name'));
					$this.children ().removeClass ('fa-spin');
				}).fail (function () {
				});
			};
			
			$('button[name="register-client"]').click (function (e) {
				$('div#master-data-client').removeClass ('col-lg-12 col-xl-12').addClass ('col-lg-8 col-xl-8');
				$('div#form-client').show ();
			});
			$('button:submit').click (function (e) {
				e.preventDefault ();
				$form.addClass ('was-validated');		
				var isValid = $form.checkFormValidity ();		
				if (isValid) {
					var $dbpswd = $('input[name="dbpswd"]'),
						$dbcnfm = $('input[name="dbcnfm"]'),
						validpswd = false;
					if ($dbpswd.val () === $dbcnfm.val ()) validpswd = true;
					if (validpswd) $form.submit ();
					else {
						$dbpswd.val ('');
						$dbcnfm.val ('');
						$dbpswd.focus ();
					}
				}
			});
			$('button:reset').click (function (e) {
				$('section#detail-section').hide ();
				$form.removeClass ('was-validated');
			});
			$('button[name="cancel"]').click (function (e) {
				$('button:reset').click ();
				$('div#master-data-client').removeClass ('col-lg-8 col-xl-8').addClass ('col-lg-12 col-xl-12');
				$('div#form-client').hide ();
			});
			$('button[name="generate-key"]').click (function (e) {
				$clientName.removeClass ('is-valid is-invalid');
				if ($clientName.val ().length == 0) {
					$clientName.addClass ('is-invalid');
					$clientName.focus ();
				} else {
					var $this = $(this),
						$sendData = {
							'trigger': $(this).prop ('name')
						},
						$buttonIcon = $this.children ();
					$buttonIcon.addClass ('fa-spin');
					setTimeout (function () {
						$this.generateData ($sendData);
					}, 2000);
				}
			});
			$('button[name="generate-clientcode"]').click (function (e) {
				var $clientApi = $('select[name="client-api"]');
				$clientName.removeClass ('is-valid is-invalid');
				if ($clientName.val ().length == 0) {
				} else {
					var $this = $(this),
						$sendData = {
							'trigger': $(this).prop ('name'),
							'client-name': $clientName.val (),
							'client-api': $('select[name="client-api"]').val ()
						},
						$buttonIcon = $this.children ();
					$buttonIcon.addClass ('fa-spin');
					setTimeout (function () {
						$this.generateData ($sendData);
					}, 2000);
				}
			});
			$('button[name="generate-dbname"]').click (function (e) {
				$clientName.removeClass ('is-valid is-invalid');
				if ($clientName.val ().length == 0) {
					$clientName.addClass ('is-invalid');
					$clientName.focus ();
				} else {
					var $this = $(this),
						$sendData = {
							'trigger': $(this).prop ('name'),
							'client-name': $clientName.val (),
							'client-api': $('select[name="client-api"]').val ()
						},
						$buttonIcon = $this.children ();
					$buttonIcon.addClass ('fa-spin');
					setTimeout (function () {
						$this.generateData ($sendData);
					}, 2000);
				}
			});
			$('button[name="generate-password"]').click (function (e) {
				var $this = $(this),
					$sendData = {
						'trigger': $this.prop ('name')
					},
					$buttonIcon = $this.children ();
				$buttonIcon.addClass ('fa-spin');
				setTimeout (function () {
					$this.generateData ($sendData);	
				}, 1000);
			});
			$('button[name="generate-dbprefix"').click (function (e) {
				var $this = $(this),
					$sendData = {
						'trigger': $this.prop ('name'),
						'client-api': $('select[name="client-api"]').val ()
					},
					$buttonIcon = $this.children ();
				$buttonIcon.addClass ('fa-spin');
				setTimeout (function () {
					$this.generateData ($sendData);
				}, 1000);
			});
			$('select[name="client-api"]').change (function (e) {
				if ($(this).val () !== $(this).children (':first').val ()) 
					$('section#detail-section').slideDown ();
			});
		});
	</script>
<?php include "html-footer.php" ?>