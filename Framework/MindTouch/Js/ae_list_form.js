/************************************* Events *******************************************/

self.childClose = function (data)
{
	if (data.message && data.prefix && data.type)
	{
		displayMessage(data.prefix, data.message, data.type);
	}
}

/************************************* Actions ******************************************/

/**
 * Action new Item
 * 
 * @param element
 * @param href
 * @param prefix
 * @return
 */
function newListItem(element, href, prefix)
{
	jQuery(element).attr('href', href);
	
	return true;
}

/**
 * Action edit Item
 * 
 * @param element
 * @param href
 * @param prefix
 * @return
 */
function editListItem(element, href, prefix, kind, type)
{
	var res = true; 
	var id  = getItemId(prefix);
	
	if (!id) {
		alert('Choose an list item');
		return false;
	}
	
	jQuery(element).attr('href', href + '&id=' + id);
	
	return res;
}

/**
 * Action view Item
 * 
 * @param element
 * @param href
 * @param prefix
 * @return
 */
function viewListItem(element, href, prefix)
{
	var id = getItemId(prefix);
	
	if (!id) {
		alert('Choose an list item');
		return false;
	}
	
	jQuery(element).attr('href', href + '&id=' + id);
	
	return true;
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
			
			var oldMsg = jQuery('#' + tagid + ' .systemmsg').get(0);
			if (!oldMsg) return;
			
			var msg = oldMsg.cloneNode(true);
			var id  = parseInt(jQuery('.ae_list_block .ae_current').find('.ae_item_id').text(), 10);
			
			jQuery('#' + tagid).html(body);
			jQuery('#' + tagid + ' .systemmsg').replaceWith(msg);
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
