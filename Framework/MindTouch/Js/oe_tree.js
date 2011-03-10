var oe_item_template = {};

/************************************* OnLoad ******************************************/

jQuery(document).ready(function() {
	
	jQuery('.oef_tree_active').click(clickTree);
});

function clickTree(event)
{
	event = event || window.event;
	
	var node = event.target || event.srcElement;
	
	event.stopPropagation ? event.stopPropagation() : (event.cancelBubble = true);
	
	var item = jQuery(node).parents('.ae_list_item').get(0);
	var tree = new oefTree();
	
	tree.onClick(item);
}

function oefTree()
{
	var uid    = null;
	var prefix = null;
	var item   = null;
	
	/**
	 * Process onClick event
	 * 
	 * @param DOMElement _item
	 * @return void
	 */
	this.onClick = function(_item)
	{
		item = _item;
		
		prefix = jQuery(item).parents('.ae_list_block').attr('id');
		prefix = prefix.replace('_list_block', '');
		uid    = prefix.replace('_', '.');
		
		var nodeId = jQuery(item).find('.ae_item_id').text();
		
		if (jQuery(item).find('.oef_tree_active').hasClass('oef_tree_closed'))
		{
			jQuery(item).find('.oef_tree_folder, .oef_tree_item').addClass('oef_tree_loader');
			this.load(uid, nodeId, this.appendChild);
		}
		else
		{
			this.close(nodeId);
		}
	};
	
	/**
	 * Load data
	 * 
	 * @param string uid
	 * @param int    nodeId
	 * @param link   callback
	 * @return void
	 */
	this.load = function(uid, nodeId, callback)
	{
		jQuery.ajax({
    		url: '/Special:OEController',
    	    async: true,
    		type: 'POST',
    		data: ({
    			action:     'getChildren',
    			parameters: {
    				uid:     uid,
    				node:    nodeId,
    				options: {with_link_desc: true}
    			}
    		}),
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
    		    
    		    callback(data, nodeId);
    		},
    	    error: function (XMLHttpRequest, textStatus, errorThrown)
    	    {
    			clearTimeout(this.reqTimeout);
    			
    			alert('Request error');
    	    }
    	});
	};
	
	/**
	 * Append child nodes
	 * 
	 * @param object data
	 * @return boolean
	 */
	this.appendChild = function(data, nodeId)
	{
		if (!data['status'])
		{
			var msg = '';
			
			for (var field in data['errors'])
			{
				if (!displayErrors(prefix + '_' + field, result['errors'][field]))
				{
					msg += (msg.length > 0 ? ",&nbsp;" : "&nbsp;") + result['errors'][field];
				}
			}
			
			if (msg) displayMessage(prefix, msg, false);
			
			return false;
		}
		
		if (!oe_item_template[prefix])
		{
			displayMessage(prefix, 'Template not found', false);
			return false;
		}
		
		jQuery(item).find('.oef_tree_closed').removeClass('oef_tree_closed');
		
		var list  = data['result']['list'];
		var links = data['result']['links'];
		
		var padding  = jQuery(item).find('.oef_tree_control').css('padding-left');
		var template = oe_item_template[prefix];
		
		padding = parseInt(padding.replace("px", ""), 10);
		
		var added = item;
		
		for (var key in list)
		{
			var tpl    = template;
			var folder = null;
			
			for (var field in list[key])
			{
				var value = list[key][field];
				
				if (field == '_folder')
				{
					folder = (value == '1');
					
					continue;
				}
				
				if (field == '_id' || field == '_deleted') continue;
				
				if (links[field] && links[field][value])
				{
					value = links[field][value]['text'];
				}
				
				tpl = tpl.replace('%%' + field + '%%', value);
			}
			
			tpl   = tpl.replace(/%prefix%/gi, prefix);
			added = jQuery(added).after(tpl).next().get(0);
			
			jQuery(added).attr('parent', nodeId).find('.oef_tree_control').css('padding-left', (padding + 15) + 'px');
			jQuery(added).find('.ae_item_id').text(list[key]['_id']);
			
			if (folder != null)
			{
				if (!folder)
				{
					jQuery(added).find('.oef_tree_active').removeClass('oef_tree_active oef_tree_closed').addClass('oef_tree_not_active').end().
						find('.oef_tree_folder').removeClass('oef_tree_folder oef_tree_closed').addClass('oef_tree_item');
					
					continue;
				}
			}
			
			jQuery(added).find('.oef_tree_active').click(clickTree);
		}
		
		jQuery(item).find('.oef_tree_folder, .oef_tree_item').removeClass('oef_tree_loader');
		
		return true;
	};
	
	/**
	 * Close node
	 * 
	 * @return boolean
	 */
	this.close = function(nodeId)
	{
		jQuery(item).find('.oef_tree_active').addClass('oef_tree_closed').end().
			find('.oef_tree_folder, .oef_tree_item').addClass('oef_tree_closed');
		
		var padding = jQuery(item).find('.oef_tree_control').css('padding-left');
		padding = parseInt(padding.replace("px", ""), 10);
		
		var current = jQuery(item).next().get(0);
		var padd    = jQuery(current).find('.oef_tree_control').css('padding-left');
		padd = parseInt(padd.replace("px", ""), 10);
		
		while (padd > padding)
		{
			var prev = current;
			current  = jQuery(current).next().get(0);
			
			if (padd > padding) {
				jQuery(prev).remove();
			}
			
			if (!current) return true;
			
			padd = jQuery(current).find('.oef_tree_control').css('padding-left');
			padd = parseInt(padd.replace("px", ""), 10);
		}
		
		return true;
	};
}
