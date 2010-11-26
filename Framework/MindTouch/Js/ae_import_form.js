
/************************************* OnLoad ******************************************/

jQuery(document).ready(function() {
	
	markSelected();
	
    var import_form_options = {
       url: '/Special:OEController',
       dataType:  'json',
       beforeSubmit: prepareRequest,
       success: processResponse,
       data: {action: 'import', form : 'ImportForm'}
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

/**
 * Display editform fields errors
 * 
 * @param prefix
 * @param errors
 * @return
 */
function displayErrors(prefix, errors)
{
	if (jQuery('.' + prefix + '_errors').size() == 0) return false;
	
	jQuery('.' + prefix + '_errors').each(function (index) { 
		this.innerHTML = '<li>' + errors + '</li>';
	});
	jQuery('.' + prefix + '_errors').css('display', 'block');
	
	return true;
}

/**
 * Hide all field errors
 * 
 * @return
 */
function hideFieldErrors(prefix)
{
	jQuery('.' + prefix + '_errors').css('display', 'none');
}

/**
 * Display form message
 * 
 * @param prefix
 * @param message
 * @param type
 * @return
 */
function displayMessage(prefix, message, type)
{
	jQuery('.' + prefix + '_message ul').each(function (index) { 
		this.innerHTML = '<li>' + message + '</li>';
	});
	if (type) {
		jQuery('.' + prefix + '_message').removeClass('errormsg');
		jQuery('.' + prefix + '_message').addClass('successmsg');
	}
	else {
		jQuery('.' + prefix + '_message').removeClass('successmsg');
		jQuery('.' + prefix + '_message').addClass('errormsg');
	}
	jQuery('.' + prefix + '_message').css('display', 'block');
}

/**
 * Mark selected in <select ..>
 *  
 * @return
 */
function markSelected()
{
  jQuery('form option').each( function(index) { jQuery(this).removeAttr('style'); } );
  jQuery('form option:selected').each( function(index) { jQuery(this).css('color', '#801020'); } );
}





/**
 * Set Application in Active
 * 
 * @return void
 */
function appActive()
{
	jQuery('#TB_overlay').remove();
}

/**
 * Set Application in Inactive
 * 
 * @return void
 */
function appInactive()
{
	if (jQuery('#TB_overlay').size() != 0) return;
	
	jQuery('body').append('<div id="TB_overlay" class="TB_overlayBG"></div>');
}

/**
 * Add Loader to page
 * @return
 */
function appAddLoader()
{
	if (jQuery('#TB_overlay #TB_load').size() != 0) return;
	
	jQuery('#TB_overlay').append('<div id="TB_load" style="display: block; margin-top: -10%;"><img src="/skins/common/jquery/thickbox/loadingAnimation.gif"></div>');
}