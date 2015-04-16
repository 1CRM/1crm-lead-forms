jQuery(document).ready(function() {

	var $ = jQuery;

	$('.ocrmlf-form').each(function(i,f) {
		var form = this;
		$(form).ajaxForm({
			beforeSubmit: function(arr, $form, options) {
				$form.find('.ocrmlf-input.submit').each(function(j, b) {
					$(this).attr('revert_value', this.value);
					if ($(this).attr('wait_value'))
						this.value = $(this).attr('wait_value');
					this.disabled = "disabled";
				});
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
						if (messages[err])
							$(span).append(messages[err]);
						else
							$(span).append(err);
						elt.after(span);
					}
				}
				if (typeof(data.http_result) == 'object' && data.http_result) {
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
					if (typeof(data.http_result) == 'object' && data.http_result) {
						if (data.http_result.redirect)
							redirect = data.http_result.redirect;
						if (data.http_result.message)
							message = data.http_result.message;
					}
					$(".ocrmlf-form-status").append(message).show();
					if (redirect)
						window.location.href = redirect;
				}

				$form.find('.ocrmlf-input.submit').each(function(j, b) {
					this.value = $(this).attr('revert_value');
					this.disabled = "";
				});
			},
			data : {
				_ocrmlf_ajax: '1'
			},
			dataType: 'json',
			error: function(xhr, status, error, $form) {
				var id = $form.find('[name="_ocrmlf_id"]').val();
				var messages = eval('_ocrmlf_messages_' + id);
				$(".ocrmlf-form-status").empty().attr('role', 'alert').append(messages.error_posting).show();
				$form.find('.ocrmlf-input.submit').each(function(j, b) {
					this.value = $(this).attr('revert_value');
					this.disabled = "";
				});
			}
		});
	});

	$('.ocrmlf-form').on('submit', function() {
	});

});
