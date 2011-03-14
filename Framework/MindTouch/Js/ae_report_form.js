
/************************************* OnLoad ******************************************/

jQuery(document).ready(function() {
	
	markSelected();
	
    var report_form_options = {
       url: '/Special:OEController',
       dataType:  'json',
       beforeSubmit: prepareRequest,
       success: processResponse,
       data: {action: 'generate', form : 'ReportForm', page_path: OEF_PAGE_PATH}
    };
    
    jQuery('.ae_report_form').submit(function() {
    	if (beforeSubmit(this)) {
    	   	jQuery(this).ajaxSubmit(report_form_options);
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
	
	for(var main_kind in data)
	{
		for(var main_type in data[main_kind])
		{
			var m_data = data[main_kind][main_type];
			var msg = '';
						
			if(m_data['status'] != true) // Print main errors
			{
				// Clear report
				jQuery('.ae_report').html('');
				
				// Display errors
				for(var field in m_data['errors'])
				{
					if (!displayErrors(main_kind + '_' + main_type + '_' + field, m_data['errors'][field])) {
						msg += (msg.length > 0 ? ",&nbsp;" : "&nbsp;") + m_data['errors'][field];
					}
				}
			}
			else // Insert report
			{
				jQuery('.ae_report').html(m_data['result']['output']);
			}
			
			// Print main message
			displayMessage(main_kind + '_' + main_type,  msg.length > 0 ? msg : m_data['result']['msg'], m_data['status']);
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





/************************************* Actions ******************************************/

var aeDecode = function(_type, _params)
{
	/* Attributes */
	this.decodes = _params;
	this.type = _type;
	
	/**
	 * Process responce
	 * 
	 * @param array responce
	 * @return void
	 */
	this.processResponce = function(responce) {
		if (responce['status'] == false)
		{
			displayMessage('reports_' + this.type, responce['result']['msg'], false);
			return;
		}
		var data = responce['result']['data'];
		
		if (!data) {
			alert('Result is empty');
		}
		else if (data.content && typeof(data.content) == 'string') {
			var decWin = window.open(null, null, 'width=400,height=250');
			decWin.document.write(data.content);
			decWin.moveTo(300,200);
			decWin.focus();
		}
		else if (data.reference && typeof(data.reference) == 'object') {
			url  = location.protocol + '//' + location.host + location.pathname;
			flag = false;
			for (var key in data.reference) {
				if (!flag) {
					url += '?';
					flag = true;
				} 
				else url += '&';
				
				if (typeof data.reference[key] == 'object') {
					var first = true;
					for (var name in data.reference[key]) {
						if (first) first = false;
						else url += '&';
						
						url += key + '_' + name + '=' + data.reference[key][name];
					}
				}
				else url += key + "=" + data.reference[key];
			}
			var decWin = window.open(encodeURI(url));
			decWin.focus();
		}
		else alert('Result is wrong');
	};
	
	/**
	 * Display decode for selected item
	 * 
	 * @param string decode - decode variant
	 * @return void
	 */
    this.showDecode = function(decode) {
    	var params = {};
    	params[this.type] = {};
    	params[this.type][decode] = this.decodes[decode];
    	
    	this.sendDecodeRequest(params, this.processResponce);
	};
	
	/**
	 * Send request to server
	 * 
	 * @param array params - posted params
	 * @param string callback - callback function to process server responce
	 * @return void
	 */
	this.sendDecodeRequest = function(params, callback) {
		jQuery.ajax({
    		url: '/Special:OEController',
    	    async: true,
    		type: 'POST',
    		data: ({parameters: params, action: 'decode', page_path: OEF_PAGE_PATH}),
    		cache: false,
    		dataType: 'json',
    		reqTimeout: null,
    		beforeSend: function (xmlhttp)
    		{
    			this.reqTimeout = setTimeout(function () { xmlhttp.abort(); alert('Timeout have been exceeded'); }, 30000);
    		},
    		success: function (data, status)
    		{
    		    clearTimeout(this.reqTimeout);
    		    
    		    callback(data);
    		},
    	    error: function (XMLHttpRequest, textStatus, errorThrown)
    	    {
    			clearTimeout(this.reqTimeout);
    			
    			alert('Request error');
    	    }
    	});
    };
};

/**
 * Decode tabular document item
 * 
 * @param object event
 * @param object parameters
 * @return
 */
function decode(event, parameters)
{
	if (event.stopPropagation)
	{
		event.stopPropagation();
	}
	else event.cancelBubble = true;
	
	// All decode variants
	var decodes = new Array();
	for (var key in parameters) decodes.push(key);
	
	if (decodes.length === 0) return;
	
	var type = jQuery(event.target || event.srcElement).parents('.ae_report').attr('type');
	
	Decode = new aeDecode(type, parameters);
	Decode.showDecode(decodes[0]);
}


/************************************* Functions ******************************************/

