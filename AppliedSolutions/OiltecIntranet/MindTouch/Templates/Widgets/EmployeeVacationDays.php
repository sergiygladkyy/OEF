{{
  var zone = $0 ?? 'zone_0';
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="https://www.google.com/jsapi"&gt;&lt;/script&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
		Widgets.showWidget({
	        'load': {
		       'solution': 'OiltecIntranet',
		       'service':  'Personal',
		       'method':   'EmployeeVacationDays',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
	        },
	        'view': {
		       'widget':  'EmployeeVacationDays',
		       'tag_id':  'oef_employee_vacation_days',
		       'options': {}
	        }
         });

         jQuery(document).ready(function() { jQuery('#"..zone.."').html('&lt;div class=\"oef_content\"&gt; &lt;h3 style=\"clear:both\"&gt;Employee Vacations Days&lt;/h3&gt;&lt;div id=\"chart\" style=\"float: left;\"&gt;&nbsp;&lt;/div&gt;&lt;div id=\"table_chart\" style=\"float: left;\"&gt;&nbsp;&lt;/div&gt;&lt;/div&gt;'); });
      "&lt;/script&gt;
    &lt;/head&gt;
    &lt;body&gt;&lt;/body&gt;
    &lt;tail&gt;&lt;/tail&gt;
  &lt;/html&gt;
}}
