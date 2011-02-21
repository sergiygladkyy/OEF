
/************************************* OnLoad ******************************************/

jQuery(document).ready(function() {
	var related_options = {
		url: '/Special:OEController',
		dataType: 'json',
		beforeSubmit: prepareRequest,
		success: processRelatedResponse,
		data: {action: 'relatedForDeletion', form : 'DeletionForm'}
	};
	
	var delete_options = {
		url: '/Special:OEController',
		dataType: 'json',
		beforeSubmit: prepareRequest,
		success: processDeleteResponse,
		data: {action: 'deleteMarkedForDeletion', form : 'DeletionForm'}
	};
    
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
    	var method  = 'submitForm';
    	var form    = jQuery(element).parents('form');
    	var command = jQuery(element).attr('command');
    	
    	switch(command)
    	{
    		case 'related':
    			options = related_options;
    		break;
    		
    		case 'delete':
    			alert('delete');
    			return;
    		break;
    		
    		default:
    			alert('Not supported operation');
				return;
    	}
    	
    	try {
    		eval(method + '(form, options)');
    	}
    	catch(e) { ; }
    }
});




/************************************* Forms ******************************************/

/**
 * Submit form
 * 
 * @param mixed  form    - form object
 * @param object options - submit options
 * @return void
 */
function submitForm(form, options)
{
	appInactive();
	
	if (beforeSubmit(form))
	{
		appAddLoader();
		
		jQuery(form).ajaxSubmit(options);
	}
	else
	{
		appActive();
	}
}

/**
 * Execute before submit
 * 
 * @param mixed form
 * @return
 */
function beforeSubmit(form)
{
	return true;
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
    return true; 
}

/**
 * Process respons
 * 
 * @param data
 * @param status
 * @return
 */
function processRelatedResponse(data, status)
{
	if (!data.status)
	{
		if (data.errors)
		{
			displayMessage('delete_marked_for_deletion', data.errors.global, false);
		}
		
		return;
	}
	
	if (!data.result.list)
	{
		displayMessage('delete_marked_for_deletion', 'Invalid data', false);
		
		return;
	}
	
	data = data.result.list;
	
	var form = new DeletionForm();
	
	form.addRelatedItems(data);
	
	appActive();
}

/**
 * Process respons
 * 
 * @param data
 * @param status
 * @return
 */
function processDeleteResponse(data, status)
{
	;
}




/************************************* Actions ********************************************/


/************************************* Functions ******************************************/

function ucfirst(str)
{
    if (!str) return '';
    
    str += '';
    
    return str.charAt(0).toUpperCase() + str.substr(1);
}


function DeletionForm()
{
	var rel_id = 'oef_related_entities_container';
	
	/**
	 * Add list of related entities in form
	 * 
	 * @param object data
	 * @return void
	 */
	this.addRelatedItems = function(data)
	{
		this.clearRelated();
		
		var rows = prepareRelatedData(data);
		
		var content = '<table>';
		
		for (var kind in rows)
		{
			content += '<tr><td class="oef_group_header">' + ucfirst(kind) + '</td></tr>';
			
			var cnt = 0;
			
			for (var type in rows[kind])
			{
				for (var id in rows[kind][type])
				{
					param = rows[kind][type][id];
					
					content += '<tr class="item"><td class="' + (cnt % 2 == 0 ? 'oef_even' : 'oef_not_even') + '">';
					content += '<a href="?uid=' + param.kind + '.' + param.type + '&actions=displayItemForm&id=' + param.value + '"';
					content += ' target="_blank" class="oef_link">' + param.text + '</a>';
					content += '</td></tr>';
					
					cnt++;
				}
			}
		}
		
		content += '</table>';
		
		jQuery('#' + rel_id).html(content);
	};
	
	/**
	 * Clear list of related in form
	 * 
	 * @return void
	 */
	this.clearRelated = function()
	{
		jQuery('#' + rel_id + ' .item').remove();
	};
	
	/**
	 * Prepare related data to method addRelatedItems
	 * 
	 * @param object data
	 * @return object
	 */
	function prepareRelatedData(data)
	{
		var result = {};
		
		for (var dkind in data)
		{
			for (var dtype in data[dkind])
			{
				var related = data[dkind][dtype];
				
				for (var rkind in related)
				{
					if (!result[rkind])
					{
						result[rkind] = {};
					}
					
					for (var rtype in related[rkind])
					{
						var params = related[rkind][rtype];
						
						if (!result[rkind][rtype])
						{
							result[rkind][rtype] = new Array();
						}
						
						for (var id in params)
						{
							if (!result[rkind][rtype][id])
							{
								var param  = params[id];
								param.kind = rkind;
								param.type = rtype;
								
								result[rkind][rtype][id] = param;
							} 
						}
					}
				}
			}
		}
		
		return result;
	}
}
