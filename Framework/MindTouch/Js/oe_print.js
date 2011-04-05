var OPENED_TO_PRINT = {};

/**
 * Process onPrint event
 * 
 * @param DOMElements element - action link
 * @param string kind         - current entity kind
 * @param string type         - current entity type
 * @param int    id           - current id
 * @param object layout       - layout configuration
 * @return void
 */
function onPrint(element, kind, type, id, layout)
{
	var print = new oefPrint(kind, type, id, layout);
	
	return print.updateMenu(element);
}

/**
 * Constructor Submenu for Print
 * 
 * @param string kind
 * @param string type
 * @param int    id
 * @param object layout
 * @return 
 */
function oefPrint(kind, type, id, layout)
{
	var submenu_id = 'oef_js_submenu';
	var link   = null;
	var oPrint = null;
	
	this.kind   = kind;
	this.type   = type;
	this.id     = id;
	this.layout = layout;
	
	
	/**
	 * Process update menu event
	 * 
	 * @param DOMElements element - clicked link
	 * @return void
	 */
	this.updateMenu = function(element)
	{
		link   = element;
		oPrint = this;
		
		if (jQuery(element).hasClass('oef_menu_opened'))
		{
			removeSubmenu();
		}
		else if (this.layout.length == 1)
		{
			executePrint(this.kind, this.type, this.id, this.layout[0]);
		}
		else
		{
			openSubMenu(this.kind, this.type, this.id, this.layout);
		}
		
		return false;
	};
	
	/**
	 * Open submenu
	 * 
	 * @return void
	 */
	function openSubMenu(kind, type, id, layout)
	{
		if (!id)
		{
			alert('Unknow entity');
			return;
		}
		
		jQuery(link).addClass('oef_menu_opened');
		
		var pos     = getElementPosition(link);
		var submenu = '';
		
		submenu += '<div id="' + submenu_id + '" ';
		submenu += 'style="position: absolute; top: ' + (pos['top'] + pos['height']) + 'px; left: ' + pos['left'] + 'px;">';
		
		for (var i in layout)
		{
			var template = layout[i];
			
			submenu += '<div class="oef_menu_item" template="' + template + '">' + template + '</div>';
		}
		
		submenu += '</div>'; 
		
		jQuery(link).append(submenu);
		jQuery('#' + submenu_id + ' .oef_menu_item').click(function(event) {
			event = event || window.event;
			
			//event.stopPropagation ? event.stopPropagation() : (event.cancelBubble = true);
			
			var node     = event.target || event.srcElement;
			var template = jQuery(node).attr('template');
			
			jQuery('#' + submenu_id).css('display', 'none');
			
			executePrint(kind, type, id, template);
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
	
	
	/**
	 * Execute print action
	 * 
	 * @param string kind
	 * @param string type
	 * @param int    id
	 * @param string template
	 * @return void
	 */
	function executePrint(kind, type, id, template)
	{
		appInactive();
		appAddLoader();
		
		var content = oPrint.getContent(kind, type, id, template);
		
		oPrint.displayPrintWindow(content);
		
		appActive();
	}
	
	/**
	 * Return content
	 * 
	 * @param kind
	 * @param type
	 * @param id
	 * @param template
	 * @param options
	 * @return
	 */
	this.getContent = function (kind, type, id, template, options)
	{
		var content = '', prefix;
		
		prefix  = kind.replace('.', '_');
		prefix += '_' + type;

		jQuery.ajax({
		    url: '/Special:OEController',
		    async: false,
		    type: 'POST',
		    data: ({
		    	page_path: OEF_PAGE_PATH,
		    	action: 'printEntity',
		    	aeform: {
		    		kind:     kind,
		    		type:     type,
		    		id:       id,
		    		template: template,
		    		options:  options
		    	}
		    }),
		    dataType: 'json',
		    success: function (data, status)
		    {
				var msg = '';
				
				if (!data['status'])
				{
					for (var index in data['errors'])
					{
						msg += data['errors'][index]+'\n';
					}
				}
				else
				{
					content = data['result']['output'];
				}
				
				if (!msg)
				{
					msg = (data['result'] && data['result']['msg']) ? data['result']['msg'] : (data['status'] ? 'Generated succesfully' : 'Not generated');
				}
				
				displayMessage(prefix, msg, data['status']);
		    }
		});
		
		return content;
	};
	
	/**
	 * Display print window
	 * 
	 * @param string content
	 * @return boolean
	 */
	this.displayPrintWindow = function(content)
	{
		var pWin, doc, txt = '';
		
		if (!OPENED_TO_PRINT[this.kind])
		{
			OPENED_TO_PRINT[this.kind] = {};
		}
		
		if (!OPENED_TO_PRINT[this.kind][this.type] || OPENED_TO_PRINT[this.kind][this.type].closed)
		{
			OPENED_TO_PRINT[this.kind][this.type] = window.open(null, '_blank', 'width=810,height=600,menubar=1,toolbar=0,scrollbars=1');
		}
		
		pWin = OPENED_TO_PRINT[this.kind][this.type];
		
		doc  = pWin.document.open("text/html", "replace");
		
		txt += '<html><body style="padding: 0; margin: 0;">';
		txt += '<table id="oef_print_container" style="width: 100%; background-color: #444444; border: 0 none;"><tr><td align="center">';
		txt += '<table style="width: 0; height: 0; background-color: #FFFFFF;"><tr><td>' + content + '</td></tr></table>';
		txt += '</td></tr></table></body></html>';
		
		doc.write(txt);
		doc.close();
		
		pWin.focus();
	};
}
