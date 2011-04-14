/************************************* Actions ******************************************/

/**
 * Action edit item
 * 
 * @param DOMElements element
 * @param string kind - entity kind
 * @param string type - entity type
 * @param int    id   - entity id
 * 
 * @return boolean
 */
function editItem(element, kind, type, id)
{ 
	if (!id || id < 1) return false;
	
	return openPopup(element, kind, type, 'EditForm', {id: id});
}

/**
 * Update ItemForm
 * 
 * @param pageAPI - deki api to MT page
 * @param tagid   - parent to page content
 * @param maxRequestTime - max request execution time 
 * @return void
 */
function updateItemForm(pageAPI, tagid, maxRequestTime)
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
			
			jQuery('#' + tagid).html(body);
		}/*,
	    error: function (XMLHttpRequest, textStatus, errorThrown)
	    {
			alert(textStatus);
	    }*/
	});
}

/**
 * Get params from query string
 * 
 * @return array
 */
function getQueryVariable()
{
	var arr = new Array();
	var query = window.location.search.substring(1);
	
	if (query.length == 0) return arr;
	
	var vars = query.split("&");
	for (var i = 0; i < vars.length; i++) {
		var pair = vars[i].split("=");
		arr[pair[0]] = pair[1];
	}
	
	return arr;
}
