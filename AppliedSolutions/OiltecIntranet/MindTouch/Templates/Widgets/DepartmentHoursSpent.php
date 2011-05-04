{{
  var zone   = $0 ?? 'zone_0';
  var period = $1 ? $1 : 'Today';
  var department = $2 ? $2 : false;
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
        jQuery(document).ready(function() {
           jQuery('#"..zone.."').html('&lt;div id=\"oef_department_hours_spent\"&gt;&nbsp;&lt;/div&gt;');
        });
      "&lt;/script&gt;
    &lt;/head&gt;
    &lt;body&gt;&lt;/body&gt;
    &lt;tail&gt;
      &lt;script type="text/javascript"&gt;"
	    Widgets.showWidget({
	        'load': {
		       'solution': 'OiltecIntranet',
		       'service':  'Pm',
		       'method':   'DepartmentHoursSpent',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
                  "..(period ? '\'Period\': \''..period..'\'' : '').."
                  "..(department ? (period ? ',' : '')..'\'Department\': \''..department..'\'' : '').."
               }
	        },
	        'view': {
		       'widget':  'List',
		       'tag_id':  'oef_department_hours_spent',
		       'options': {
		          header: 'Department hours spent'
		       }
	        }
        });
      "&lt;/script&gt;
    &lt;/tail&gt;
  &lt;/html&gt;
}}
