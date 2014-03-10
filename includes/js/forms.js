jQuery(document).ready(function() {

	var $ = jQuery;

	$('.ocrmlf-form').each(function(i,f) {
		var form = this;
		$(form).ajaxForm({
			beforeSubmit: function(arr, $form, options) {
			},
			success: function(data, status, xhr, $form) {
				var id = $form.find('[name="_ocrmlf_id"]').val();
				var messages = eval('_ocrmlf_messages_' + id);
				$(".ocrmlf-form-status").empty().removeAttr('role').hide();
				$form.find('.ocrmlf-invalid-field').remove();
				var have_errors = false;
				if (data.errors) {
					have_errors = true;
					for (var i in data.errors) {
						var elt = $form.find('[name="' + i + '"]')
						var err = data.errors[i];
						var span = document.createElement('span');
						span.setAttribute('role', 'alert');
						span.setAttribute('class', 'ocrmlf-invalid-field');
						$(span).append(messages[err]);
						elt.after(span);
					}
				}
				if (typeof(data.http_result) == 'object') {
					if (data.http_result.error) {
						$(".ocrmlf-form-status").empty().attr('role', 'alert').append(data.http_result.error).show();
						have_errors = true;
					}
				}
				if (!have_errors) {
					var redirect = null;
					var message = messages.success;
					$form.resetForm().clearForm();
					if (data.redirect)
						redirect = data.redirect;
					if (typeof(data.http_result) == 'object') {
						if (data.http_result.redirect)
							redirect = data.http_result.redirect;
						if (data.http_result.message)
							message = data.http_result.message;
					}
					$(".ocrmlf-form-status").append(message).show();
					if (redirect)
						window.location.href = redirect;
				}
			},
			data : {
				_ocrmlf_ajax: '1'
			},
			dataType: 'json',
			error: function(xhr, status, error, $form) {
				var id = $form.find('[name="_ocrmlf_id"]').val();
				var messages = eval('_ocrmlf_messages_' + id);
				$(".ocrmlf-form-status").empty().attr('role', 'alert').append(messages.error_posting).show();
			}
		});
	});

	$('.ocrmlf-form').on('submit', function() {
	});

});
