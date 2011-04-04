var OEF_PAGE_PATH = null;

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
	if (typeof errors == "undefined") return false;
	if (typeof errors != "object") {
		errors = [errors];
	}
		
	jQuery('.' + prefix + '_errors').each(function (index) { 
		this.innerHTML = '<li>' + errors.join('</li>\n<li>') + '</li>';
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
	
	switch (type)
	{
		// Success
		case true:
		case 1:
			jQuery('.' + prefix + '_message').removeClass('warningmsg errormsg');
			jQuery('.' + prefix + '_message').addClass('successmsg');
		break;
		
		// Warning
		case 2:
			jQuery('.' + prefix + '_message').removeClass('successmsg errormsg');
			jQuery('.' + prefix + '_message').addClass('warningmsg');
		break;
		
		// Error
		default:
			jQuery('.' + prefix + '_message').removeClass('successmsg warningmsg');
			jQuery('.' + prefix + '_message').addClass('errormsg');
	}
	
	jQuery('.' + prefix + '_message').css('display', 'block');
}

/**
 * Hide all messages
 * 
 * @return void
 */
function hideMessages()
{
	jQuery('.systemmsg').css('display', 'none');
}

/**
 * Change attribute "checked" in <input type="checkbox" ..>
 * @param _class
 * @param parent
 * @return
 */
function checkAll(_class, parent)
{
    jQuery('.' + _class + ' input[type=checkbox]').each(function(index) {
    	this.checked = parent.checked;
    });
}

/**
 * Mark selected in <select ..>
 *  
 * @return
 */
function markSelected()
{
	jQuery('form option').each( function(index) { jQuery(this).removeAttr('style'); } );
	jQuery('form option:selected').each( function(index) {
		jQuery(this).css('color', '#801020').attr('current', 'true'); 
	});
}

/**
 * Disabled form
 * 
 * @param selector - css selector
 * @return void
 */
function disabledForm(selector)
{
	jQuery(selector).find('input:not([command=cancel])').attr('disabled', true);
	jQuery(selector).find('select').attr('disabled', true);
	jQuery(selector).find('textarea').attr('disabled', true);
	jQuery(selector).find('.tabulars_actions').attr('disabled', true);
	jQuery(selector).find('.oef_datetime_picker').css('opacity', '0.5');
}

/**
 * Disabled form
 * 
 * @param selector - css selector
 * @return void
 */
function enabledForm(selector)
{
	jQuery(selector).find('input').attr('disabled', false);
	jQuery(selector).find('select').attr('disabled', false);
	jQuery(selector).find('textarea').attr('disabled', false);
	jQuery(selector).find('.tabulars_actions').attr('disabled', false);
	jQuery(selector).find('.oef_datetime_picker').css('opacity', '1');
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

/**
 * Display loader
 * 
 * @param boolean flag - if false - loader hide. Else - lodaer show
 * @return void
 */
function appDisplayLoader(flag)
{
	if (jQuery('#TB_overlay #TB_load').size() == 0 && flag)
	{
		appAddLoader();
	}
	else
	{
		jQuery('#TB_load').css("display", flag ? 'block' : 'none');
	}
}
