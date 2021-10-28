/**
 * 
 */
const modalSize 	= {"xl": "modal-xl", "l": "modal-lg", "m": "modal-md", "s": "modal-sm", "xs": "modal-xs"};
const messageType	= {"OKMessage": 0, "YesNoMessage": 1, "YesNoCancelMessage": 2, "OKCancelMessage": 4};
Object.freeze (modalSize);
Object.freeze (messageType);

$.base_url = function (postfix) {
	var baseUrl = "//localhost/central-restful";
	if (postfix == null) return baseUrl;
	else return baseUrl + "/" + postfix;
};

$.fn.formToJSON = function () {
	
    var formArray = $(this).serializeArray();
    var jsonOutput = {};

    $.each(formArray, function (i, element) {
		if (jsonOutput[element.name] !== undefined) {
			if (!jsonOutput[element.name].push) jsonOutput[element.name] = [jsonOutput[element.name]];
			jsonOutput[element.name].push (element.value || '');
		} else jsonOutput[element.name] = element.value || '';
    });
	
	return JSON.stringify (jsonOutput);
};

$.showDialog = function (title, content, name, size, type) {
	var modalDialog = $('body').find ('div#modal-dialog');
	
	if (modalDialog == undefined || modalDialog == null) 
		console.log ("missing modal dialog");
	else {
		var data = {
			"trigger": "getbuttonlocales",
			"data-lang": $('html').prop ('lang')
		};
		$.ajax ({
			"url": $.base_url ('api/getlocale'),
			"method": "put",
			"contentType": "application/json",
			"data": JSON.stringify (data),
			"dataType": "json"
		}).done (function (result) {
			if (result.status != 200) console.log (result.message);
			else {
				var modalDialog = $('div#modal-dialog'),
					modalDocument = modalDialog.find ('#modal-document'),
					modalTitle = modalDocument.find ('#modal-title'),
					modalBody = modalDocument.find ('.modal-body'),
					modalFooter = modalDocument.find ('.modal-footer'),
					size = (size !== undefined) ? size : modalSize.m,
					type = (type !== undefined) ? type : messageType.OKMessage;
				
				if (name !== undefined || name !== null) modalDialog.attr ("data-name", name);
				
				modalDocument.addClass (size);
				
				modalTitle.html (title);
				modalBody.text (content);
				
				var button = $.createDialogButton (type, result);
				modalFooter.html (button);
				
				modalDialog.modal ('show');
			}
		}).fail (function () {
			console.log ("something wrong with showDialog function!");
		});
	}
	
	modalDialog.on ('hidden.bs.modal', function (event) {
	
		var modalDocument = modalDialog.find ('#modal-document'),
			modalTitle = modalDocument.find ('#modal-title'),
			modalBody = modalDocument.find ('.modal-body'),
			modalFooter = modalDocument.find ('.modal-footer');
				
		$(this).removeAttr ('data-name');
		modalDocument.removeClass ('modal-xl modal-lg modal-md modal-sm modal-xs');
			
		modalTitle.empty ();
		modalBody.empty ();
		modalFooter.empty ();
	});
};

$.createDialogButton = function (type, result) {
	var button = $('<div>', {
		"style": "width: 100%"
	});
	switch (type) {
		default: 
			$('<button/>', {
				"type": "button",
				"class": "btn btn-primary btn-block",
				"name": "modal-btn-ok",
				"text": result.buttonData.ok
			}).appendTo (button);
			break;
		case 1:
			$('<button/>', {
				"type": "button",
				"class": "btn btn-primary btn-block",
				"name": "modal-btn-yes",
				"text": result.buttonData.yes
			}).appendTo (button);
			
			$('<button/>', {
				"type": "button",
				"class": "btn btn-outline-danger btn-block",
				"name": "modal-btn-no",
				"text": result.buttonData.no
			}).appendTo (button);
			break;
		case 2:
			$('<button/>', {
				"type": "button",
				"class": "btn btn-primary btn-block",
				"name": "modal-btn-yes",
				"text": result.buttonData.yes
			}).appendTo (button);
			
			$('<button/>', {
				"type": "button",
				"class": "btn btn-outline-danger btn-block",
				"name": "modal-btn-no",
				"text": result.buttonData.no
			}).appendTo (button);
			
			$('<button/>', {
				"type": "button",
				"class": "btn btn-outline-secondary btn-block",
				"name": "modal-btn-cancel",
				"text": result.buttonData.cancel
			}).appendTo (button);
			break;
		case 3:
			$('<button/>', {
				"type": "button",
				"class": "btn btn-primary btn-block",
				"name": "modal-btn-ok",
				"text": result.buttonData.ok
			}).appendTo (button);
			
			$('<button/>', {
				"type": "button",
				"class": "btn btn-outline-secondary btn-block",
				"name": "modal-btn-cancel",
				"text": result.buttonData.cancel
			}).appendTo (button);
			break; 
	}
	return button;
};