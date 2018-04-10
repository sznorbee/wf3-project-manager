function validate(nameFromForm)
{
	$.post
	(
		'/username/available',
		{
			username: nameFromForm
		},
		function(responseData)
		{
			$('.username-validation').remove();
			
			if(responseData.available)
			{
				$('label[for="form_username"]').append
												(
												  '<span class="username-available username-validation"> available</span>'
												);
				return;
			}
			$('label[for="form_username"]').append
											(
											  '<span class="username-unavailable username-validation"> unavailable</span>'
											);
		}
	);
}
$('#form_username').on
					(
					  'keyup', function()
					  		   {
						  		validate($(this).val());
					  		   }
					);