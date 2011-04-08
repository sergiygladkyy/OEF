/************************************* Events *******************************************/

self.childClose = function (data)
{
	if (data.message && data.prefix && data.type)
	{
		displayMessage(data.prefix, data.message, data.type);
	}
};

/************************************* Actions ******************************************/

/**
 * Action new Item
 * 
 * @param DOMElements element
 * @param string kind - entity kind
 * @param string type - entity type
 * @param object options
 * 
 * @return boolean
 */
function newListItem(element, kind, type, options)
{
	var param  = options && options.type ? {type: options.type} : {};
	var popup  = new oefPopup(kind, type, param);
	var target = element.getAttribute('target');
    
    if (target) popup.setTarget(target);
	
	return popup.displayWindow('displayEditForm', {id: 0});
}

/**
 * Action edit Item
 * 
 * @param DOMElements element
 * @param string kind - entity kind
 * @param string type - entity type
 * 
 * @return boolean
 */
function editListItem(element, kind, type)
{
	var pref = getPrefix(kind, type);
	var id   = getItemId(pref);
	
	if (!id) {
		alert('Choose an list item');
		return false;
	}
	
	var popup  = new oefPopup(kind, type);
	var target = element.getAttribute('target');
    
    if (target) popup.setTarget(target);
    
	return popup.displayWindow('displayEditForm', {id: id});
}

/**
 * Action view Item
 * 
 * @param DOMElements element
 * @param string kind - entity kind
 * @param string type - entity type
 * 
 * @return boolean
 */
function viewListItem(element, kind, type)
{
	var pref = getPrefix(kind, type);
	var id   = getItemId(pref);
	
	if (!id) {
		alert('Choose an list item');
		return false;
	}
	
	var popup  = new oefPopup(kind, type);
	var target = element.getAttribute('target');
    
    if (target) popup.setTarget(target);

	return popup.displayWindow('displayItemForm', {id: id});
}

/**
 * Action delete Item (only for not object types)
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return boolean
 */
function deleteListItem(kind, type, prefix)
{
	var result = true;
	
	appInactive();
	
	if (confirm('Are you sure?'))
	{
		appAddLoader();
			
		result = executeDeleteListItem(kind, type, prefix);
	}
	else jQuery('.' + kind.replace(/\./g, '_') + '_' + type + '_message').css('display', 'none');
	
	appActive();
	
	return result;
}

/**
 * Action mark for deletion Item (only for object types)
 * 
 * @param kind
 * @param type
 * @param prefix
 * @param show_deleted
 * @return boolean
 */
function markForDeletionListItem(kind, type, prefix, show_deleted)
{
	var result = true;
	
	appInactive();
	
	if (confirm('Are you sure?'))
	{
		appAddLoader();
			
		result = executeMarkForDeletionListItem(kind, type, prefix, show_deleted);
	}
	else jQuery('.' + kind.replace(/\./g, '_') + '_' + type + '_message').css('display', 'none');
	
	appActive();
	
	return result;
}

/**
 * Action unmark for deletion Item
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return boolean
 */
function unmarkForDeletionListItem(kind, type, prefix)
{
	appInactive();
	appAddLoader();
	
	var result = executeUnmarkForDeletionListItem(kind, type, prefix);

	appActive();
	
	return result;
}

/**
 * Action post Item
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return
 */
function postListItem(kind, type, prefix)
{
	appInactive();
	appAddLoader();
	
	var result = executePostListItem(kind, type, prefix);

	appActive();
	
	return result;
}

/**
 * Action Clear posting Item
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return
 */
function clearPostingListItem(kind, type, prefix)
{
	appInactive();
	appAddLoader();
	
	var result = executeClearPostingListItem(kind, type, prefix);

	appActive();
	
	return result;
}

/**
 * Update ListForm
 * 
 * @param pageAPI - deki api to MT page
 * @param tagid   - parent to page content
 * @return void
 */
function updateListForm(pageAPI, tagid, maxRequestTime)
{
	if (!tagid) tagid = 'pageText';
	if (!maxRequestTime) maxRequestTime = 1000;

	var query_string = window.location.search.substring(1);
	
	var x = jQuery.ajax({
		url: pageAPI + '/contents?dream.out.format=xml' + (query_string.length == 0 ? '' : '&' + query_string),
	    async: true,
		type: 'GET',
		cache: false,
		dataType: 'xml',
		reqTimeout: null,
		beforeSend: function (xmlhttp)
		{
			this.reqTimeout = setTimeout(function () { xmlhttp.abort(); }, maxRequestTime);
		},
		success: function (data , status)
		{
		    /* Cancel XMLHttpRequest.abort() */
			
			clearTimeout(this.reqTimeout);
			
			/* Update page */
			
		    var body = jQuery(data).find('body')[0];
			body = jQuery(body).text();
			if (jQuery(body).find('span.warning')[0]) return;
			
			var id = parseInt(jQuery('.ae_list_block .ae_current').find('.ae_item_id').text(), 10);
			
			jQuery('#' + tagid + ' .ae_listform').replaceWith(jQuery(body).find('.ae_listform'));
			if (id) {
				jQuery('#' + tagid + ' .ae_item_id').each(function () {
					if (parseInt(this.innerHTML, 10) == id)	{
						jQuery(this).parents('.ae_list_item').addClass('ae_current');
						return;
					}
				});
			}
		}/*,
	    error: function (XMLHttpRequest, textStatus, errorThrown)
	    {
			alert(textStatus);
	    }*/
	});
}

/**
 * Create new entity by current
 * 
 * @param DOMElements element - action link
 * @param string kind         - current entity kind
 * @param string type         - current entity type
 * @param object basis_for    - basis_for configuration
 * @return void
 */
function newOnBasis(element, kind, type, basis_for)
{
	var basis = new oefNewOnBasis(kind, type, basis_for);
	
	return basis.updateMenu(element);
}

/**
 * Action print Item
 * 
 * @param element
 * @param kind
 * @param type
 * @param layout
 * @return
 */
function printListItem(element, kind, type, layout)
{
	var prefix = kind.replace(/\./g, '_') + '_' + type;
	var id     = getItemId(prefix);
	
	if (!id) {
		alert('Choose an list item');
		return false;
	}
	
	return onPrint(element, kind, type, id, layout);
}




/************************************* Functions ******************************************/

/**
 * Delete Item
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return boolean
 */
function executeDeleteListItem(kind, type, prefix)
{
	if (kind == 'catalogs' || kind == 'documents'){
		displayMessage(kind + '_' + type, 'Unsupported operation', false);
		return false;
	}
	
	var ret = true;
	var id  = getItemId(prefix);
	
	if (!id) {
		displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Choose an list item', false);
		return false;
	}
	
	jQuery.ajax({
		url: '/Special:OEController',
	    async: false,
		type: 'POST',
		data: ({aeform: {kind: kind, type: type, _id : id}, action: 'delete', page_path: OEF_PAGE_PATH}),
		dataType: 'json',
		success: function (data , status)
		{
			ret = data['status'];	
			
			var msg = '';
			
			if(!data['status'])
			{
				for(var index in data['errors'])
				{
					msg += data['errors'][index]+"\n";
				}
			}
			
			if (!msg)
			{
				msg = (data['result'] && data['result']['msg']) ? data['result']['msg'] : (data['status'] ? 'Deleted successfully' : 'Not deleted');
			}
			
			displayMessage(kind.replace(/\./g, '_') + '_' + type, msg, data['status']);
		}
	});
	
	return ret;
}

/**
 * Mark For Deletion
 * 
 * @param kind
 * @param type
 * @param prefix
 * @param show_deleted
 * @return boolean
 */
function executeMarkForDeletionListItem(kind, type, prefix, show_deleted)
{
	if (kind != 'catalogs' && kind != 'documents'){
		displayMessage(kind + '_' + type, 'Unsupported operation', false);
		return false;
	}
	
	var ret = true;
	var id  = getItemId(prefix);
	
	if (!id) {
		displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Choose an list item', false);
		return false;
	}
	
	jQuery.ajax({
		url: '/Special:OEController',
	    async: false,
		type: 'POST',
		data: ({aeform: {kind: kind, type: type, _id : id}, action: 'markForDeletion', page_path: OEF_PAGE_PATH}),
		dataType: 'json',
		success: function (data , status)
		{
			ret = data['status'];	
			
			var msg = '';
			
			if (!data['status'])
			{
				for (var index in data['errors'])
				{
					msg += data['errors'][index]+"\n";
				}
			}
			else
			{
				if (show_deleted != true) {
					jQuery('#' + prefix + '_list_block .ae_current').remove();
				}
				else {
					jQuery('#' + prefix + '_list_block .ae_current').addClass('ae_deleted_col');
				}
			}
			
			if (!msg)
			{
				msg = (data['result'] && data['result']['msg']) ? data['result']['msg'] : (data['status'] ? 'Mark for deletion succesfully' : 'Not marked for deletion');
			}
			
			displayMessage(kind + '_' + type, msg, data['status']);
		}
	});
	
	return ret;
}

/**
 * Unmark For Deletion Item
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return
 */
function executeUnmarkForDeletionListItem(kind, type, prefix)
{
	if (kind != 'catalogs' && kind != 'documents'){
		displayMessage(kind + '_' + type, 'Unsupported operation', false);
		return false;
	}
	
	var ret = true;
	var id  = getItemId(prefix);
	
	if (!id) {
		displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Choose an list item', false);
		return false;
	}
	
	jQuery.ajax({
	    url: '/Special:OEController',
	    async: false,
	    type: 'POST',
	    data: ({aeform: {kind: kind, type: type, _id : id}, action: 'unmarkForDeletion', page_path: OEF_PAGE_PATH}),
	    dataType: 'json',
	    success: function (data , status)
	    {
			ret = data['status'];
			
			var msg = '';
			
			if(!data['status'])
			{
				for(var index in data['errors'])
				{
					msg += data['errors'][index]+'\n';
				}
			}
			else
			{
				jQuery('#' + prefix + '_list_block .ae_current').removeClass('ae_deleted_col');
			}
			
			if (!msg)
			{
				msg = (data['result'] && data['result']['msg']) ? data['result']['msg'] : (data['status'] ? 'Unmarked for deletion succesfully' : 'Not unmarked for deletion');
			}
			
			displayMessage(kind + '_' + type, msg, data['status']);
	    }
	});
	
	return ret;
}

/**
 * Post Item
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return
 */
function executePostListItem(kind, type, prefix)
{
	var ret = true;
	var id  = getItemId(prefix);
	
	if (!id) {
		displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Choose an list item', false);
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
				displayMessage(kind.replace(/\./g, '_') + '_' + type, msg, false);
			}
			else {
				var element = jQuery('#' + prefix + '_list_block .ae_current').find('.ae_not_posted');
				jQuery(element).removeClass('ae_not_posted');
				jQuery(element).addClass('ae_posted');
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
function executeClearPostingListItem(kind, type, prefix)
{
	var ret = true;
	var id  = getItemId(prefix);
	
	if (!id) {
		displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Choose an list item', false);
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
				displayMessage(kind.replace(/\./g, '_') + '_' + type, msg, false);
			}
			else {
				var element = jQuery('#' + prefix + '_list_block .ae_current').find('.ae_posted');
				jQuery(element).removeClass('ae_posted');
				jQuery(element).addClass('ae_not_posted');
				displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Clear posting succesfully', true);
			}
	    }
	});
	
	return ret;
}




/**
 * Get current (selected) item id
 * 
 * @param prefix
 * @return
 */
function getItemId(prefix)
{
	return parseInt(jQuery('#' + prefix + '_list_block .ae_current').find('.' + prefix + '_item_id').html(), 10);
}

/**
 * Select Item
 * 
 * @param element
 * @param prefix
 * @return
 */
function selectColumn(element, prefix)
{
	jQuery('#' + prefix + '_list_block .ae_current').removeClass('ae_current');
	jQuery(element).parent().addClass('ae_current');
	
	return false;
}





/**
 * Constructor Submenu for New On Basis
 * 
 * @param string kind
 * @param string type
 * @param object basis_for
 * @return 
 */
function oefNewOnBasis(kind, type, basis_for)
{
	var submenu_id = 'oef_js_submenu';
	var link = null;
	
	this.kind  = kind;
	this.type  = type;
	this.basis_for = basis_for;
	
	
	/**
	 * Process update menu event
	 * 
	 * @param DOMElements element - clicked link
	 * @return void
	 */
	this.updateMenu = function(element)
	{
		link = element;
		
		if (jQuery(element).hasClass('oef_menu_opened'))
		{
			removeSubmenu();
			
			return (element.href == location.href) ? false : true;
		}
		else
		{
			element.href = '';
			
			openSubMenu(this.kind, this.type, this.basis_for);
			
			return false;
		}
	};
	
	/**
	 * Open submenu
	 * 
	 * @return void
	 */
	function openSubMenu(kind, type, basis_for)
	{
		var bid = getItemId(kind + '_' + type);
		
		if (!bid)
		{
			alert('Choose an list item');
			return;
		}
		
		jQuery(link).addClass('oef_menu_opened');
		
		var pos     = getElementPosition(link);
		var submenu = '';
		
		submenu += '<div id="' + submenu_id + '" ';
		submenu += 'style="position: absolute; top: ' + (pos['top'] + pos['height']) + 'px; left: ' + pos['left'] + 'px;">';
		
		for (var rkind in basis_for)
		{
			for (var i in basis_for[rkind])
			{
				var rtype = basis_for[rkind][i];
				
				submenu += '<div class="oef_menu_item" uid="' + rkind + '.' + rtype + '">' + rtype + '</div>';
			}
		}
		
		submenu += '</div>'; 
		
		jQuery(link).append(submenu);
		jQuery('#' + submenu_id + ' .oef_menu_item').click(function(event) {
			event = event || window.event;
			
			var node  = event.target || event.srcElement;
			var uid   = jQuery(node).attr('uid');
			var basis = kind + '.' + type;
			var href  = location.href + '?uid=' + uid + '&actions=displayEditForm&basis=' + basis + '&bid=' + bid;
			
			jQuery(link).attr('href', href);
		});
		
		jQuery('body').bind('click', bodyClick);
	}
	
	/**
	 * Remove submenu
	 * 
	 * @return void
	 */
	function removeSubmenu()
	{
		jQuery(link).removeClass('oef_menu_opened');
		
		jQuery('#' + submenu_id).remove();
	}
	
	/**
	 * Add to body onClick event to control this submenu
	 * 
	 * @param DOMEvents event
	 * @return void
	 */
	function bodyClick(event)
	{
		event = event || window.event;
		
		var node  = event.target || event.srcElement;
		
		if (node != link && jQuery(node).parents('#' + submenu_id).size() == 0)
		{
			removeSubmenu();
			
			jQuery('body').unbind('click', bodyClick);
		}
	}
	
	/**
	 * Return elements position attributes
	 * 
	 * @param DOMElements elem
	 * @return object
	 */
	function getElementPosition(elem)
	{
	    var w = elem.offsetWidth;
	    var h = elem.offsetHeight;
	    
	    var l = 0;
	    var t = 0;
	    
	    while (elem)
	    {
	        l += elem.offsetLeft;
	        t += elem.offsetTop;
	        elem = elem.offsetParent;
	    }

	    return {"left":l, "top":t, "width": w, "height":h};
	}
}