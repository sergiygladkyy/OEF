var OEF_PAGE_PATH = null;

/**
 * Return prefix
 * 
 * @param string kind
 * @param string type
 * @return string
 */
function getPrefix(kind, type)
{
	return kind.replace('.', '_') + '_' + type;
}

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



/************************************* Windows ***********************************************/

var OEF_WINDS = {};
var OEF_MAIN_WINDOW = window;

// Initialize window
while (true)
{
	if (!OEF_MAIN_WINDOW.opener || OEF_MAIN_WINDOW.opener == OEF_MAIN_WINDOW) break;
	
	OEF_MAIN_WINDOW = OEF_MAIN_WINDOW.opener;
	
	OEF_WINDS = OEF_MAIN_WINDOW.OEF_WINDS;
}

/**
 * Constuctor oefPopup
 *  
 * @param string kind
 * @param string type
 * @param object params
 * @param string options
 */
function oefPopup(kind, type, params, options)
{
	this.kind = kind ? kind : null;
	this.type = type ? type : null;
	
	this.params  = params  ? params  : {};
	this.options = options ? options : 'width=768,height=600,menubar=1,toolbar=0,scrollbars=1,resizable=0';
	this.target  = '_blank';
	
	var required_params = {
		'default': {kind: 'kind', type: 'type'},
		'displayItemForm': {kind: 'kind', type: 'type', id: 'id'},
		'displayEditForm': {kind: 'kind', type: 'type', id: 'id'}
	};
	
	/**
	 * Set current kind
	 * 
	 * @param string kind
	 * @return void
	 */
	this.setKind = function(kind)
	{
		this.kind = kind;
	};
	
	/**
	 * Set current type
	 * 
	 * @param string type
	 * @return void
	 */
	this.setType = function(type)
	{
		this.type = type;
	};
	
	/**
	 * Set default (common) parameters in query string
	 * 
	 * @param object params
	 * @return void
	 */
	this.setDefaultQSParams = function(params)
	{
		this.params = params;
	};
	
	/**
	 * Set options string to window.open
	 * 
	 * @param string options
	 * @return void
	 */
	this.setWindowOptionsString = function(options)
	{
		this.options = options;
	};
	
	/**
	 * Set target
	 *  
	 *  _blank  - URL is loaded into a new window. This is default
     *  _parent - URL is loaded into the parent frame
     *  _self   - URL replaces the current page
     *  _top    - URL replaces any framesets that may be loaded
     *  name    - The name of the window
     *
	 * @param string target
	 * @return void
	 */
	this.setTarget = function(target)
	{
		this.target = target;
	};
	
	/**
	 * Display popup window
	 * 
	 * @param action
	 * @param params
	 * @return boolean
	 */
	this.displayWindow = function(action, params)
	{
		if (!action) return false;
		
		if (params)
		{
			params = array_merge(this.params, params);
		}
		else params = this.params;
		
		var win = this.openWindow(this.kind, this.type, action, params);
		
		if (win)
		{
			//win.onload = function() { onLoad(win); };
			
			win.focus();

			return true;
		}
		
		return false;
	};
	
	/**
	 * Open popup window
	 * 
	 * @param string kind
	 * @param string type
	 * @param string action
	 * @param object params
	 * @return window object
	 */
	this.openWindow = function(kind, type, action, params)
	{
		var required, id, uri, index, cache;
		
		// Check parameters
		if (!kind || !type) return false;
		
		if (!action) action = false;
		
		required = required_params[action] ? required_params[action] : required_params['default'];
		
		id = required.id ? (params.id ? params.id : 0) : false;
		
		//if (id === 0) return false;
		
		// Generate URI
		params.uid   = kind + '.' + type;
		
		if (action)
		{
			params.actions = action;
		}
		else if (params.actions)
		{
			params.actions = null;
		}
		
		params.popup = 1;
		
		uri = generateUri(params);
		
		// Open window
		if (!OEF_WINDS[kind]) OEF_WINDS[kind] = {};
		
		if (!OEF_WINDS[kind][type]) OEF_WINDS[kind][type] = {};
		
		index = action;
		cache = OEF_WINDS[kind][type];
		
		if (id !== false)
		{
			if (!cache[index]) cache[index] = {};
			
			cache = cache[index];
			index = id;
		}
		
		if (!cache[index] || cache[index].closed)
		{
			if (this.target != '_blank')
			{
				removeFromCache(window);
			}
			
			cache[index] = window.open(uri, this.target, this.options);
		}
		
		return cache[index];
	};
	
	/**
	 * Close all opened window
	 */
	this.closeAllWindow = function ()
	{
		for (var kind in OEF_WINDS)
		{
			for (var type in OEF_WINDS[kind])
			{
				for (var action in OEF_WINDS[kind][type])
				{
					if (required_params[action]['id'])
					{
						for (var id in OEF_WINDS[kind][type][action])
						{
							if (!OEF_WINDS[kind][type][action][id].closed)
							{
								OEF_WINDS[kind][type][action][id].close();
							}
						}
					}
					else if (!OEF_WINDS[kind][type][action].closed)
					{
						OEF_WINDS[kind][type][action].close();
					}
				}
			}
		}
	};
	
	/**
	 * Generate URI
	 * 
	 * @param object params - query string params
	 * @return string
	 */
	function generateUri(params)
	{
		var qs = '';
		
		for (var name in params)
		{
			qs += '&' + name + '=' + params[name];
		}
		
		if (qs.length) qs = '?' + qs.substr(1);
		
		return self.location.pathname + qs;
	}
	
	/**
	 * Merge two arrays
	 * 
	 * @param arr1
	 * @param arr2
	 * @return object
	 */
	function array_merge(arr1, arr2)
	{
		for (var name in arr2)
		{
			arr1[name] = arr2[name];
		}
			
		return arr1;
	}
	
	/**
	 * Process onLoad event
	 * 
	 * @return void
	 */
	function onLoad(win)
	{
		jQuery(win.document).find('body').append('<h1>On load</h1>');
	}
	
	/**
	 * Remove window from cache
	 * 
	 * @param object win - window object
	 * @return boolean
	 */
	function removeFromCache(win)
	{
		for (var kind in OEF_WINDS)
		{
			for (var type in OEF_WINDS[kind])
			{
				for (var action in OEF_WINDS[kind][type])
				{
					if (required_params[action]['id'])
					{
						for (var id in OEF_WINDS[kind][type][action])
						{
							if (OEF_WINDS[kind][type][action][id] == win)
							{
								OEF_WINDS[kind][type][action][id] = null;
							}
						}
					}
					else if (OEF_WINDS[kind][type][action] == win)
					{
						OEF_WINDS[kind][type][action] = null;
					}
				}
			}
		}
	}
}
