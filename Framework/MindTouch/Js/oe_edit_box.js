
/************************************* OnLoad ******************************************/

jQuery(document).ready(function() {
	jQuery('.oef_edit_box_button').each(function(index) {
		
		jQuery(this).mousedown(function(){
			jQuery(this).css('background', 'url("/skins/common/icons/edit_box_pressed_button.png") no-repeat scroll 0px 0px transparent');
		});
		
		jQuery(this).mouseup(function(event) {
			jQuery(this).css('background', '');
			
			event = event || window.event;
			node  = event.target || event.srcElement;
			node  = jQuery(node).parents('.oef_edit_box_button').get(0);
			
			var editBox = new oefEditBox();
			
			switch (node.getAttribute('command'))
			{
				case 'select':
					editBox.onShowSelectBox(event);
				break;
				
				case 'clear':
					editBox.onClear(event);
				break;
				
				default:
					return;
			}
		});
		
		jQuery(this).mouseout(function(){
			jQuery(this).css('background', '');
		});
	});
	
	jQuery('.oef_edit_box_item').each(function(index) {
		jQuery(this).click(function(event){
			var editBox = new oefEditBox();
			editBox.onItemSelected(event);
		});
	});
});


/****************************** OEF Edit Box for Owner *********************************/

function oefEditBox ()
{
	var container  = null;
	
	/**
	 * Process event ShowSelectBox
	 *  
	 * @param DOMEvents event
	 * @return boolean
	 */
	this.onShowSelectBox = function(event)
	{
		event = event || window.event;
		node  = event.target || event.srcElement;
		
	    event.stopPropagation ? event.stopPropagation() : (event.cancelBubble = true);
		
	    var cont = retrieveContainer(node);
	    
	    if (!cont) return false;
	    
	    var disp = jQuery(cont).find('.oef_owners_container').css('display') == 'none';
	    
	    this.showSelectBox(disp);
	    		
	    return true;
	};
	
	/**
	 * Process event ItemSelected
	 * 
	 * @param DOMEvents event
	 * @return boolean
	 */
	this.onItemSelected = function(event)
	{
		event = event || window.event;
		node  = event.target || event.srcElement;
		
	    event.stopPropagation ? event.stopPropagation() : (event.cancelBubble = true);
		
		var uid   = jQuery(node).attr('uid');
		var id    = parseInt(jQuery(node).attr('id'), 10);
		var otype = jQuery(node).attr('otype');
		var cont  = retrieveContainer(node);
		
		this.showSelectBox(false);
		
		this.displaySelectForm(uid, id, otype);
		
		return true;
	};
	
	/**
	 * Process event Clear
	 * 
	 * @param DOMEvents event
	 * @return boolean
	 */
	this.onClear = function(event)
	{
		event = event || window.event;
		node  = event.target || event.srcElement;
		
	    event.stopPropagation ? event.stopPropagation() : (event.cancelBubble = true);
	    
	    var cont = retrieveContainer(node);
	    
	    updateEditBox(cont, '', 0, '');
	    
	    return true;
	};
	
	/**
	 * Retrieve root DOMNode for current edit box
	 *  
	 * @param DOMElements node
	 * @return DOMElements
	 */
	function retrieveContainer(node)
	{
		container = jQuery(node).parents('.oef_edit_box_container').get(0);
		
		return container;
	}
	
	/**
	 * Show or hide select list for choice OwnerType
	 * 
	 * @param boolean display - if true - show
	 * @return void
	 */
	this.showSelectBox = function(display)
	{
		jQuery(container).find('.oef_owners_container').css('display', (display ? 'table' : 'none'));
	};
	
	/**
	 * Show SelectForm for choice entity
	 * 
	 * @param string uid   - current entity uid
	 * @param int id       - current entity id
	 * @param string otype - selected owner type
	 * @return void
	 */
	this.displaySelectForm = function(uid, id, otype)
	{
		if (!uid || !otype) return;
		
		var callback = this.generateSelectForm;
		var params   = {
			uid:   uid,
			pkey:  id,
			otype: otype,
			fields: {OwnerType: true}
		};
		
		this.loadEntitiesList(params, callback);
	};
	
	/**
	 * Generate and dysplay SelectForm
	 * 
	 * @param object params - parameters
	 * @param object data   - form data
	 * @return boolean
	 */
	this.generateSelectForm = function(params, data)
	{
		// Check data
		if (!params['uid'] || !params['otype'])
		{
			alert('Load error');
			
			return false;
		}
		
		var prefix = params.uid.replace('.', '_');
		
		if (!data['status'])
		{
			var msg = '';
			
			for (var field in data['errors'])
			{
				if (!displayErrors(prefix + '_' + field, data['errors'][field]))
				{
					msg += (msg.length > 0 ? ",&nbsp;" : "&nbsp;") + data['errors'][field];
				}
			}
			
			if (msg) displayMessage(prefix, msg, false);
			
			return false;
		}
		
		if (!data['result'] || !data['result']['OwnerType'])
		{
			displayMessage(prefix, 'Can\'t load data', false);
			
			return false;
		}
		
		data = data['result']['OwnerType'];
		
		// Generate and append form
		var form = '<div class="oef_edit_box_select_form">';
		form += '<select name="oef_edit_box_select_form_value" size="10">';
		
		for (var index in data)
		{
			form += '<option value="' + data[index]['value'] + '">' + data[index]['text'] + '</option>';
		}
		
		form += '</select><div class="oef_edit_box_select_form_actions">';
		form += '<input type="button" name="Select" value="Select">&nbsp;';
		form += '<input type="button" name="Cancel" value="Cancel"></div></div>';
		
		appDisplayLoader(false);
		
		jQuery(container).append(form);
		
		// Select action
		jQuery(container).find('.oef_edit_box_select_form_actions input[name="Select"]').click(function(event){
			var node = jQuery(container).find('*[name="oef_edit_box_select_form_value"]').get(0);
			
			switch (node.nodeName)
			{
				case 'SELECT':
					if (node.selectedIndex < 0) break;
					
					var otype = params['otype'];
					var oid   = node.options[node.selectedIndex].value;
					var text  = node.options[node.selectedIndex].text;
					
					updateEditBox(container, otype, oid, text);
				break;
				
				default:
					displayMessage(prefix, 'Can\'t get selected', false);
			}
			
			jQuery(container).find('.oef_edit_box_select_form').remove();
			
			endLoad();
		});
		
		// Cancel action
		jQuery(container).find('.oef_edit_box_select_form_actions input[name="Cancel"]').click(function(event){
			jQuery(container).find('.oef_edit_box_select_form').remove();
			
			endLoad();
		});
		
		return true;
	};
	
	/**
	 * Load data for SelectForm
	 * 
	 * params {
	 *    string uid    - current entity uid
	 *    int    id     - current entity id
	 *    string otype  - selected owner type
	 *    object fields - return data only this fields
	 * }
	 * 
	 * @param object params   - parameters
	 * @param func   callback - link to function that process loaded data
	 * @return void
	 */
	this.loadEntitiesList = function(params, callback)
	{
		startLoad();
		
		jQuery.ajax({
    		url: '/Special:OEController',
    	    async: true,
    		type: 'POST',
    		data: ({
    			action:     'getSelectData',
    			page_path:  OEF_PAGE_PATH,
    			parameters: params
    		}),
    		cache: false,
    		dataType: 'json',
    		reqTimeout: null,
    		beforeSend: function (xmlhttp)
    		{
				this.reqTimeout = setTimeout(function () { xmlhttp.abort(); abort(); }, 30000);
    		},
    		success: function (data, status)
    		{
    		    clearTimeout(this.reqTimeout);
    		    
    		    if (!data)
    		    {
    		    	requestError();
    		    }
    		    else
    		    {
    		    	if (!callback(params, data))
    		    	{
    		    		endLoad();
    		    	}
    		    }
    		},
    	    error: function (XMLHttpRequest, textStatus, errorThrown)
    	    {
    			clearTimeout(this.reqTimeout);
    			
    			requestError();
    	    }
    	});
	};
	
	/**
	 * Called before request
	 * 
	 * @return void
	 */
	function startLoad()
	{
		hideMessages();
		appInactive();
	    appDisplayLoader(true);
	}
	
	/**
	 * Called after process responce (if request is success)
	 * 
	 * @return void
	 */
	function endLoad()
	{
		appActive();
	}
	
	/**
	 * Called if request aborted
	 * 
	 * @return void
	 */
	function abort()
	{
		endLoad();
		
		alert('Timeout have been exceeded');
	}
	
	/**
	 * Called
	 * 
	 * @return void
	 */
	function requestError()
	{
		endLoad();
		
		alert('Request error');
	}
	
	/**
	 * Update edit box
	 * 
	 * @param DOMElements container
	 * @param string otype
	 * @param string oid
	 * @param string text
	 * @return boolean
	 */
	function updateEditBox(container, otype, oid, text)
	{
		jQuery(container).find('.oef_edit_box_otype').attr('value', otype).end().
			find('.oef_edit_box_oid').attr('value', oid).end().
			find('.oef_edit_box_text').text(text);
		
		return true;
	}
}
