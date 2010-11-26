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
	
	if (jQuery('#' + prefix + '_list_block .ae_current').find('.ae_posted').size() != 0)
	{
		appInactive();
		
		if (confirm('This document is posted. You must clear posted before edit.\n\n Clear posted end open edit form?'))
		{
			appAddLoader();
			res = executeClearPostingListItem(kind, type, prefix);
		}
		else
		{
			res = false;
		}
		
		appActive();
		
		if (!res) return false;
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
 * Action delete Item
 * 
 * @param kind
 * @param type
 * @param prefix
 * @param show_deleted
 * @return
 */
function deleteListItem(kind, type, prefix, show_deleted)
{
	var result = true;
	
	appInactive();
	
	if (confirm('Are you sure?'))
	{
		appAddLoader();
			
		result = executeDeleteListItem(kind, type, prefix, show_deleted);
	}
	else jQuery('.' + kind.replace(/\./g, '_') + '_' + type + '_message').css('display', 'none');
	
	appActive();
	
	return result;
}

/**
 * Action restore Item
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return
 */
function restoreListItem(kind, type, prefix)
{
	appInactive();
	appAddLoader();
	
	var result = executeRestoreListItem(kind, type, prefix);

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
 * @param show_deleted
 * @return
 */
function executeDeleteListItem(kind, type, prefix, show_deleted)
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
		data: ({aeform: {kind: kind, type: type, _id : id}, action: 'delete'}),
		dataType: 'json',
		success: function (data , status)
		{
			ret = data['status'];	
			
			if(!data['status'])
			{
				var msg = '';
				for(var index in data['errors'])
				{
					msg += index + ': ' + data['errors'][index]+"\n";
				}
				displayMessage(kind.replace(/\./g, '_') + '_' + type, "At Mark for deletion there were some errors\n" + msg, false);
			}
			else {
				if (show_deleted != true){
					jQuery('#' + prefix + '_list_block .ae_current').remove();
				}
				else {
					jQuery('#' + prefix + '_list_block .ae_current').addClass('ae_deleted_col');
				}
				displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Mark for deletion succesfully', true);
			}
		}
	});
	
	return ret;
}

/**
 * Restore Item
 * 
 * @param kind
 * @param type
 * @param prefix
 * @return
 */
function executeRestoreListItem(kind, type, prefix)
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
	    data: ({aeform: {kind: kind, type: type, _id : id}, action: 'restore'}),
	    dataType: 'json',
	    success: function (data , status)
	    {
			ret = data['status'];
			
			if(!data['status'])
			{
				var msg = '';
				for(var index in data['errors'])
				{
					msg += index + ': ' + data['errors'][index]+'\n';
				}
				displayMessage(kind.replace(/\./g, '_') + '_' + type, "At Restore there were some errors\n" + msg, false);
			}
			else {
				jQuery('#' + prefix + '_list_block .ae_current').removeClass('ae_deleted_col');
				displayMessage(kind.replace(/\./g, '_') + '_' + type, 'Restore succesfully', true);
			}
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