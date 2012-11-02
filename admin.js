
jQuery(function($){

	// Manage:

	$('.editinline').click(function() {
		setTimeout( function() {
			$('.inline-edit-password-switch').prop('checked',function(){
				return $('.inline-edit-password-input').val() ? 'checked' : '';
			});
		}, 1 ); // hacky
	});

	$('<label><input type="checkbox" class="inline-edit-password-switch" /> <span class="checkbox-title">' + gpp.password_protected + '</span></label>').insertAfter('.inline-edit-password-input').click(function(){
		val = $('input',this).prop('checked') ? gpp.password : '';
		$('.inline-edit-password-input').val(val);
	});
	$('.inline-edit-password-input').hide();

	// Settings:

	$('#add_gpp').click(function(e){
		$('.globalpostpassword:last').after($('.globalpostpassword:last').clone().show());
		$('.globalpostpassword:last input').val('').focus();
		e.preventDefault();
	});

	$('#globalpostpassword').change(function(){
		if ( '' == $(this).val() )
			$(this).closest('tr').addClass('form-invalid');
		else
			$(this).closest('tr').removeClass('form-invalid');
	});

	$('#globalpostpassword_form').submit(function(e){
		if ( '' == $('#globalpostpassword').val() ) {
			alert( gpp.not_blank );
			e.preventDefault();
		}
	});

});
