{{
  var zone = $0 ?? 'zone_0';
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="https://www.google.com/jsapi"&gt;&lt;/script&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
        jQuery(document).ready(function() {
            var html = '';
            html += '&lt;style&gt;#oef_employee_vacation_days .google-visualization-table-table { background-color: #f8f8f8; }';
            html += '#oef_employee_vacation_days .google-visualization-table-th { background-image: none; background-color: #dfeddc; color: #84aa05; font-weight: bold; }&lt;/style&gt;';
            html += '&lt;div id=\"oef_employee_vacation_days\"&gt;&lt;div id=\"chart\" style=\"float: left; margin-bottom: 10px;\"&gt;&nbsp;&lt;/div&gt;&lt;div id=\"table_chart\" style=\"float: left; margin-left: 9px;\"&gt;&nbsp;&lt;/div&gt;&lt;div style=\"clear:both\"&gt;&nbsp;&lt;/div&gt;&lt;/div&gt;';
            
            jQuery('#"..zone.."').html(html);
        });
        
		Widgets.showWidget({
	        'load': {
		       'solution': 'OiltecIntranet',
		       'service':  'Personal',
		       'method':   'EmployeeVacationDays',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
	        },
	        'view': {
		       'widget': 'EmployeeVacationDays',
		       'tag_id': 'oef_employee_vacation_days',
		       'options': {
		          header: 'Employee Vacations Days',
		          width:  500,
		          chart: {
		             background: 'F8F8F8'
		          },
		          table: {
		             showRowNumber: false,
		             sort: 'disable'
		          }
		       }
	        }
         }); 
      "&lt;/script&gt;
    &lt;/head&gt;
    &lt;body&gt;&lt;/body&gt;
    &lt;tail&gt;&lt;/tail&gt;
  &lt;/html&gt;
}}
