
/************************************* OnLoad ******************************************/

jQuery(document).ready(function() {
	
	markSelected();
	
    var import_form_options = {
       url: '/Special:OEController',
       dataType:  'json',
       beforeSubmit: prepareRequest,
       success: processResponse,
       data: {action: 'import', form : 'ImportForm', page_path: OEF_PAGE_PATH}
    };
    
    jQuery('.ae_import_form').submit(function() {
    	if (beforeSubmit(this)) {
    	   	jQuery(this).ajaxSubmit(import_form_options);
    	}
    	else appActive();
    	
    	return false;
    });
});




/************************************* Forms ******************************************/

function prepareRequest(formData, jqForm, options)
{ 
    return true; 
}

/**
 * Process respons
 * @param data
 * @param status
 * @return
 */
function processResponse(data , status)
{
	if (!data.status && data.errors)
	{
		alert(data.errors.global);
		jQuery('#TB_overlay').remove();
		return;
	}
	
	for(var kind in data)
	{
		for(var type in data[kind])
		{
			var m_data = data[kind][type];
			var msg = '';
						
			if(m_data['status'] != true) // Print main errors
			{
				for(var field in m_data['errors'])
				{
					if (!displayErrors(kind + '_' + type + '_' + field, m_data['errors'][field])) {
						msg += (msg.length > 0 ? ",&nbsp;" : "&nbsp;") + m_data['errors'][field];
					}
				}
			}
			
			// Print main message
			displayMessage(kind + '_' + type,  msg.length > 0 ? msg : m_data['result']['msg'], m_data['status']);
		}
	}
	
	jQuery('#TB_overlay').remove();
}


function beforeSubmit(form)
{
	appInactive();
	
	hideFieldErrors('ae_editform_field');
		
	appAddLoader();
	
	return true;
}


/************************************* Actions ********************************************/


/************************************* Functions ******************************************/

