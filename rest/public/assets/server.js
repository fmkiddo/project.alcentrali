/**
 * 
 */
$(document).ready (function () {
	$('#dataTable-client').DataTable ();
	
	$('input:checkbox').change (function () {
		var el = $(this), 
			name = $(this).prop ('name');
		if (name.startsWith ('clientID')) {
			var data = {
				'request-type': 'update-active-client',
				'data': {
					'dataName': name,
					'active': $(this).is(':checked')
				}
			}
			
			$.ajax ({
				'method': 'put',
				'url': 'http://localhost/assets/server/public/dashboard/requests',
				'contentType': 'application/json',
				'data': JSON.stringify (data),
				'dataType': 'json'
			}).done (function (result) {
				if (result.status == 200) {
					var elParents = el.parents ('tr');
					if (data.data.active) elParents.prop ('style', 'background-color: #D4EDDA');
					else elParents.prop ('style', 'background-color: #F8D7DA');
				}
			}).fail (function () {
				
			});
		}
	});
	
	$('body').on ('click', 'button', function (event) {
		var el = event.target.nodeName;
		switch (el) {
			default:
				break;
			case 'BUTTON':
				var name = $(this).attr ('data-name');
				switch (name) {
					default:
						break;
					case 'button-generate-clientid':
						var inputClientName = $('input[name="input-new-clientname"]'),
							inputClientId = $('input[name="input-new-clientid"]');
						if (inputClientName.val ().length > 0 && inputClientId.val ().length == 0) {
							var data = {
								'request-type': 'new-clientid',
								'data': inputClientName.val ()
							};
							$.ajax ({
								'method': 'put',
								'url': 'http://localhost/assets/server/public/dashboard/requests',
								'contentType': 'application/json',
								'data': JSON.stringify (data),
								'dataType': 'json'
							}).done (function  (result) {
								if (result.status == 200) inputClientId.val (result.message);
							}).fail (function () {
								
							});
						}
						break;
					case 'button-generate-clientkey':
						var inputClientName = $('input[name="input-new-clientname"]'),
							inputClientId = $('input[name="input-new-clientid"]'),
							inputClientKey = $('input[name="input-new-clientkey"]');
							
						if (inputClientName.val ().length > 0 && inputClientId.val ().length > 0 && inputClientKey.val ().length == 0) {
							var data = {
								'request-type': 'new-clientkey',
								'data': {
									'inputClientName': inputClientName.val (),
									'inputClientId': inputClientId.val ()
								}
							};
							
							$.ajax ({
								'method': 'put',
								'url': 'http://localhost/assets/server/public/dashboard/requests',
								'contentType': 'application/json',
								'data': JSON.stringify (data),
								'dataType': 'json'
							}).done (function (result) {
								if (result.status == 200) inputClientKey.val (result.message);
							})
						}
						break;
					case 'button-addpic':
						$(this).next ().fadeIn ();
						$(this).parents ('.row').next ().slideDown ();
						break;
					case 'button-cancelpic':
						$(this).fadeOut ();
						$(this).parents ('.row').next ().slideUp ();
						break;
					case 'button-clear':
						break;
					case 'button-generate-dbpassword':
						var theButton = $(this),
							data = {
								'request-type': 'generate-dbpassword'
							};
						$.ajax ({
							'method': 'put',
							'url': 'http://localhost/assets/server/public/dashboard/requests',
							'contentType': 'application/json',
							'data': JSON.stringify (data),
							'dataType': 'json'
						}).done (function (result) {
							if (result.status == 200) {
								theButton.parents ('.form-group').find ('input:password').val (result.message);
								theButton.parents ('.form-group').find ('button[data-name="button-reveal-dbpassword"]').prop ('title', result.message);
							}
						}).fail (function () {
							
						});
						break;
					case 'button-reveal-dbpassword':
						var password = $(this).parents ('.form-group').find (':password');
						$(this).mouseup (function () {
							password.prop ('type', 'password');
						});
						
						$(this).mousedown (function () {
							password.prop ('type', 'text');
						});
						break;
				}
				break;
		}
	});
});