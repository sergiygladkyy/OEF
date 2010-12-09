
function oeMultiselect()
{
	this.data    = {};
	this.options = {};
	this.prefix  = '';
	
	/**
	 * Display Multiselect
	 * 
	 * @param object data
	 * @param object options
	 * @return bool
	 */
	this.showMultiselect = function(data, options)
	{
		// Initialize parameters
		if (typeof data == "object" || typeof data == "array")
		{
			this.data = data;
		}
		
		if (typeof options == "object" || typeof options == "array")
		{
			this.options = options;
		}
		
        if (!this.options.name || !this.options.tag_id) return false;
		
		this.prefix = this.options.name.replace(/[\[\]]*$/g, '');
		this.prefix = this.prefix.replace(/[\[\]]/g, '_');
		
		// Generate
		if (!this.draw(this.options.tag_id)) return false;
		
		// Initialize multiselect
		jQuery('#' + this.options.tag_id).find('.oe_controll input[name=add]').click(function(event){
			var obj = new oeMultiselect();
			obj.addFromList(event);
		});
		jQuery('#' + this.options.tag_id).find('.oe_controll input[name=remove]').click(function(event){
			var obj = new oeMultiselect();
			obj.removeFromSelected(event);
		});
		
		this.initialize(jQuery('#' + this.options.tag_id + ' .oe_multiselect').get(0));
	};
	
	/**
	 * Generate HTML
	 * 
	 * @param string tag_id
	 * @return bool
	 */
	this.draw = function(tag_id)
	{
		var content = '<div class="oe_multiselect oe_' + this.prefix + '">';
		
		// List
		content += '\n\t<div class="oe_list">';
		if (this.data.list)
		{
			for (var i in this.data.list)
			{
				content += this.getListElement(this.data.list[i]);
			}
		}
		content += '\n\t</div>';
		
		// Controls
		content += '\n\t<div class="oe_controll">';
		content += '<input type="button" name="add" value=">" /><br/>';
		content += '<input type="button" name="remove" value="<" /></div>';
		
		// Selected
		content += '\n\t<div class="oe_selected">';
		if (this.data.selected)
		{
			for (var i in this.data.selected)
			{
				content += this.getSelectedElement(this.data.selected[i]);
			}
		}
		content += '\n\t</div>';
		
		content += '\n\t<div style="clear: both;"></div>';
		content += '\n</div>';
		
		/* Insert HTML */
		
		jQuery('#' + tag_id).html(content);
		
		return true;
	};
	
	/**
	 * Generate list item
	 * 
	 * @param object data
	 * @return string
	 */
	this.getListElement = function(data)
	{
		var text  = data.text  ? data.text : '';
		var value = data.value ? data.value : '';
		
		return '\n\t\t<div class="oe_list_item"><span class="oe_ms_text">' + text + '</span><span class="oe_ms_value" style="display: none;">' + value + '</span></div>';
	};
	
	/**
	 * Generate selected item
	 * 
	 * @param object data
	 * @return string
	 */
	this.getSelectedElement = function(data)
	{
		var text  = data.text  ? data.text : '';
		var name  = this.options.name ? this.options.name : 'mselect[]'; 
		var value = data.value ? data.value : '';
		
		return '\n\t\t<div class="oe_selected_item"><span class="oe_ms_text">' + text + '</span><input type="hidden" name="' + name + '" value="' + value + '" class="oe_ms_value"></div>';
	};
	
	/**
	 * Mark item
	 * 
	 * @param object event
	 * @return void
	 */
	this.markItem = function(event)
	{
		var node = event.currentTarget;
		
		if (jQuery(node).hasClass('oe_mark'))
		{
			jQuery(node).removeClass('oe_mark');
		}
		else
		{
			jQuery(node).addClass('oe_mark');
		}
	};
	
	/**
	 * Add mark item from list into selected
	 * 
	 * @param object event
	 * @return void
	 */
	this.addFromList = function(event)
	{
		var node = event.currentTarget;
		var ms   = jQuery(node).parents('.oe_multiselect').get(0);
		var data = {};
		var func = this.getSelectedElement;
		
		jQuery(ms).find('.oe_list .oe_mark').each(function(index) {
			data.text  = jQuery(this).find('.oe_ms_text').text();
			data.value = jQuery(this).find('.oe_ms_value').text();
			jQuery(ms).find('.oe_selected').append(func(data));
			jQuery(this).remove();
		});
		
		this.initialize(ms);
	};
	
	/**
	 * Remove mark item from selected (add into list)
	 * @param object event
	 * @return void
	 */
	this.removeFromSelected = function(event)
	{
		var node = event.currentTarget;
		var ms   = jQuery(node).parents('.oe_multiselect').get(0);
		var data = {};
		var func = this.getListElement;
		
		jQuery(ms).find('.oe_selected .oe_mark').each(function(index) {
			data.text  = jQuery(this).find('.oe_ms_text').text();
			data.value = jQuery(this).find('.oe_ms_value').attr('value');
			jQuery(ms).find('.oe_list').append(func(data));
			jQuery(this).remove();
		});
		
		this.initialize(ms);
	};
	
	/**
	 * Initialize multiselect
	 * 
	 * @param object node - root node (class="oe_multiselect")
	 * @return void
	 */
	this.initialize = function(node)
	{
		var markFunc = this.markItem;
		
		jQuery(node).find('.oe_list_item').each(function(index){
			if (!this.onclick) this.onclick = markFunc;
		});
		jQuery(node).find('.oe_selected_item').each(function(index){
			if (!this.onclick) this.onclick = markFunc;
		});
		
		jQuery(node).find('.oe_list_item').removeClass('oe_list_even');
		jQuery(node).find('.oe_selected_item').removeClass('oe_selected_even');
		jQuery(node).find('.oe_list .oe_list_item:nth-child(even)').addClass('oe_list_even');
		jQuery(node).find('.oe_selected .oe_selected_item:nth-child(even)').addClass('oe_selected_even');
	};
}
