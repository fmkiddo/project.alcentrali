/**
 * 
 */

const dialogSize = {
		xsmall: 'modal-xs',
		small: 'modal-sm',
		medium: 'modal-md',
		large: 'modal-lg',
		xlarge: 'modal-xl'
	}, messageType = {
		OkMessage: 'ok',
		YesNoMessage: 'yesno',
		YesNoCancelMessage: 'yesnocancel',
		OkCancelMessage: 'okcancel'
	}, buttons = {
		ok: ['ok'],
		yesno: ['yes', 'no'],
		yesnocancel: ['yes', 'no', 'cancel'],
		okcancel: ['ok', 'cancel']
	};
		
Object.freeze (dialogSize);	
Object.freeze (messageType);
Object.freeze (buttons);
	
$(document).ready (function () {
	
	var modal = $('div.modal'),
		modalDialog = modal.find ('.modal-dialog'),
		modalTitle = modalDialog.find ('.modal-title'),
		modalBody = modalDialog.find ('.modal-body'),
		modalFooter = modalDialog.find ('.modal-footer');
		
	$.fn.createModalButton = function (type) {
		var footer = $(this);
		
		$.createButton = function (type) {
			var button = $('<button/>', {
				'class': 'btn',
				'type': 'button',
				'name': 'button-dialog-' + type,
				'data-dismiss': 'modal'
			}), buttonType, icon;
			
			if (type=='cancel') {
				buttonType='btn-outline-secondary';
				icon = $('<i></i>', {
					'class': 'fas fa-undo fa-fw fa-2x'
				});
			} else if (type=='no') {
				buttonType='btn-outline-danger';
				icon = $('<i></i>', {
					'class': 'fas fa-times fa-fw fa-2x'
				});
			} else { 
				buttonType='btn-outline-primary';
				icon = $('<i></i>', {
					'class': 'fas fa-check fa-fw fa-2x'
				});
			}
			
			button.addClass (buttonType);
			button.append (icon);
			
			return button;
		};
		
		var footerButtons = buttons[type];
		$.each (footerButtons, function (k, v) {
			var createdButton = $.createButton (v);
			footer.append (createdButton);
		});
	};
		
	$.showMessageDialog = function (title, message, size, type, verticalCenter) {
		title = (title==undefined) ? '' : title;
		message = (message==undefined) ? '' : message;
		size = (size==undefined) ? dialogSize.medium : size;
		type = (type==undefined) ? messageType.OkMessage : type;
		verticalCenter = (verticalCenter==undefined) ? false : verticalCenter;
		
		modalDialog.removeClass ();
		modalDialog.addClass ('modal-dialog');
		if (verticalCenter) modalDialog.addClass ('modal-dialog-centered');
		modalDialog.addClass (size);
		
		modalTitle.empty ();
		modalTitle.text (title);
		modalBody.empty ();
		modalBody.text (message);
		
		modalFooter.empty ();
		modalFooter.createModalButton (type);
		
		modal.modal ('show');
	};
});