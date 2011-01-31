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
		
		var uri = 'http://halley/webservices/' + solution + '/' + service + '/' + method;
		
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
	/**
	 * Grid
	 * 
	 * @param string tag_id
	 * @param array data
	 * @param array options
	 * @return void
	 */
	this.drawGrid = function(tag_id, data, options)
	{
		if (!data || !data['fields'])
		{
			jQuery('#' + tag_id).html('<span>Data is empty</span>');
			return;
		}
		var list   = data['list']  ? data['list']  : [];
		var links  = data['links'] ? data['links'] : [];
		var fields = data['fields'];
		var numb_f = 0;
		var html   = "<table>\n<thead>\n\t<tr>";
		
		for (var key in fields)
		{
			html += "\n\t\t<th>" + fields[key] + "</th>";
			numb_f++;
		}
		
		html += '\n\t</tr>\n</thead>\n<tbody>';
		
		for (var key in list)
		{
			var row = list[key];
			
			html += "\n\t<tr>";
			
			for (var i = 0; i < numb_f; i++)
			{
				var name = fields[i];
				var text = row[name];
				
				if (links[name] && links[name][text])
				{
					text = links[name][text]['text'];
				}
				
				html += "\n\t\t<td>" + text + "</td>";
			}
			
			html += "\n\t</tr>";
		}
		
		html += '\n</tbody>\n</table>';
		
		jQuery('#' + tag_id).html(html);
	};
	
	/**
	 * Column Chart
	 * 
	 * @param string tag_id
	 * @param array data
	 * @param array options
	 * @return void
	 */
	this.drawColumnChart = function(tag_id, data, options)
	{
		if (!data || !data['fields'])
		{
			jQuery('#' + tag_id).html('<span>Data is empty</span>');
			return;
		}
		var list   = data['list']  ? data['list']  : [];
		var links  = data['links'] ? data['links'] : [];
		var fields = data['fields'];
		
		google.load("visualization", "1", {packages:["corechart"]});
		google.setOnLoadCallback(function () {
			var gdata  = new google.visualization.DataTable();
			var numb_f = 0;
			var rows   = 0;
			
			for (var field in fields)
			{
				gdata.addColumn(fields[field]['type'], field);
				numb_f++;
			}
			
			for (var key in list)
			{
				gdata.addRows(1);
				
				for (var i = 0; i < numb_f; i++)
				{
					gdata.setValue(rows, i, list[key][i]);
				}
				
				rows++;
			}
			
			var chart = new google.visualization.ColumnChart(document.getElementById(tag_id));
			chart.draw(gdata, options);
		});
	};
        /**
	 * Column Chart
	 * 
	 * @param string tag_id
	 * @param array data
	 * @param array options
	 * @return void
	 */
        this.drawSpeedometer = function(tag_id, data, options)
        {
              if (!data )
              {
                   jQuery('#' + tag_id).html('<span>Data is empty</span>');
                  return;
              }

              var max1 = data['Hours']['max']  ? data['Hours']['max']  : 100;
              var max2 = data['Overtime']['max']  ? data['Overtime']['max']  : 100;
              var max3 = data['Extra']['max']  ? data['Extra']['max']  : 100;

              var val1 = data['Hours']['actual']  ? data['Hours']['actual']  : 0;
              var val2 = data['Overtime']['actual']   ? data['Overtime']['actual']  : 0;
              var val3 = data['Extra']['actual']   ? data['Extra']['actual']  : 0;

              google.load('visualization', '1', {packages:['gauge']});
              google.setOnLoadCallback(function () {
                if(data['Hours'])
                {
                    var data1 = new google.visualization.DataTable();
                    data1.addColumn('string', 'Label');
                    data1.addColumn('number', 'Value');
                    data1.addRows(1);
                    data1.setValue(0, 0, 'HRS');
                    data1.setValue(0, 1, val1);
                    var chart1 = new google.visualization.Gauge(document.getElementById('chart1_div'));
                    var redLine = max1;
                    if(val1>max1)
                    {
                        max1 = val1;
                    }
                    var options = {width: 200, height: 200,minorTicks: 7, max: max1,
                        redFrom: redLine, redTo: max1};
                    chart1.draw(data1, options);
                }
                if(data['Overtime'])
                {
                    var data2 = new google.visualization.DataTable();
                    data2.addColumn('string', 'Label');
                    data2.addColumn('number', 'Value');
                    data2.addRows(1);
                    data2.setValue(0, 0, 'Overtime');
                    data2.setValue(0, 1, val2);
                    
                    var chart2 = new google.visualization.Gauge(document.getElementById('chart2_div'));
                    var options2 = {width: 200, height: 200,minorTicks: 7, max: max2};
                    chart2.draw(data2, options2);
                }

                if(data['Overtime'])
                {
                    var data3 = new google.visualization.DataTable();
                    data3.addColumn('string', 'Label');
                    data3.addColumn('number', 'Value');
                    data3.addRows(1);
                    data3.setValue(0, 0, 'Extra');
                    data3.setValue(0, 1, val3);

                    var chart3 = new google.visualization.Gauge(document.getElementById('chart3_div'));
                    var options3 = {width: 200, height: 200,minorTicks: 7, max: max3};
                    chart3.draw(data3, options3);
                }
              });
              /*var html   = "<div id='chart1_div'></div><div id='chart2_div'></div><div id='chart3_div'></div>";
              jQuery('#' + tag_id).html(html);*/
        }
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
			end = cookie.indexOf(";", offset);
			
			if (end == -1) end = cookie.length;
			
			setStr = unescape(cookie.substring(offset, end));
		}
	}
	
	return setStr;
}


var loader  = new oeLoader();
var viewer  = new oeWidgetsView();
var Widgets = new oeWidgets(loader, viewer);
