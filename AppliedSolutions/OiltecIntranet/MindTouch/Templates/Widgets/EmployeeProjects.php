{{
  var zone  = $0 ?? 'zone_0';
  var dateD = $1 ?? false;
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
        jQuery(document).ready(function() {
           jQuery('#"..zone.."').html('&lt;div id=\"oef_employee_projects\"&gt;&nbsp;&lt;/div&gt;');
        });
      "&lt;/script&gt;
    &lt;/head&gt;
    &lt;body&gt;&lt;/body&gt;
    &lt;tail&gt;
      &lt;script type="text/javascript"&gt;"
	    Widgets.showWidget({
	        'load': {
		       'solution': 'OiltecIntranet',
		       'service':  'Personal',
		       'method':   'EmployeeProjects',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          "..(dateD ? '\'Date\': \''..dateD..'\'' : '').."
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_employee_projects',
		       'options': {
		          header: 'Employee projects'
		       }
	        }
        });
      "&lt;/script&gt;
    &lt;/tail&gt;
  &lt;/html&gt;
}}
