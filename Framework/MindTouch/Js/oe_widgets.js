/**
 * Widgets object constructor
 * 
 * @param object loader
 * @param object viewer
 * @return void
 */
function oeWidgets(loader, viewer)
{
    this.Loader = (typeof loader == "object") && (loader instanceof oeLoader) ? loader : null;
    this.Viewer = (typeof viewer == "object") && (viewer instanceof oeWidgetsView) ? viewer : null;
    this.data   = null;
    
    /**
     * Show widget
     * 
     * @param array parameters
     * @return void
     */
    this.showWidget = function(parameters)
    {
    	// Initialize loader and viewer
    	var config = parameters['conf'];
    	if (config)
    	{
    		if (config['loader'])
    		{
    			this.Loader = eval('new ' + config['loader'] + '()');
    		}
    		
    		if (config['viewer'])
    		{
    			this.Viewer = eval('new ' + config['viewer'] + '()');
    		}
    	}
    	
    	var load_param = parameters['load'];
    	var view_param = parameters['view'];
    	
    	// Load data
    	this.loadData(load_param);
    	
    	// Draw widget
    	this.drawWidget(view_param);
    };
    
    /**
     * Load data
     * 
     * @param array parameters
     * @return array
     */
    this.loadData = function(parameters)
    {
    	this.Loader.load(parameters);
    	
    	if (this.Loader.status == '200')
    	{
    		this.data = this.Loader.getData();
    	}
    	
    	return this.data;
    };
    
    /**
     * Draw widget
     * 
     * @param array parameters
     * @return bool
     */
    this.drawWidget = function(parameters)
    {
    	var method  = 'draw' + parameters['widget'];
    	var view    =  this.Viewer;
    	var tag_id  = parameters['tag_id'];
    	var options = parameters['options'];
    	
    	if (typeof view[method] != 'function')
    	{
    		alert('View method "' + method + '" not exists');
    		
    		return false;
    	}
    	
    	return viewer[method](tag_id, this.data, options);
    };
}


/**
 * Constructor for Loader object
 * 
 * @param object parameters
 * @return void
 */
function oeLoader(parameters)
{
	this.status = '';
	this.data   = null;
	this.errors = null;
	
	/**
	 * Process responce data
	 * 
	 * @param object data
	 * @param string status
	 * @param object loader - oeLoader
	 * @return void
	 */
	this.processResponce = function(data, status, loader)
	{
		if (!loader)
		{
			this.status = status;
		    this.data   = data['result'];
		    this.errors = data['errors'];
		}
		else
		{
			loader.status = status;
			loader.data   = data['result'];
			loader.errors = data['errors'];
		}
	};
	
	/**
	 * Load data (send AJAX request)
	 * 
	 * @param object parameters
	 * @param function callback
	 * @param int maxRequestTime
	 * @return void
	 */
	this.load = function(parameters, callback, maxRequestTime)
	{
		if (!callback) callback = this.processResponce;
		if (!maxRequestTime) maxRequestTime = 1000;
		
		var solution    = parameters['solution'];
		var service     = parameters['service'];
		var method      = parameters['method'];
		var attributes  = parameters['attributes'];
		var auth_header = parameters['authMethod'] ? parameters['authMethod'] : 'MTAuth';
		
		if (!parameters['authtoken'])
		{
			parameters['authtoken'] = getCookie('authtoken');
		}
		
		auth_header += parameters['authtoken'] ? ' ' + parameters['authtoken'] : '';
		
		var uri = 'http://oiltec-odessa.tenet.odessa.ua/webservices/' + solution + '/' + service + '/' + method;
		
		for (var name in attributes)
		{
			uri += '/' + name + '=' + attributes[name];
		}
		
		var loader = this;
		
		jQuery.ajax({
			url: uri,status: 'zxc',
		    async: false,
			type: 'GET',
			cache: false,
			dataType: 'json',
			reqTimeout: null,
			beforeSend: function (xmlhttp)
			{
				xmlhttp.setRequestHeader("OEF-Autorization", auth_header);
				this.reqTimeout = setTimeout(function () { xmlhttp.abort(); }, maxRequestTime);
			},
			success: function (data, status, xmlhttp)
			{
			    clearTimeout(this.reqTimeout);

				callback(data, xmlhttp.status, loader);
			},
		    error: function (XMLHttpRequest, textStatus, errorThrown)
		    {
				//alert('Request error');
				;
		    }
		});
	};
	
	/**
	 * Get data
	 * 
	 * @return object
	 */
	this.getData = function()
	{
		return this.data;
	};
}


/**
 * Constructor for Widgets View 
 * @return
 */
function oeWidgetsView()
{
	this.drawGrid = function(tag_id, data, options)
	{
		jQuery('#' + tag_id).html('<pre>' + data + '</pre>');
	};
}

/**
 * Return cookie value by cookie name
 * 
 * @param string name
 * @return string
 */
function getCookie(name)
{
	var cookie = " " + document.cookie;
	var search = " " + name + "=";
	var setStr = null;
	var offset = 0;
	var end = 0;
	if (cookie.length > 0)
	{
		offset = cookie.indexOf(search);
		if (offset != -1)
		{
			offset += search.length;
			end = cookie.indexOf(";", offset)
			
			if (end == -1) end = cookie.length;
			
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	
	return setStr;
}


var loader  = new oeLoader();
var viewer  = new oeWidgetsView();
var Widgets = new oeWidgets(loader, viewer);