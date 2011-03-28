
var ae_index = {};
var ae_name_prefix = {};
var ae_template = {};
var pageAPI = '';
var processFormCommand = null;
var Context = new oefContext();

Context.addListener('end_response_process', onEndResponseProcess);

var custom_edit_form_options = {
	options: {},
    async: false,
    url: '/Special:OEController',
    dataType:  'json',
    beforeSubmit: prepareRequest,
    success: function (data, status) { processObjectResponse(data, status, this.options); },
    data: {action: 'save', form: 'CustomForm', page_path: OEF_PAGE_PATH}
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
      data: {action: 'save', page_path: OEF_PAGE_PATH}
    };
    
    var object_edit_form_options = {
       options: {},
       async: false,
       url: '/Special:OEController',
       dataType:  'json',
       beforeSubmit: prepareRequest,
       success: function (data, status) { processObjectResponse(data, status, this.options); },
       data: {action: 'save', form: 'ObjectForm', page_path: OEF_PAGE_PATH}
    };
    
    var constants_edit_form_options = {
    	options: {},
    	async: false,
    	url: '/Special:OEController',
    	dataType:  'json',
    	beforeSubmit: prepareRequest,
    	success: function (data, status) { processConstantsResponse(data, status, this.options); },
    	data: {action: 'updateConstants', page_path: OEF_PAGE_PATH}
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
    
    jQuery('.ae_constants_edit_form').submit(function() {
    	submitObjectForm(this, constants_edit_form_options);
    	
    	return false;
    });
    
    jQuery('.oef_dynamic_update').each(function(index) {
    	jQuery(this).change(function(event) {
    	    var dynamicUpdate = new oefDynamicUpdate();
    	    dynamicUpdate.processEvent(event);
    	});
    });
    
    jQuery('.ae_command').each(function(index) {
    	jQuery(this).click(function() { 
    		commandForm(this);
    	});
    });
    
    jQuery('.ae_edit_form, .ae_object_edit_form, .oe_custom_edit_form, .ae_constants_edit_form').
    	change(function(event) {
    		event = event || window.event;
    		node  = event.target || event.srcElement;
    		var id = jQuery(node).parents('form').attr('id');
    		
    		if (id) Context.setFormChangedFlag(id, true);
    	}
    );
    
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
    	else if (jQuery(form).hasClass('ae_constants_edit_form')) {
    		method  = 'submitForm';
    		options = constants_edit_form_options;
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
    		
    		case 'cancel':
    			var id = jQuery(form).attr('id');
    			
    			if (Context.getFormChangedFlag(id) && !confirm('By closing this form, you will lose the unsaved information. Continue?'))
    			{
    				return;
    			}
    			
    			window.self.close();
    			
    			if (window.opener && window.opener.length)
    			{
    				window.opener.focus();
    			}
    			return;
    		break;
    		
    		default:
    			options.options.close = false;
    	}
    	
    	try {
    		eval(method + '(form, options)');
    	}
    	catch(e) { ; }
    }
    
    processFormCommand = commandForm;
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
	
	if (beforeSubmit(form))
	{
		jQuery(form).ajaxSubmit(options);
	}
	else Context.setLastStatus(false);
	
	//appActive();
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

	//appActive();
}

/**
 * Calling after response process
 * 
 * @param params
 * @return
 */
function onEndResponseProcess(params)
{
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
 * Process response for ConstantsEditForm
 *  
 * @param result
 * @param status
 * @param options
 * @return
 */
function processConstantsResponse(result, status, options)
{
	var msg = '';
	var prefix = 'Constants';
	
	Context.setLastStatus(true);
	
	if (!result['status'])
	{
		Context.setLastStatus(false);
		
		for(var field in result['errors'])
		{
			if (!displayErrors(prefix + '_' + field, result['errors'][field])) {
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
					prefix:  prefix,
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
		insertId(prefix, result['result']['_id']);
		var header = document.getElementById(prefix + '_header');
		if (header)	{
			header.innerHTML = header.innerHTML.replace(/New/g, 'Edit');
		}
		jQuery('#'+ prefix + '_item input[type=submit]').attr('value', 'Update');
		if (jQuery('.' + prefix + '_actions').size() != 0) {
			jQuery('.' + prefix + '_actions').css('display', 'block');
		}
	}
	
	// Print main message
	displayMessage(prefix, msg.length > 0 ? msg : result['result']['msg'], result['status']);
	
	Context.notify('end_response_process');
}

/**
 * Process responce to simple form
 * 
 * @param data
 * @param status
 * @param options
 * @return
 */
function processResponse(data, status, options)
{
	Context.setLastStatus(true);
	
	if (data.status == false)
	{
		Context.setLastStatus(false);
		alert(data['errors']['global']);
		return;
	}
	
	for(var kind in data)
	{
		for(var type in data[kind])
		{
			var result = data[kind][type];
			var msg = '';
			
			if (!result['status'])
			{
				Context.setLastStatus(false);
				
				for(var field in result['errors'])
				{
					if (!displayErrors(kind + '_' + type + '_' + field, result['errors'][field])) {
						msg += (msg.length > 0 ? ",&nbsp;" : "&nbsp;") + result['errors'][field];
					}
				}
				
				/*if (result['result'] && result['result']['msg']) {
					if (msg) {
						msg = result['result']['msg'] + msg;
					}
					else msg = result['result']['msg'];
				}*/
			}
			else Context.setFormChangedFlag(kind + '_' + type + '_item', false);
			
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
				var prefix = kind + '_' + type;
				if (jQuery('.' + prefix + '_actions').size() != 0) {
					jQuery('.' + prefix + '_actions').css('display', 'block');
				}
			}
			
			// Print main message
			displayMessage(kind + '_' + type,  msg.length > 0 ? msg : result['result']['msg'], result['status']);
		}
	}
	
	Context.notify('end_response_process');
}

/**
 * Process respons to object form
 * @param data
 * @param status
 * @return
 */
function processObjectResponse(data, status, options)
{
	Context.setLastStatus(true);
	
	if (data.status == false)
	{
		Context.setLastStatus(false);
		alert(data['errors']['global']);
		return;
	}
	
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
				Context.setLastStatus(false);
				
				for(var field in m_data['errors'])
				{
					if (!displayErrors(main_kind + '_' + main_type + '_' + field, m_data['errors'][field])) {
						msg += (msg.length > 0 ? ",&nbsp;" : "&nbsp;") + m_data['errors'][field];
					}
				}
				
				/*if (m_data['result'] && m_data['result']['msg']) {
					if (msg) {
						msg = m_data['result']['msg'] + msg;
					}
					else msg = m_data['result']['msg'];
				}*/
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
				var prefix = main_kind + '_' + main_type;
				if (jQuery('.' + prefix + '_actions').size() != 0) {
					generateActionsMenu('.' + prefix + '_actions', main_kind, main_type, m_data['result']['_id']);
					jQuery('.' + prefix + '_actions').css('display', 'block');
				    jQuery('#' + prefix + '_post_flag').css('display', '');
				}
			}
			
			// Print main message
			displayMessage(main_kind + '_' + main_type,  msg.length > 0 ? msg : m_data['result']['msg'], m_data['status']);
			
			if (m_data['status'] && state)
			{
				Context.setFormChangedFlag(main_kind + '_' + main_type + '_item', false);
			}
			
			Context.notify(main_kind + '_' + main_type + '_end_process');
		}
	}
	
	Context.notify('end_response_process');
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
	
	var msg = '';
	
	for (var type in data)
	{
		for (var i in data[type])
		{
			var m_data = data[type][i];
			
			if (m_data['status'] != true) // Print main errors
			{
				flag = false;
				
				for (var field in m_data['errors'])
				{
					if (!displayErrors(kind + '_' + type + '_' + i + '_' + field, m_data['errors'][field])) {
						msg += (msg.length > 0 ? "&nbsp;" : "&nbsp;") + m_data['errors'][field];
					}
				}
			}
			else if (m_data['result']['_id']) // Insert main ID
			{
				insertId(kind + '_' + type, m_data['result']['_id'], i);
			}
		}
	}
	
	if (!flag) Context.setLastStatus(false);
	
	/* Check close flag */
	
	if (flag && options.close) return flag;
	
	/* Print main message */
	
	if (!msg) {
		msg = flag ? 'Tabular section updated succesfully' : 'At updating Tabular section there were some errors';
	}
	
	displayMessage(kind + '_' + type,  msg, flag);
	
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
 * @param string uid    - entity uid
 * @param string prefix - prefix for id
 * @return int - row index
 */
function addTabularSectionItem(uid, prefix)
{
	ae_index[uid]++;
	var content = ae_template[uid];
	content = content.replace(/%%i%%/g, ae_index[uid]);
	content = content.replace(/%%script%%/g, 'script');
	jQuery('#' + prefix + '_edit_block').append(content);
	jQuery('#' + prefix + '_' + ae_index[uid] + '_item .oef_dynamic_update').each(function(index) {
		jQuery(this).change(function(event) {
    	    var dynamicUpdate = new oefDynamicUpdate();
    	    dynamicUpdate.processEvent(event);
    	});
    });
	
	return ae_index[uid];
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
	hideMessages();
	appInactive();
	appAddLoader();
	
	if (Context.getFormChangedFlag(prefix + '_item'))
	{
		var button = jQuery('#' + prefix + '_item *[command=save]').get(0);
		
		if (button)
		{
			processFormCommand(button);
			
			if (!Context.getLastStatus()) return false;
			
			Context.setFormChangedFlag(prefix + '_item', false);
			
			hideMessages();
			appInactive();
			appAddLoader();
		}
	}
	
	var result = executePost(kind, type, id, prefix);
	
	if (!result) Context.setLastStatus(false);
	
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
	hideMessages();
	appInactive();
	appAddLoader();
	
	var result = executeClearPosting(kind, type, id, prefix);

	appActive();
	
	return result;
}





/************************************* Functions ******************************************/

/**
 * Generate ActionsMenu
 * 
 * @param selector - jQuery selector (menu container)
 * @param kind     - entity kind
 * @param type     - entity type
 * @param itemID   - entity id
 * @return void
 */
function generateActionsMenu(selector, kind, type, itemID)
{
	var prefix = kind.replace(/\./g, '_') + '_' + type;
	
	var menu = '<a href="#" onclick="javascript:post(\'' + kind + '\', \'' + type + '\', ' + itemID + ', \'' + prefix + '\'); return false;">Post</a>';
    menu += '&nbsp;|&nbsp;<a href="#" onclick="javascript:clearPosting(\'' + kind + '\', \'' + type + '\', ' + itemID + ', \'' + prefix + '\'); return false;">Clear posting</a>';
    
    jQuery(selector).html(menu);
}

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
	var ret  = true;
	var id   = parseInt(id, 10);
	var pref = kind.replace(/\./g, '_') + '_' + type;
	
	if (!id) {
		displayMessage(pref, 'Unknow element id', false);
		return false;
	}
	
	jQuery.ajax({
	    url: '/Special:OEController',
	    async: false,
	    type: 'POST',
	    data: ({aeform: {kind: kind, type: type, _id : id}, action: 'post', page_path: OEF_PAGE_PATH}),
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
				displayMessage(pref, msg, false);
			}
			else {
				var element = jQuery('#' + prefix + '_post_flag .ae_field_not_posted');
				jQuery(element).removeClass('ae_field_not_posted');
				jQuery(element).addClass('ae_field_posted');
				jQuery(element).find('span.ae_field_posted_text').css('display', 'block');
				jQuery(element).find('span.ae_field_not_posted_text').css('display', 'none');
				displayMessage(pref, 'Post succesfully', true);
				disabledForm('#' + pref + '_item');
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
	var ret  = true;
	var id   = parseInt(id, 10);
	var pref = kind.replace(/\./g, '_') + '_' + type;
	
	if (!id) {
		displayMessage(pref, 'Unknow element id', false);
		return false;
	}
	
	jQuery.ajax({
	    url: '/Special:OEController',
	    async: false,
	    type: 'POST',
	    data: ({aeform: {kind: kind, type: type, _id : id}, action: 'unpost', page_path: OEF_PAGE_PATH}),
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
				displayMessage(pref, msg, false);
			}
			else {
				var element = jQuery('#' + prefix + '_post_flag .ae_field_posted');
				jQuery(element).removeClass('ae_field_posted');
				jQuery(element).addClass('ae_field_not_posted');
				jQuery(element).find('span.ae_field_posted_text').css('display', 'none');
				jQuery(element).find('span.ae_field_not_posted_text').css('display', 'block');
				displayMessage(pref, 'Clear posting succesfully', true);
				enabledForm('#' + pref + '_item');
			}
	    }
	});
	
	return ret;
}






/**
 * Insert id tag after entity creation
 * 
 * @param string  uid - entity uid
 * @param integer id  - entity id
 * @param integer i   - item index (optional)
 * @return void
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
    
    jQuery('#' + tag_id).find('input[name="'+ ae_name_prefix[uid] +'[ids][]"]').attr('value', id);
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



/************************************ For form module ****************************************/

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
	    data: ({action: 'generateForm', uid: uid, name: form, parameters: params, page_path: OEF_PAGE_PATH}),
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

/**
 * Notify about form event
 * 
 * @param string uid       - entity uid
 * @param string formName  - form name
 * @param string eventName - event name
 * @param mixed  params    - json object (contents list of optional parameters)
 * @return boolean
 */
function notifyFormEvent(uid, formName, eventName, params)
{
	var dispatcher = new oeEventDispatcher();
	
	hideMessages();
	appInactive();
	appAddLoader();
	
	var status = dispatcher.notify(uid, formName, eventName, params);
	
	appActive();
	
	return status;
}

/**
 * Constructor object oeEventDispatcher
 */
function oeEventDispatcher()
{
	this.event  = {};
	this.prefix = ae_name_prefix;
	this.index  = {};
	
	/**
	 * Notify about form event
	 * 
	 * @param string uid       - entity uid
	 * @param string formName  - form name
	 * @param string eventName - event name
	 * @param mixed  params    - json object (contents list of optional parameters)
	 * @return boolean
	 */
	this.notify = function(uid, formName, eventName, params)
	{
		this.event.uid    = uid;
		this.event.name   = eventName;
		this.event.params = params;
		
		var retval   = false;
		var callback = this.getCallback(eventName);
		
		if (callback === null)
		{
			displayMessage(uid.replace(/\./g, '_'), "Unknow form event " + eventName, false);
			return false;
		}
		
		var formData = jQuery('#' + uid.replace(/\./g, '_') + '_item').formSerialize();
		
		jQuery.ajax({
		    url: '/Special:OEController',
		    async: false,
		    type: 'POST',
		    data: ({
		    	action: 'notifyFormEvent',
		    	uid:   uid,
		    	event: eventName,
		    	formName: formName,
		    	formData: formData,
		    	parameters: params,
		    	page_path:  OEF_PAGE_PATH
		    }),
		    dataType: 'json',
		    success: function (data, status)
		    {
				if (!data['status'])
				{
					var msg = '';
					for (var index in data['errors'])
					{
						msg += (index > 0 ? ",&nbsp;" : "&nbsp;") + data['errors'][index];
					}
					displayMessage(uid.replace(/\./g, '_'), msg, false);
				}
				else
				{
					retval = callback.call(new oeEventDispatcher(), uid, data['result']);
				}
		    },
		    error: function (XMLHttpRequest, textStatus, errorThrown)
		    {
				alert('Request error');
				;
		    }
		});
		
		return retval;
	};
	
	/**
	 * Get callback function by event name
	 * 
	 * @param string eventName
	 * @return function
	 */
	this.getCallback = function(eventName)
	{
		switch(eventName)
		{
			case 'onFormUpdateRequest':
				return this.processOnFormUpdateRequestResponse;
				break;
				
			default:
				return null;
		}
	};
    
	/**
	 * Process response to event 'onFormUpdateRequest'
	 * 
	 * data = array(
	 *    <kind> => array(
     *       <type> => array(
     *          'attributes' => array(
     *             <attr_1_name> => value,
     *             ......................,
     *             <attr_N_name> => value
     *          ),
     *          'errors' => array(
     *             <attr_1_name> => array('msg_1', .., 'msg_N'),
     *             ............................................
     *          ),
     *          'tabulars' => array(
     *             <type> => array(
     *                'items' => array(
     *                   <index> => array(
     *                      <t_attr_1_name> => value,
     *                      ........................,
     *                      <t_attr_N_name> => value
     *                   ),
     *                   .........................
     *                ),
     *                'errors' => array(
     *                   <index> => array(
     *                      <t_attr_1_name> => array('msg_1', .., 'msg_N'),
     *                      ..............................................
     *                   ),
     *                   .........................
     *                )
     *             ),
     *             ............................
     *          )
     *       ),
     *       ...............................
     *    ),
     *    ...............................
     * );
     *    
	 * @param string uid - uid entity that generated the event
	 * @param array data - response data
	 * @return boolean
	 */
	this.processOnFormUpdateRequestResponse = function(uid, data)
	{
		// Check data
		if (data['type'] != 'array')
		{
			alert('Not supported response type');
			return false;
		}
		if (!data['data']) return false;
		
		// Prepare update
		var msg = new Array(), i = 0;
		
		for (var kind in data['data'])
		{
			var cdata = data['data'][kind];
			
			for (var type in cdata)
			{
				if (cdata[type]['attributes'])
				{
					for (var attr in cdata[type]['attributes'])
					{
						msg[i++] = '\n   - ' + kind + ' ' + type + ' ' + attr + ' attribute';
					}
				}
				
				if (cdata[type]['tabulars'])
				{
					for (var ttype in cdata[type]['tabulars'])
					{
						if (cdata[type]['tabulars'][ttype]['items'])
						{
							msg[i++] = '\n   - ' + kind + ' ' + type + ' tabular ' + ttype;
						}
						else if (cdata[type]['tabulars'][ttype]['select'])
						{
							cdata[type]['tabulars'][ttype]['items'] = {};
							
							msg[i++] = '\n   - ' + kind + ' ' + type + ' tabular ' + ttype;
						}
					}
				}
			}
		}
		
		// Update edit form
		if (msg.length > 0)
		{
			appDisplayLoader(false);
			
			if (confirm('Will be changed:\n' + msg.join(';\n') + '.\n\nChange?'))
			{
				appDisplayLoader(true);
				
				for (var kind in data['data'])
				{
					var cdata = data['data'][kind];
					var res   = true;
					
					for (var type in cdata)
					{
						// Update entity attributes
						if (cdata[type]['attributes'])
						{
							res = this.updateAttributes(kind, type, cdata[type]['attributes']);
						}
						
						// Display entity attributes errors
						if (res && cdata[type]['errors'])
						{
							this.displayAttributesErrors(kind, type, cdata[type]['errors']);
						}
						
						// Process tabular sections data
						if (cdata[type]['tabulars'])
						{
							for (var ttype in cdata[type]['tabulars'])
							{
								res = true;
								
								// Update selectboxes
								if (cdata[type]['tabulars'][ttype]['select'])
								{
									this.updateTabularSelectBoxes(kind + '_' + type + '_tabulars', ttype, cdata[type]['tabulars'][ttype]['select']);
								}
								
								// Update entity tabular section attributes
								if (cdata[type]['tabulars'][ttype]['items'])
								{
									res = this.updateTabular(kind + '_' + type + '_tabulars', ttype, cdata[type]['tabulars'][ttype]['items']);
								}
								
								// Display entity tabular section attributes errors
								if (res && cdata[type]['tabulars'][ttype]['errors'])
								{
									this.displayTabularErrors(kind + '_' + type + '_tabulars', ttype, cdata[type]['tabulars'][ttype]['errors']);
								}
							}
						}
					}
				}
				
				// Display message
				if (data['msg'])
				{
					displayMessage(uid.replace(/\./g, '_'), data['msg'], true);
				}
				
				// Mark selected
				markSelected();
			}
			else
			{
				return false;
			}
		}
		
		return true;
	};
	
	/**
	 * Update attributes
	 * 
	 * @param string kind - entity kind
	 * @param string type - entity type
	 * @param array attributes - values for entity attributes
	 * @return boolean
	 */
	this.updateAttributes = function(kind, type, attributes)
	{
		if (!this.prefix[kind + '_' + type]) return false;
		
		var prefix = this.prefix[kind + '_' + type];
		
		for (var attr in attributes)
		{
			var value   = attributes[attr];
			var element = jQuery('*[name="' + prefix + '[' + attr + ']' + '"]').get(0);
			
			if (typeof element != 'object') continue;
			
			this.setElementValue(element, value);
		}
		
		return true;
	};
	
	/**
	 * Update select boxes in tabular section
	 * 
	 * @param string kind - tabular kind
	 * @param string type - tabular type 
	 * @param array vals  - list of new options 
	 * @return boolean
	 */
	this.updateTabularSelectBoxes = function(kind, type, vals)
	{
		var prefix  = kind + '_' + type;
		
		if (!ae_template[prefix]) return false;
		
		for (var attribute in vals)
		{
			var options = '<option value="0">&nbsp;</option>';
			
			for (var i in vals[attribute])
			{
				var text  = vals[attribute][i]['text'];
				var value = vals[attribute][i]['value'];
				if (value == 'new')
				{
					options += '<option value="new" class="oef_edit_form_field_add_new">add new</option>';
				}
				else options += '<option value="'+ value +'">'+ text +'</option>';
			}
			
			var code = 'ae_template[prefix].match(/id="'+ prefix +'_%%i%%_'+ attribute +'_field[^<]*/g);';
			var begs = eval(code);
			
			if (begs != null)
			{
				begs.sort();
				
				var previous = ''; 
				
				for (var i in begs)
				{
					if (previous == begs[i]) continue;
					
					patt = begs[i];
					patt = patt.replace(/\//g, '\\/');
					patt = patt.replace(/\[/g, '\\[');
					patt = patt.replace(/\]/g, '\\]');
					
					eval('ae_template[prefix] = ae_template[prefix].replace(/'+ patt +'([^<]*)(.*)(?=<\\/select)/g, begs[i] + options);');
					
					previous = begs[i];
				}
			}
		}
		
		return true;
	};
	
	/**
	 * Update tabular section
	 * 
	 * @param string kind - tabular kind
	 * @param string type - tabular type 
	 * @param array items - tabular rows
	 * @return boolean
	 */
	this.updateTabular = function(kind, type, items)
	{
		if (!this.prefix[kind + '_' + type]) return false;
		
		var uid    = kind + '_' + type;
		var prefix = this.prefix[uid];
		
		this.index[type] = {};
		           
		var edit_block = document.getElementById(uid + '_edit_block');
		
		if (!edit_block) return false;
		
		edit_block.innerHTML = '';
		
		for (var i in items)
		{
			// Add tabular item
			var index = addTabularSectionItem(uid, uid);
			
			// Update item attributes
			for (var attr in items[i])
			{
				var value = items[i][attr];
				
				if (attr == '_id')
				{
					insertId(uid, value, index);
					continue;
				}
				
				var element = jQuery('*[name="' + prefix + '[' + index + '][' + attr + ']' + '"]').get(0);
				
				if (typeof element != 'object') continue;
				
				this.setElementValue(element, value);
			}
			
			this.index[type][i] = index;
		}
		
		return true;
	};
	
	/**
	 * Set value for input, select, textarea
	 *  
	 * @param element
	 * @param value
	 * @return
	 */
	this.setElementValue = function(element, value)
	{
		switch(element.nodeName)
		{
			case 'INPUT':
				element.value = value;
				break;
				
			case 'SELECT':
				jQuery(element).find('*[current="true"]').removeAttr('current');
				
				for (i = 0; i < element.length; i++)
				{
					var option = element.options[i];
					
					if (option.value == value)
					{
						option.setAttribute('current', 'true'); // For Dynamic Update
						element.selectedIndex = i;
						break;
					}
				}
				break;
				
			case 'TEXTAREA':
				element.innerHTML = value;
				break;
				
			default:
				element.value = value;
		}
	};
	
	/**
	 * Display entity attributes errors
	 * 
	 * @param string kind  - entity kind
	 * @param string type  - entity type
	 * @param array errors - error messages
	 * @return
	 */
	this.displayAttributesErrors = function(kind, type, errors)
	{
		var ret = true;
		
		for (var attribute in errors)
		{
			if (!displayErrors(kind + '_' + type + '_' + attribute, errors[attribute]))
			{
				ret = false;
			}
		}
		
		return ret;
	};
	
	/**
	 * Display entity tabular section attributes errors
	 * 
	 * @param string kind    - tabular kind
	 * @param string type    - tabular type
	 * @param array errors   - error messages
	 * @return
	 */
	this.displayTabularErrors = function(kind, type, errors)
	{
		var ret   = true;
		var index = null;
		
		for (var i in errors)
		{
			if (this.index[type][i])
			{
				index = this.index[type][i];
			}
			else index = i;
			
			for (var attribute in errors[i])
			{
				if (!displayErrors(kind + '_' + type + '_' + index + '_' + attribute, errors[i][attribute]))
				{
					ret = false;
				}
			}
		}
		
		return ret;
	};
}



/************************************ Dynamic update ****************************************/

function oefDynamicUpdate()
{
	var tagID = 'oef_dynamic_upadate_edit_form';
	var node  = {};
	var kind;
	var type;
	var active = false;
	
	var submitOptions = {
		options: {},
		async: false,
		url: '/Special:OEController',
		dataType:  'json',
		beforeSubmit: prepareRequest,
		success: function (data, status) { processDynamicUpdateResponse(data, status, this.options); },
		data: {action: 'save', form: 'ObjectForm', page_path: OEF_PAGE_PATH}
    };
	
	/**
	 * Process event
	 * 
	 * @param object event
	 * @return boolean
	 */
	this.processEvent = function(event)
	{
		event = event || window.event;
		node  = event.target || event.srcElement;
		
	    if (node.nodeName != 'SELECT') return false;
	    
	    var i = node.selectedIndex;
	    
		if (node.options[i].value != 'new')
		{
			jQuery(node).find('*[current="true"]').removeAttr('current');
			
			node.options[i].setAttribute('current', 'true');
			
			return true;
		}
		
		event.stopPropagation ? event.stopPropagation() : (event.cancelBubble = true);
		
		kind = jQuery(node).attr('rkind');
		type = jQuery(node).attr('rtype');
		
		hideMessages();
		appInactive();
	    appDisplayLoader(true);
	    
		this.displayEditForm(kind, type);
		
		appDisplayLoader(false);
	};
	
	/**
	 * Display edit form
	 * 
	 * @param string kind - entity kind
	 * @param string type - entity type
	 * @return boolean
	 */
	this.displayEditForm = function(kind, type)
	{
		var query = 'uid=' + kind + '.' + type + '&actions=displayEditForm';
		
		var listener = this.Listener;
		
		var x = jQuery.ajax({
			url: pageAPI + '/contents?dream.out.format=xml' + (query.length == 0 ? '' : '&' + query),
		    async: false,
			type: 'GET',
			cache: false,
			dataType: 'xml',
			success: function (data, status)
			{
				var body = jQuery(data).find('body')[0];
				body = jQuery(body).text();
				
				if (!document.getElementById(tagID))
				{
					jQuery('body').append('<div id="'+ tagID +'"></div>');
				}
				
				jQuery('#' + tagID).html(body);
				jQuery('#' + tagID + ' .ae_command[command=save]').remove();
				jQuery('#' + tagID + ' .ae_command').each(function(index) {
			    	jQuery(this).click(function() { 
			    		listener(this);
			    	});
			    });
			},
		    error: function (XMLHttpRequest, textStatus, errorThrown)
		    {
				appActive();
				alert(textStatus);
		    }
		});
	};
	
	/**
	 * Listener to edit form
	 * 
	 * @param object element
	 * @return
	 */
	this.Listener = function(element)
    {
    	var method;
    	var options = submitOptions;
    	var form    = jQuery(element).parents('form');
    	var command = jQuery(element).attr('command');
    	
    	if (jQuery(form).hasClass('ae_object_edit_form')) {
    		method = 'submitObjectForm';
    	}
    	else if (jQuery(form).hasClass('oe_custom_edit_form')) {
    		method = 'submitForm';
    		options.data.form = 'CustomForm';
    	}
    	else {
    		method = 'submitForm';
    	}
    	
    	switch(command)
    	{
    		case 'save_and_close':
    			options.options.close = true;
    		break;
    		
    		default: /*case 'cancel':*/
    			removeEditForm();
    			
    			if (jQuery(node).find('option[current="true"]').size() != 0)
    			{
    				jQuery(node).find('option[current="true"]').attr('selected', true);
    			}
    			else
    			{
    				jQuery(node).find('option[value="0"]').attr('selected', true);
    			}
    			
    			appActive();
				
    			return;
    	}
    	
    	try {
    		eval(method + '(form, options)');
    	}
    	catch(e) { ; }
    };
    
    /**
	 * Process dynamic update response
	 * 
	 * @param object data
	 * @param string status
	 * @param object options
	 * @return
	 */
	function processDynamicUpdateResponse(data, status, options)
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
					var id = m_data['result']['_id'];
					
					if (main_kind == 'catalogs')
					{
						var text = jQuery('#' + tagID + ' input[name="'+ ae_name_prefix[main_kind + '_' + main_type] + '[Description]"]').attr('value');
					}
					else
					{
						var text = main_type + ' ' + jQuery('#' + tagID + ' input[name="'+ ae_name_prefix[main_kind + '_' + main_type] + '[Date]"]').attr('value');
					}
					
					removeEditForm();
					
					updateListOfOption(text, id);
					
					active = true;
					
					return true;
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
	 * Remove edit form
	 * 
	 * @return void
	 */
	function removeEditForm()
	{
		jQuery('#' + tagID).remove();
	}
	
	/**
	 * Update all list with current kind and type
	 * 
	 * @param string text  - text for OPTION
	 * @param string value - value for OPTION
	 * @return void
	 */
	function updateListOfOption(text, value)
	{
		var append = '<option value="' + value + '">' +  text + '</option>';
		
		if (node.getAttribute('name').match(/\[tabulars\]/g) != null)
		{
			var prefix = node.getAttribute('id').match(/.*(?=_[0-9]+_[\S]+_field)/g);
			
			if (prefix[0])
			{
				prefix = prefix[0];
				ae_template[prefix] = ae_template[prefix].replace(/<\/select>/g, append + '</select>');
			}
		}
		
		jQuery('select.oef_' + kind + '_' + type).append(append);
		jQuery(node).find('option[value="' + value + '"]').attr('selected', true);
		jQuery(node).change();
	}
}



/**
 * 
 * @return
 */
function oefContext()
{
	this.lastStatus  = true;
	this.formChanged = {};
	this.listeners   = {};
	
	this.getLastStatus = function()
	{
		return this.lastStatus;
	};
	
	this.setLastStatus = function(status)
	{
		this.lastStatus = status;
	};
	
	this.setFormChangedFlag = function(form_id, value)
	{
		this.formChanged[form_id] = value;
	};
	
	this.getFormChangedFlag = function(form_id)
	{
		if (this.formChanged[form_id]) return true;
		
		return false;
	};
	
	this.addListener = function(event_name, callback)
	{
		if (!this.listeners[event_name])
		{
			this.listeners[event_name] = new Array();
		}
		
		for (var i in this.listeners[event_name])
		{
			if (this.listeners[event_name][i].toString() == callback.toString())
			{
				return;
			}
		}
		
		this.listeners[event_name].push(callback);
	};
	
	this.notify = function(event_name, params)
	{
		if (!this.listeners[event_name]) return;
		if (!params) params = {};
		
		for (var i in this.listeners[event_name])
		{
			var callback = this.listeners[event_name][i];
			callback(params);
		}
	};
}