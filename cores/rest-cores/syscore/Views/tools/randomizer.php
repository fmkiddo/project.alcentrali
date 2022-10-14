<?php include (__DIR__ . '/../header.php'); ?>

			<div class="container">
				<div id="form-section">
					<form id="form-randomizer" role="form">
						<div class="form-group">
							<label for="key-length">Key Length:</label>
							<input type="number" class="form-control" name="key-length" title="" min="32" value="32" required />
						</div>
						<button type="submit" class="btn btn-primary btn-block"><?php echo isset($sendLengthText) ? $sendLengthText : ''; ?></button>
					</form>
				</div>
				
				<div id="form-result">
					<div class="card card-primary">
						<div class="card-header">
							<h5>Your Generated Key</h5>
						</div>
						
						<div class="card-body">
							<div class="row">
								<div class="col">
									<p>Key Length: <span id="key-length"></span></p>
									<input type="text" name="generated-key" class="form-control" readonly />
									<span id="generate-message"></span>
								</div>
							</div>
							<div class="row" style="margin-top: 20px">
								<div class="col">
									<p>Officer Encryption Key:</p>
									<input type="text" name="app-key" class="form-control" readonly />
									<span id="officer-message"></span>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
<?php include (__DIR__ . "/../footer.php"); ?>
		
		<script type="text/javascript">
			$(document).ready (function () {
				var form = $('form#form-randomizer'),
					length = form.find ('input[name="key-length"]'),
					generatedKey = $(document).find ('[name="generated-key"]');

				$('input[name="generated-key"]').click (function (event) {
					if ($(this).val ().trim ().length > 0) {
						var $temp = $("<input/>");
						$('body').append ($temp);
						$temp.val ($(this).val ()).select ();
						document.execCommand ("copy");
						$temp.remove ();
						$.showDialog ("Copy Successful", "Encryption key copied to clipboard successfully!", "info-copy", modalSize.m, messageType.OKMessage);
					}
				});

				$('input[name="app-key"]').click (function (event) {
					if ($(this).val ().trim ().length > 0) {
						var $temp = $("<input/>");
						$('body').append ($temp);
						$temp.val ($(this).val ()).select ();
						document.execCommand ("copy");
						$temp.remove ();
						$.showDialog ("Copy Successful", "Encryption key copied to clipboard successfully!", "info-copy", modalSize.m, messageType.OKMessage);
					}
				});

				$('button:submit').click (function (event) {
					event.preventDefault ();
					var valid = true;
					form.find (':input').each (function () {
						$(this).removeClass ('is-valid is-invalid')
								.addClass (this.checkValidity () ? 'is-valid' : 'is-invalid');
						if ($(this).hasClass ('is-invalid')) valid = false;
					});

					if (valid) {
						$.ajax ({
							'method': 'put',
							'url': '<?php echo isset ($formAction) ? $formAction : ''; ?>',
							'contentType': 'application/json',
							'data': form.formToJSON (),
							'dataType': 'json'
						}).done (function (result) {
							if (result.status == '200') {
								var message = result.message;
								$('span#key-length').empty ();
								$('span#key-length').text (length.val ());
								$('input[name="generated-key"]').val ('');
								$('input[name="generated-key"]').val (message['appkey']);
								$('input[name="app-key"]').val ('');
								$('input[name="app-key"]').val (message['enckey']);
							} else $('span#generate-message').text (result.message);
						}).fail (function () {
						});
					}
				});

				$(document).on ('click', 'button', function (event) {
					var btnName = $(this).prop ('name');
					switch (btnName) {
						default:
							break;
						case "modal-btn-ok":
							$("#modal-dialog").modal ('hide');
							break;
					}
				});
			});
		</script>
	</body>
</html>