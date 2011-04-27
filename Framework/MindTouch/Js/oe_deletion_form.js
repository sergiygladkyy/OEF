
/************************************* OnLoad ******************************************/

jQuery(document).ready(function() {
	var related_options = {
		url: '/Special:OEController',
		dataType: 'json',
		beforeSubmit: prepareRequest,
		success: processRelatedResponse,
		data: {action: 'relatedForDeletion', form : 'DeletionForm', page_path: OEF_PAGE_PATH}
	};
	
	var unmark_options = {
		url: '/Special:OEController',
		dataType: 'json',
		beforeSubmit: prepareRequest,
		success: processUnmarkResponse,
		data: {action: 'batchUnmarkForDeletion', form : 'DeletionForm', page_path: OEF_PAGE_PATH}
	};
	
	var delete_options = {
		url: '/Special:OEController',
		dataType: 'json',
		beforeSubmit: prepareRequest,
		success: processDeleteResponse,
		data: {action: 'deleteMarkedForDeletion', form : 'DeletionForm', page_path: OEF_PAGE_PATH}
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
    	hideMessages();
    	
    	var options;
    	var method  = 'submitForm';
    	var form    = jQuery(element).parents('form');
    	var command = jQuery(element).attr('command');
    	
    	switch(command)
    	{
    		case 'related':
    			options = related_options;
    		break;
    		
    		case 'unmarked':
    			options = unmark_options;
    		break;
    		
    		case 'delete':
    			if (!confirm('Are you sure?')) return;
    			options = delete_options;
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
		
		appActive();
		
		return;
	}
	
	if (!data.result.list)
	{
		displayMessage('delete_marked_for_deletion', 'Invalid data', false);
		
		return;
	}
	
	data = data.result.list;
	
	var form = new DeletionForm();
	
	var cnt  = form.updateForm(data);
	
	if (cnt == 0) displayMessage('delete_marked_for_deletion', 'Relations not found', true);
	
	appActive();
}

/**
 * Process respons
 * 
 * @param data
 * @param status
 * @return
 */
function processUnmarkResponse(data, status)
{
	if (!data.status)
	{
		if (data.errors)
		{
			displayMessage('delete_marked_for_deletion', data.errors.global, false);
		}
		
		appActive();
		
		return;
	}
	
	var status = true;
	var form   = new DeletionForm();
	var data   = data.result;
	
	for (var kind in data)
	{
		for (var type in data[kind])
		{
			var cdata = data[kind][type];
			
			if (!cdata.status)
			{
				status = false;
				continue;
			}
			
			form.removeFromMarkedList(kind, type);
		}
	}
	
	var msg = status ? 'Unmarked successfully' : 'Mark for deletion is not removed from all records';
	
	displayMessage('delete_marked_for_deletion', msg, status);
	
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
	if (!data.status)
	{
		if (data.errors)
		{
			displayMessage('delete_marked_for_deletion', data.errors.global, false);
		}
		
		appActive();
		
		return;
	}
	
	var status = true;
	var form   = new DeletionForm();
	var data   = data.result;
	
	for (var kind in data)
	{
		for (var type in data[kind])
		{
			var cdata = data[kind][type];
			
			if (!cdata.status)
			{
				status = false;
				continue;
			}
			
			form.removeFromMarkedList(kind, type);
		}
	}
	
	var msg = status ? 'Deleted successfully' : 'Don\'t delete all records';
	
	displayMessage('delete_marked_for_deletion', msg, status);
	
	appActive();
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
	var mark_id = 'oef_marked_for_deletion_container';
	var rel_id  = 'oef_related_entities_container';
	
	/**
	 * Update deletion form (process response data)
	 * 
	 * @param object data
	 * @return int
	 */
	this.updateForm = function(data)
	{
		var rdata = prepareRelatedData(data);
		
		this.updateMarkedList(rdata.has_related);
		
		return this.addRelatedItems(rdata.related);
	};
	
	/**
	 * Update list of marked for deletion
	 * 
	 * @param object rows
	 * @return void
	 */
	this.updateMarkedList = function(rows)
	{
		jQuery('#' + mark_id + ' .oef_deletion_checkboxes input:checked').each(function() {
			var link = jQuery(this).parents('.oef_marked_item').find('.oef_link').get(0);
			
			if (!jQuery(link).hasClass('oef_could_be_removed'))
			{
				jQuery(link).removeClass('oef_could_not_be_removed');
				jQuery(link).addClass('oef_could_be_removed');
			}
		});
		
		for (var kind in rows)
		{
			for (var type in rows[kind])
			{
				for (var id in rows[kind][type])
				{
					var selector = '#' + mark_id + ' .oef_' + kind + '_' + type + '_' + id;
					
					jQuery(selector).removeClass('oef_could_be_removed');
					jQuery(selector).addClass('oef_could_not_be_removed');
				}
			}
		}
	};
	
	/**
	 * Add list of related entities in form
	 * 
	 * @param object rows
	 * @return int
	 */
	this.addRelatedItems = function(rows)
	{
		this.clearRelated();
		
		var numb = 0;
		
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
					numb++;
				}
			}
		}
		
		content += '</table>';
		
		jQuery('#' + rel_id).html(content);
		
		return numb;
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
	 * Remove items from marked for deletion list
	 * 
	 * @param string kind
	 * @param string type
	 * @return void
	 */
	this.removeFromMarkedList = function (kind, type)
	{
		var _class = '.oef_' + kind + '_' + type + '_item';
		
		jQuery('#' + mark_id + ' ' + _class + ' input:checked').parents(_class).remove();
	};
	
	/**
	 * Prepare related data to method addRelatedItems
	 * 
	 * @param object data
	 * @return object
	 */
	function prepareRelatedData(data)
	{
		var related = {};
		var hasrel  = {};
		
		for (var pkind in data)
		{
			for (var ptype in data[pkind])
			{
				var relation = data[pkind][ptype];
				
				for (var rkind in relation)
				{
					if (!hasrel[pkind])  hasrel[pkind]  = {};
					if (!related[rkind]) related[rkind] = {};
					
					for (var rtype in relation[rkind])
					{
						var params = relation[rkind][rtype];
						
						if (!hasrel[pkind][ptype])  hasrel[pkind][ptype]  = new Array();
						if (!related[rkind][rtype]) related[rkind][rtype] = new Array();
						
						for (var id in params)
						{
							if (!related[rkind][rtype][id])
							{
								var param  = params[id];
								param.kind = rkind;
								param.type = rtype;
								
								related[rkind][rtype][id] = param;
							}
							
							if (!param.rel) continue;
							
							for (var ind in param.rel)
							{
								if (!hasrel[pkind][ptype][param.rel[ind]])
								{
									hasrel[pkind][ptype][param.rel[ind]] = new Array();
								}
								
								// Generate relation description array
							}
						}
					}
				}
			}
		}
		
		return {'has_related': hasrel, 'related': related};
	}
}
