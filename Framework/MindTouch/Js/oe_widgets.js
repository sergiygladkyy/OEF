if (!OEF_WIDGET_NUM) var OEF_WIDGET_NUM = 0;

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
    this.errors = null;
    
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
    		this.data   = this.Loader.getData();
    		this.errors = this.Loader.getErrors();
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
    	// Draw Layout
    	var view    = this.Viewer;
    	var options = parameters['options'];
    	var tag_id  = parameters['tag_id'];
    	
    	if (typeof view.drawLayout == 'function')
    	{
    		tag_id = view.drawLayout(tag_id, options);
    	}
    	
    	// Draw widget content
    	if (this.errors)
    	{
    		var errors = '<ul class="oe_widget_errors">';
    		
    		for (var i in  this.errors)
    		{
    			errors += '<li>' + this.errors[i]+ '</li>';
    		}
    		
    		jQuery('#' + tag_id).html(errors + '</ul>');
    		
    		return false;
    	}
    	
    	var method = 'draw' + parameters['widget'];
    	
    	if (typeof view[method] != 'function')
    	{
    		alert('View method "' + method + '" not exists');
    		
    		return false;
    	}
    	
    	return view[method](tag_id, this.data, options);
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
		    this.errors = data['status'] ? null : data['errors'];
		}
		else
		{
			loader.status = status;
			loader.data   = data['result'];
			loader.errors = data['status'] ? null : data['errors'];
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
			url: uri,
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
	
	/**
	 * Get errors
	 * 
	 * @return object
	 */
	this.getErrors = function()
	{
		return this.errors;
	};
}


/**
 * Constructor for Widgets View 
 * @return
 */
function oeWidgetsView()
{
	/**
	 * Draw layout
	 * 
	 * @param string tag_id
	 * @param array  options
	 * @return tag_id
	 */
	this.drawLayout = function(tag_id, options)
	{
		var header = '&nbsp;';
		var style  = '', html = '';
		var wTagId = 'oef_w' + (OEF_WIDGET_NUM++) + '_content';
		
		if (options)
		{
			if (options.header) header = options.header;
			if (options.width)  style += 'width: ' + (options.width  /*+ 20*/) + 'px;';
			if (options.height) style += 'height:' + (options.height /*+ 42*/) + 'px;';
		}
		
		html += '<div class="oef_w_layout">\n';
		html += '  <b class="rnd_modtitle"><b class="rnd1"></b><b class="rnd2"></b><b class="rnd3"></b></b>\n';
		html += '  <div class="modtitle">\n';
		html += '    <h2 class="lnk"><span class="modtitle_text">' + header + '</span></h2>\n';
		html += '    <div style="display: block;">\n';
		html += '      <a class="v2enlargebox" href="javascript:void(0);"></a>\n';
		html += '      <a class="v2ddbox" href="javascript:void(0);"></a>\n';
		html += '    </div>\n';
		html += '    <div style="clear: both;"></div>\n';
		html += '  </div>\n';
		html += '  <div class="modboxin">\n';
		html += '    <div class="oef_w_container">\n';
		html += '      <div id="' + wTagId + '" class="oef_w_content" style="' + style + '">%%content%%</div>\n';
		html += '    </div>\n';
		html += '  </div>\n';
		html += '  <b class="rnd_modboxin"><b class="rnd3"></b><b class="rnd2"></b><b class="rnd1"></b></b>\n';
		html += '</div>\n';
		
		if (jQuery('#' + tag_id).size() == 0) // For Google widgets
		{
			jQuery(document).ready(function() {
				insertLayout(tag_id, html);
			});
		}
		else insertLayout(tag_id, html);
		
		return wTagId;
	};
	
	/**
	 * Insert layout
	 * 
	 * @param string tag_id
	 * @param string layout
	 * @return void
	 */
	function insertLayout(tag_id, layout)
	{
		var content = jQuery('#' + tag_id).html();
		
		layout = layout.replace('%%content%%', content);
		
		jQuery('#' + tag_id).html(layout);
	}
	
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
		
		var html = createGrid(data);
		
		jQuery('#' + tag_id).html(html);
	};
	
	/**
	 * Grid
	 *
	 * @param array data
	 * @return void
	 */
	function createGrid(data)
    {
		if (!data || !data['fields'])
		{
			return "";
		}
		var list   = data['list']  ? data['list']  : [];
		var links  = data['links'] ? data['links'] : [];
		var fields = data['fields'];
		var numb_f = 0;
		var html   = '<table class="oef_widget_grid">\n<thead>\n\t<tr>';

		for (var key in fields)
		{
			html += "\n\t\t<th>" + fields[key] + "</th>";
			numb_f++;
		}

		html += '\n\t</tr>\n</thead>\n<tbody>';
		
		var _class, cnt = 0;

		for (var key in list)
		{
			_class = (cnt++)%2 == 0 ? 'even' : 'uneven';
			
			var row = list[key];
			
			html += "\n\t<tr>";

			for (var i = 0; i < numb_f; i++)
			{
				var name = fields[i];
				var text = row[i];

				if (links[name] && links[name][text])
				{
					text = links[name][text]['text'];
				}

				html += '\n\t\t<td class="' + _class + '">' + text + '</td>';
			}

			html += "\n\t</tr>";
		}

		html += '\n</tbody>\n</table>';
		
		return html;
	}
	
    /**
     * List of pair - key: value
     *
     */
    function createList(data)
    {
    	if (!data)
    	{
    		return "";
    	}
    	var list = data['list'] ? data['list'] : [];
    	var html = '<table class="oef_widget_list">\n';
    	
    	for (var key in list)
    	{
    		var row = list[key];
    		
    		html += "\n\t<tr>";
    		
    		for (var key2 in row)
    		{
    			var value = row[key2];
    			html += '<td class="oef_widget_list_label">\n';
    			
    			if (value['label'] != undefined)
    			{
    				html += value['label'];
    			}
    			
    			html += '\n\t</td>';
    			html += '<td class="oef_widget_list_value">\n';
    			
    			if ( value['value']!= undefined)
    			{
    				html += value['value'];
    			}
    			
    			html +="\n\t</td>";
    		}
    		
    		html += "\n\t</tr>";
    	}
    	
    	html += '\n</table>';
    	
    	return html;
    }
    
    /**
	 * Project Overview
	 * 
	 * @param string tag_id
	 * @param array data
	 * @param array options
	 * @return void
	 */
    this.drawProjectOverview = function(tag_id, data, options)
    {
    	if (!data)
    	{
    		jQuery('#' + tag_id).html('<span>Data is empty</span>');
    		return;
    	}
    	
    	var html = createList(data['ProjectOverview']);
    	html += '<div style="height: 0px; margin-bottom: 20px;">&nbsp;</div>';
    	html += createGrid(data['Employees']);
    	html += '<div style="height: 0px; margin-bottom: 20px;">&nbsp;</div>';
    	html += createGrid(data['Milestones']);
    	
    	jQuery('#' + tag_id).html(html);
    };
    
	/**
	 * Draw List
	 * 
	 * @param string tag_id
	 * @param array data
	 * @param array options
	 * @return void
	 */
	this.drawList = function(tag_id, data, options)
	{
		if (!data)
		{
			jQuery('#' + tag_id).html('<span>Data is empty</span>');
			return;
		}
		
		var html = createList(data);
		
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
	 * Speedometer
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

                if(data['Extra'])
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
        };
        
        /**
		 * Employee Vacation Days
		 *
		 * @param string tag_id
		 * @param array data
		 * @param array options
		 * @return void
		 */
        this.drawEmployeeVacationDays = function(tag_id, data, options)
        {
              if (!data )
              {
                   jQuery('#' + tag_id).html('<span>Data is empty</span>');
                  return;
              }
              var all = data['daysEligible']  ? data['daysEligible']  : 24;
              var actual = data['daysAccounted']  ? data['daysAccounted']  : 0;
              var spent = data['daysSpent']  ? data['daysSpent']  : 0;
              var vacationDate = data['nextMondayVacationEnds'] ? data['nextMondayVacationEnds'] : "undefined " ;
              //var difference = all - actual;
              var leftColor = 'FF9900';
              var rightColor = '000000';
              var gradient = '000000,';
              var coef = all/24;
              for(var i=0;i<25;i++)
              {
                  if(i>0)
                    gradient+='|';
                  if(i*coef>actual)
                      gradient+=rightColor;
                  else
                      gradient+=leftColor;
              }
              google.load('visualization', '1', {packages:['imagechart']});
              google.setOnLoadCallback(function ()
              {
                  var dataTable = new google.visualization.DataTable();
                  dataTable.addRows(1);

                  dataTable.addColumn('number');
                  dataTable.setValue(0, 0, spent%2?spent:spent+1);;

                  var vis = new google.visualization.ImageChart(document.getElementById('chart'));
                  var options = {
                     chxl: '0:|0|' + all,
                     chxp: '0,0,' + all,
                     chxr: '0,0,' + all,
                     chxs: '',
                     chxtc: '',
                     chxt: 'y',
                     chs: '200x100',
                     cht: 'gm',
                     chco: gradient,//'000000,FF9900|FF9900|FF9900|FF9900|FF9900|FF9900|FF9900|FF9900|FF9900|FF9900|FF9900|FF9900|000000|000000|000000|000000|000000|000000|000000|000000|000000|000000|000000|000000|000000',
                     chds: '0,' + all,
                     chd: 't:' + spent,
                   //   chdl: '',
                     chl: spent,
                     chma: '5'
                    };
                   vis.draw(dataTable, options);

                   
                   
              })
              google.load('visualization', '1', {packages:['table']});
              google.setOnLoadCallback(function ()
              {
                   var data = new google.visualization.DataTable();
                   data.addColumn('string', 'Name');
                   data.addColumn('string', 'Value');

                   data.addRows(4);
                   data.setCell(0, 0, 'All time ');
                   data.setCell(0, 1,"" + all);
                   data.setCell(1, 0, 'Actual');
                   data.setCell(1, 1, "" + actual);
                   data.setCell(2, 0, 'Spent');
                   data.setCell(2, 1, "" + spent);
                   data.setCell(3, 0, 'Next monday vacation ends');
                   data.setCell(3, 1, vacationDate);

                   var table = new google.visualization.Table(document.getElementById('table_chart'));
                   table.draw(data, {showRowNumber: true});
              })
              
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
