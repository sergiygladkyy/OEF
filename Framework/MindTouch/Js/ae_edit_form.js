
var ae_index = {};
var ae_name_prefix = {};
var ae_template = {};

var custom_edit_form_options = {
	options: {},
    async: false,
    url: '/Special:OEController',
    dataType:  'json',
    beforeSubmit: prepareRequest,
    success: function (data, status) { processResponse(data, status, this.options); },
    data: {action: 'save', form: 'CustomForm'}
};

/************************************* OnLoad ******************************************/

jQuery(document).ready(function() {
	
	markSelected();
	
    var edit_form_options = {
  	  options: {},
  	  async: false,
      url: '/Special:OEController',
      dataType:  'json',
      beforeSubmit: prepareRequest,
      success: function (data, status) { processResponse(data, status, this.options); },
      data: {action: 'save'}
    };
    
    var object_edit_form_options = {
       options: {},
       async: false,
       url: '/Special:OEController',
       dataType:  'json',
       beforeSubmit: prepareRequest,
       success: function (data, status) { processObjectResponse(data, status, this.options); },
       data: {action: 'save', form: 'ObjectForm'}
    };

    jQuery('.ae_edit_form').submit(function() {
    	hideFieldErrors('ae_editform_field');
    	jQuery(this).ajaxSubmit(edit_form_options);
    	
    	return false;
    });
    
    jQuery('.ae_object_edit_form').submit(function() {
    	submitObjectForm(this, object_edit_form_options);
    	
    	return false;
    });
    
    jQuery('.ae_command').each(function(index) {
    	jQuery(this).click(function() { 
    		commandForm(this);
    	});
    });
    
    /**
     * Submit by command
     * 
     * @param element - DOM object
     * @return
     */
    function commandForm(element)
    {
    	var options;
    	var method;
    	var form    = jQuery(element).parents('form');
    	var command = jQuery(element).attr('command');
    	
    	if (jQuery(form).hasClass('ae_object_edit_form')) {
    		method  = 'submitObjectForm';
    		options = object_edit_form_options;
    	}
    	else if (jQuery(form).hasClass('oe_custom_edit_form')) {
    		method  = 'submitForm';
    		options = custom_edit_form_options;
    	}
    	else {
    		method  = 'submitForm';
    		options = edit_form_options;
    	}
    	
    	switch(command)
    	{
    		case 'save_and_close':
    			options.options.close = true;
    		break;
    		
    		default:
    			options.options.close = false;
    	}
    	
    	try {
    		eval(method + '(form, options)');
    	}
    	catch(e) { ; }
    }
});




/************************************* Forms ******************************************/

/**
 * Submit object form
 * 
 * @param form - form object
 * @return void
 */
function submitObjectForm(form, options)
{
	appInactive();
	
	if (beforeSubmit(form)) jQuery(form).ajaxSubmit(options);
	
	appActive();
}

/**
 * Submit simple form
 * 
 * @param form - form object
 * @return void
 */
function submitForm(form, options)
{
	appInactive();
	appAddLoader();
	
	hideFieldErrors('ae_editform_field');
	
	jQuery(form).ajaxSubmit(options);

	appActive();
}

/**
 * Prepare request
 * 
 * @param formData
 * @param jqForm
 * @param options
 * @return boolean
 */
function prepareRequest(formData, jqForm, options)
{ 
	/*var queryString = $.param(formData); 
	alert('About to submit: \n\n' + queryString);*/ 
	return true; 
}

/**
 * Process responce to simple form
 * 
 * @param data
 * @param status
 * @return
 */
function processResponse(data, status, options)
{
	for(var kind in data)
	{
		for(var type in data[kind])
		{
			var result = data[kind][type];
			var msg = '';
			
			if (!result['status'])
			{
				for(var field in result['errors'])
				{
					if (!displayErrors(kind + '_' + type + '_' + field, result['errors'][field])) {
						msg += (msg.length > 0 ? ",&nbsp;" : "&nbsp;") + result['errors'][field];
					}
				}
				
				if (result['result'] && result['result']['msg']) {
					if (msg) {
						msg = result['result']['msg'] + msg;
					}
					else msg = result['result']['msg'];
				}
			}
			
            /* Check close flag */
			
			if (options.close == true && result['status'] == true) // Close window
			{
				window.self.close();
				
				if (window.opener && window.opener.length)
				{
					if (window.opener.childClose)
					{
						window.opener.childClose({
							prefix:  (kind + '_' + type),
							message: (msg.length > 0 ? msg : result['result']['msg']),
							type:     result['status']
						});
					}
					
					window.opener.focus();
				}
				
				return;
			}
			
			if (result['result']['_id']) // Insert main ID
			{
				insertId(kind + '_' + type, result['result']['_id']);
				var header = document.getElementById(kind + '_' + type + '_header');
				if (header)	{
					header.innerHTML = header.innerHTML.replace(/New/g, 'Edit');
				}
				jQuery('#'+ kind + '_' + type + '_item input[type=submit]').attr('value', 'Update');
				if (jQuery('.' + prefix + '_actions')) {
					var prefix = kind + '_' + type;
					jQuery('.' + prefix + '_actions').css('display', 'block');
				}
			}
			
			// Print main message
			displayMessage(kind + '_' + type,  msg.length > 0 ? msg : result['result']['msg'], result['status']);
		}
	}
}

/**
 * Process respons to object form
 * @param data
 * @param status
 * @return
 */
function processObjectResponse(data, status, options)
{
	var state = true;
	
	for(var main_kind in data)
	{
		for(var main_type in data[main_kind])
		{
			var m_data = data[main_kind][main_type];
			var msg = '';
			
			/* Check object result */
			
			if(m_data['status'] != true) // Print main errors
			{
				for(var field in m_data['errors'])
				{
					if (!displayErrors(main_kind + '_' + main_type + '_' + field, m_data['errors'][field])) {
						msg += (msg.length > 0 ? ",&nbsp;" : "&nbsp;") + m_data['errors'][field];
					}
				}
			}
			
			/* Check tabular result */
			
			if (options.close == true) options.close = m_data['status'];
			
			if (m_data['tabulars'])
			{
				state = processTabularResponce(main_kind + '_' + main_type + '_tabulars', m_data['tabulars'], options);
			}
			
			/* Check close flag */
			
			if (options.close == true && state == true) // Close window
			{
				window.self.close();
				
				if (window.opener && window.opener.length)
				{
					if (window.opener.childClose)
					{
						window.opener.childClose({
							prefix:  (main_kind + '_' + main_type),
							message: (msg.length > 0 ? msg : m_data['result']['msg']),
							type:     m_data['status']
						});
					}
					
					window.opener.focus();
				}
				
				return;
			}
			else if (m_data['result']['_id']) // Insert main ID
			{
				insertId(main_kind + '_' + main_type, m_data['result']['_id']);
				var header = document.getElementById(main_kind + '_' + main_type + '_header');
				if (header)	{
					header.innerHTML = header.innerHTML.replace(/New/g, 'Edit');
				}
				jQuery('#'+ main_kind + '_' + main_type + '_item input[type=submit]').attr('value', 'Update');
				if (jQuery('.' + prefix + '_actions')) {
					var prefix = main_kind + '_' + main_type;
					jQuery('.' + prefix + '_actions').css('display', 'block');
				    jQuery('#' + prefix + '_post_flag').css('display', '');
				}
			}
			
			// Print main message
			displayMessage(main_kind + '_' + main_type,  msg.length > 0 ? msg : m_data['result']['msg'], m_data['status']);
		}
	}
}

/**
 * Process responce to tabular sections
 * 
 * @param kind
 * @param data
 * @return boolean
 */
function processTabularResponce(kind, data, options)
{
	var flag = true;
    
	/* Check tabular items */
	
	jQuery('.tabular_item:hidden').remove();
	
	for(var type in data)
	{
		for(var i in data[type])
		{
			var m_data = data[type][i];
			
			if(m_data['status'] != true) // Print main errors
			{
				flag = false;
				
				for(var field in m_data['errors'])
				{
					displayErrors(kind + '_' + type + '_' + i + '_' + field, m_data['errors'][field]);
				}
			}
			else if (m_data['result']['_id']) // Insert main ID
			{
				insertId(kind + '_' + type, m_data['result']['_id'], i);
			}
		}
	}
	
	/* Check close flag */
	
	if (flag && options.close) return flag;
	
	/* Print main message */
	
	if (flag) {
		displayMessage(kind + '_' + type, 'Tabular section updated succesfully', flag);
	}
	else {
		displayMessage(kind + '_' + type, 'At updating Tabular section there were some errors', flag);
	}
	
	return flag;
}

/**
 * Executed before submit
 *  
 * @param form - form object
 * @return
 */
function beforeSubmit(form)
{
	hideFieldErrors('ae_editform_field');
	jQuery(form).find(".tabular_item:hidden .tabular_col").remove();
	jQuery(form).find('.tabular_item:hidden').each(function(index) {
		var hidden = jQuery(this).find('input[type=hidden]').get(0);
		var name = hidden.getAttribute('name');
		name = name.replace(/\[[^\]\[]+\]\[[^\[\]]+\]$/gi, '[deleted][]');
		hidden.setAttribute('name', name);
		
	});
	
	if (jQuery(form).find('.ae_field_posted').size() != 0)
	{
		if (confirm('This document is posted. You must clear posted before update.\n\n Clear posted and update?'))
		{
			appAddLoader();
			var prefix = jQuery(form).attr('id');
			var id = jQuery('#' + prefix + ' input[type=hidden]').attr('value');
			prefix = prefix.replace('_item', '');
			puid = prefix.split('_');
			var len  = puid.length;
			var kind = puid.slice(0, len-1);
			var type = puid[len-1];
			kind = kind.join('.');
			
			if (!executeClearPosting(kind, type, id, prefix)) return false;
		}
		else return false;
	}
	
	appAddLoader();
	
	return true;
}





/************************************* Actions ******************************************/

/**
 * Add tabular section item
 * 
 * @param uid
 * @param prefix
 * @return
 */
function addTabularSectionItem(uid, prefix)
{
	ae_index[uid]++;
	var content = ae_template[uid];
	content = content.replace(/%%i%%/g, ae_index[uid]);
	content = content.replace(/%%script%%/g, 'script');
	jQuery('#' + prefix + '_edit_block').append(content);
}

/**
 * Delete tabular section item
 * 
 * @param uid
 * @param prefix
 * @return
 */
function deleteTabularSectionItems(uid, prefix)
{
	jQuery('#' + prefix + '_edit_block input[type=checkbox]').each(function(index) {
    	if (this.checked) {
    		if (this.value > 0) {
    			jQuery(this).closest('.tabular_item').css('display', 'none');
    		}
    		else {
    			jQuery(this).closest('.tabular_item').remove();
    		}
    	}
    });
}

/**
 * Action post document
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return
 */
function post(kind, type, id, prefix)
{
	appInactive();
	appAddLoader();
	
	var result = executePost(kind, type, id, prefix);

	appActive();
	
	return result;
}

/**
 * Action document Clear posting
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return
 */
function clearPosting(kind, type, id, prefix)
{
	appInactive();
	appAddLoader();
	
	var result = executeClearPosting(kind, type, id, prefix);

	appActive();
	
	return result;
}





/************************************* Functions ******************************************/

/**
 * Post Item
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return
 */
function executePost(kind, type, id, prefix)
{
	var ret = true;
	var id  = parseInt(id, 10);
	
	if (!id) {
		displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Unknow element id', false);
		return false;
	}
	
	jQuery.ajax({
	    url: '/Special:OEController',
	    async: false,
	    type: 'POST',
	    data: ({aeform: {kind: kind, type: type, _id : id}, action: 'post'}),
	    dataType: 'json',
	    success: function (data , status)
	    {
			ret = data['status'];
			
			if(!data['status'])
			{
				var msg = '';
				for(var index in data['errors'])
				{
					msg += (index > 0 ? ",&nbsp;" : "&nbsp;") + data['errors'][index];
				}
				displayMessage(kind.replace(/\./g, '_') + '_' + type, "At Post there were some errors:" + msg + ".", false);
			}
			else {
				var element = jQuery('#' + prefix + '_post_flag .ae_field_not_posted');
				jQuery(element).removeClass('ae_field_not_posted');
				jQuery(element).addClass('ae_field_posted');
				jQuery(element).find('span.ae_field_posted_text').css('display', 'block');
				jQuery(element).find('span.ae_field_not_posted_text').css('display', 'none');
				displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Post succesfully', true);
			}
	    }
	});
	
	return ret;
}

/**
 * Clear posting Item
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return
 */
function executeClearPosting(kind, type, id, prefix)
{
	var ret = true;
	var id  = parseInt(id, 10);
	
	if (!id) {
		displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Unknow element id', false);
		return false;
	}
	
	jQuery.ajax({
	    url: '/Special:OEController',
	    async: false,
	    type: 'POST',
	    data: ({aeform: {kind: kind, type: type, _id : id}, action: 'unpost'}),
	    dataType: 'json',
	    success: function (data , status)
	    {
			ret = data['status'];
			
			if(!data['status'])
			{
				var msg = '';
				for(var index in data['errors'])
				{
					msg += (index > 0 ? ",&nbsp;" : "&nbsp;") + data['errors'][index];
				}
				displayMessage(kind.replace(/\./g, '_') + '_' + type, "At Clear posting there were some errors:" + msg + ".", false);
			}
			else {
				var element = jQuery('#' + prefix + '_post_flag .ae_field_posted');
				jQuery(element).removeClass('ae_field_posted');
				jQuery(element).addClass('ae_field_not_posted');
				jQuery(element).find('span.ae_field_posted_text').css('display', 'none');
				jQuery(element).find('span.ae_field_not_posted_text').css('display', 'block');
				displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Clear posting succesfully', true);
			}
	    }
	});
	
	return ret;
}






/**
 * Insert id tag after entity creation
 * 
 * @param uid
 * @param id
 * @param i
 * @return
 */
function insertId(uid, id, i)
{
	var name   = ae_name_prefix[uid];
	var tag_id = uid;
	if (i) {
		name   += '['+ i +']';
		tag_id += '_' + i;
	}
	name   += '[_id]';
	tag_id += '_item';
	var input = document.createElement("input");
	input.setAttribute('type', 'hidden');
	input.setAttribute('name', name);
	input.setAttribute('value', id);
    var item = document.getElementById(tag_id);
    item.appendChild(input);
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
  jQuery('form option:selected').each( function(index) { jQuery(this).css('color', '#801020'); } );
}

/*
function showEditField(prefix)
{
  jQuery('#' + prefix + '_values').css('display', 'none');
  jQuery('#' + prefix + '_fields').css('display', 'block');
  jQuery('#' + prefix + '_field').focus();
}

function hideEditField(prefix, type)
{
  if (type == 'reference') {
	var field  = document.getElementById(prefix + '_field');
	var option = field.options[field.selectedIndex];
	var link   = document.getElementById(prefix + '_value');
	var span   = document.getElementById(prefix + '_val');
	if (option.value != 0) {
		link.href = link.href.replace(/id=[0-9]{1,}/g, 'id=' + option.value);
		link.setAttribute('style', 'display: block;');
		span.innerHTML = option.text;
	}
	else {
        span.innerHTML = 'not set';
	    link.setAttribute('style', 'display: none;');
	}
  }
  else {
      var value = document.getElementById(prefix + '_field').value;
	  if (!value) {
		  value = 'not set';
	  }
	  document.getElementById(prefix + '_value').innerHTML = value;
  }
  jQuery('#' + prefix + '_values').css('display', 'block');
  jQuery('#' + prefix + '_fields').css('display', 'none');
}
*/






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
 * Get and display custom form
 * 
 * @param string uid    - entity uid
 * @param string form   - form name
 * @param array  params - other params
 * @param string tag_id - tag id
 * @return void
 */
function displayCustomForm(uid, form, params, tag_id)
{
	params.uid    = uid;
	params.tag_id = tag_id;
	
	jQuery.ajax({
	    url: '/Special:OEController',
	    async: false,
	    type: 'POST',
	    data: ({action: 'generateForm', uid: uid, name: form, parameters: params}),
	    dataType: 'json',
	    success: function (data , status)
	    {
			if (!data['status'])
			{
				var msg = '';
				for (var index in data['errors'])
				{
					msg += (index > 0 ? ",&nbsp;" : "&nbsp;") + data['errors'][index];
				}
				displayMessage(uid.replace(/\./g, '_'), "At generate form there were some errors:" + msg + ".", false);
			}
			else
			{
				jQuery('head').append(data['result']['scripts']);
				jQuery('#' + tag_id).html(data['result']['form']);
				markSelected();
			}
	    },
	    error: function (XMLHttpRequest, textStatus, errorThrown)
	    {
			//alert('Request error');
			;
	    }
	});
}
