<?php include "header.php"; 
$renderTable = TRUE;
if (isset ($listApi) && array_key_exists('status', $listApi)):
	
else:
	$renderTable = FALSE;
endif;
?>

					<div class="row gutters-sm">
						<div class="col-xl-4 col-lg-8 mb-3">
							<div class="card">
								<div class="card-body">
									<form role="form" id="form-api" method="post">
										<div class="form-group">
											<label for="apicode"><?php echo isset ($text) ? $text['text-apicode'] : 'API Code'; ?> :</label>
											<div class="input-group">
												<input type="text" class="form-control" name="apicode" required />
											</div>
										</div>
										<div class="form-group">
											<label for="apiname"><?php echo isset ($text) ? $text['text-apiname'] : 'API Description'; ?> :</label>
											<div class="input-group">
												<input type="text" class="form-control" name="apiname" required />
											</div>
										</div>
										<hr />
										<div class="text-right">
											<button type="submit" class="btn btn-outline-success" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['text-submit'] : 'Submit'; ?>">
												<i class="fab fa-telegram fa-fw"></i> <span class="d-none d-md-inline"><?php echo isset ($text) ? $text['text-submit'] : 'Submit'; ?></span>
											</button>
											<button type="reset" class="btn btn-outline-secondary" data-toggle="tooltip" title="<?php echo isset ($text) ? $text['text-cancel'] : 'Cancel'; ?>">
												<i class="fas fa-ban fa-fw"></i> <span class="d-none d-md-inline"><?php echo isset ($text) ? $text['text-cancel'] : 'Cancel'; ?></span>
											</button>
										</div>
									</form>
								</div>
							</div>
						</div>
						
						<div class="col-xl-8 col-lg-8 mb-3">
							<div class="card">
								<div class="card-body">
									<h3><?php echo isset ($text) ? $text['text-masterdataapi'] : 'System API List'; ?></h3>
									<div>
										<table id="masterDataAPI" class="table table-hover table-striped" style="cursor: pointer;">
											<thead>
												<tr>
<?php foreach ($theaders as $th): ?>
													<th><?php echo $th; ?></th>
<?php endforeach; ?>
												</tr>
											</thead>
											<tbody>
<?php foreach ($listApi as $api): ?>
												<tr>
													<td><?php echo $api->apicode; ?></td>
													<td><?php echo $api->apiname; ?></td>
												</tr>
<?php endforeach; ?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>

<?php include "footer.php" ?>
	<script type="text/javascript">
	$(document).ready (function () {
		$(function () {
			$('table#masterDataAPI').DataTable ({
				'ordering'		: true,
				'pageLength'	: 25
			});
		});
		var $form = $('form#form-api');
		$('button:submit').click (function (e) {
			e.preventDefault ();
			$form.addClass ('was-validated');
			$formReadyForSubmission = $form.checkFormValidity ();
			if ($formReadyForSubmission)
				$form.submit ();
		});

		$('button:reset').click (function (e) {
			$form.removeClass ('was-validated');
		});
	});
	</script>

<?php include "html-footer.php" ?>