{{
  var zone  = $0 ?? 'zone_0';
  var dateD = $1 ?? false;
  var department = $2 ? $2 : false;
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
        jQuery(document).ready(function() {
           jQuery('#"..zone.."').html('&lt;div id=\"oef_working_on_projects_in_my_department\"&gt;&nbsp;&lt;/div&gt;');
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
		       'method':   'WorkingOnProjectsInMyDepartment',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
                  "..(dateD ? '\'Date\': \''..dateD..'\'' : '').."
                  "..(department ? (dateD ? ',' : '')..'\'Department\': \''..department..'\'' : '').."
               }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_working_on_projects_in_my_department',
		       'options': {
		          header: 'Working on projects in my department'
		       }
	        }
        });
      "&lt;/script&gt;
    &lt;/tail&gt;
  &lt;/html&gt;
}}
