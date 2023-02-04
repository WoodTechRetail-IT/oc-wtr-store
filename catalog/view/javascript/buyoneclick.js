$(document).ready(function() {
	$('body').on('focus','#boc_form input', function(){
		$(this).parent().removeClass('has-error');
	});
	$('body').on('focus','#boc_form .checkbox label', function(){
		$(this).parent().removeClass('has-error');
	});	
    $('body').on('submit','#boc_form', function(event) {
		event.preventDefault ? event.preventDefault() : (event.returnValue = false);
		if(!formValidation(event.target)){return false;}
		var sendingForm = $(this);
		var submit_btn = $(this).find('button[type=submit]');
		var value_text = $(submit_btn).text();
		var waiting_text = 'SENDING';
		$.ajax({
			url: 'index.php?route=extension/module/buyoneclick/checkout/buyoneclick/submit',
			type: 'post',
			data: $('#boc_form input[type=\'hidden\'], #boc_form input[type=\'text\'], #boc_form input[type=\'tel\'], #boc_form input[type=\'email\'], #boc_form textarea'),
			dataType: 'json',
			beforeSend: function() {
				$(submit_btn).prop( 'disabled', true );
				$(submit_btn).addClass('waiting').text(waiting_text);
			},
			complete: function() {
				$(submit_btn).button('reset');
			},
			success: function(json) {
				console.log('Ajax success!');
				clickAnalyticsSuccess();
				if (json['redirect']) {
					location = json['redirect'];
				} else if (json['success']) {
					var success = true;
					$(sendingForm).trigger('reset');
					$(submit_btn).removeClass('waiting');
					$(submit_btn).text(value_text);
					$(submit_btn).prop( 'disabled', false );
					$('#boc_order').modal('hide');
					$('#boc_order').on('hidden.bs.modal', function (e) {
						if (success) {
							$('#boc_success').modal('show');
							setTimeout(function(){
								$('#boc_success').modal('hide');
							}, 4000);
							success = false;
						}
					});
				}
			},
			error: function(xhr, ajaxOptions, thrownError) {
				console.log(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				$(submit_btn).prop( 'disabled', false );
				$(submit_btn).removeClass('waiting').text("ERROR");
				setTimeout(function(){
					$(submit_btn).delay( 3000 ).text(value_text);
				}, 3000);
			}
		});
		event.preventDefault();
    });
});
function formValidation(formElem){
	var elements = $(formElem).find('.required');
	var errorCounter = 0;
	$(elements).each(function(indx,elem){
		var placeholder = $(elem).attr('placeholder');
		if($.trim($(elem).val()) == '' || $(elem).val() == placeholder){
			$(elem).parent().addClass('has-error');
			errorCounter++;
		} else {
			$(elem).parent().removeClass('has-error');
		}
	});

	if ($(formElem).find('#boc_agree').length) {
		if ($(formElem).find('#boc_agree').is(':checked')) {
			$(formElem).find('#boc_agree').parent().parent().removeClass('has-error');
		} else {
			$(formElem).find('#boc_agree').parent().parent().addClass('has-error');
			errorCounter++;
		}
	}

	$(formElem).find('input[name="boc_phone"]').each(function() {
		// console.log('phone testing');
		var pattern = new RegExp(/^(\(?\+?[0-9]*\)?)?[0-9_\- \(\)]*$/);
		var data_pattern = $(this).attr('data-pattern');
		var data_placeholder = $(this).attr('placeholder');
		// console.log(pattern.test($(this).val()));
		if(!pattern.test($(this).val()) || $.trim($(this).val()) == '' ){
			console.log('NON valid phone!');
			$('input[name="boc_phone"]').parent().addClass('has-error');
			errorCounter++;
		} else if (data_pattern == 'true') {
			console.log('data-pattern = true');
			if ($(this).val().length < data_placeholder.length) {
				console.log('Phone too short!!!');
				$('input[name="boc_phone"]').parent().addClass('has-error');
				errorCounter++;
			}
		} else {
			$(this).parent().removeClass('has-error');
		}
	});
	if (errorCounter > 0) {
		return false;
	} else {
		return true;
	}
}