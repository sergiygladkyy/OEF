{{
  var zone  = $0 ?? 'zone_0';
  var dateD = $1 ?? false;
  var department = $2 ? $2 : false;
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
	    jQuery(document).ready(function() {
		   jQuery('#"..zone.."').html('&lt;div id=\"oef_projects_ongoing\"&gt;&nbsp;&lt;/div&gt;');
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
		       'method':   'ProjectsOngoing',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
                  "..(dateD ? '\'Date\': \''..dateD..'\'' : '').."
                  "..(department ? (dateD ? ',' : '')..'\'Department\': \''..department..'\'' : '').."
               }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_projects_ongoing',
		       'options': {
		          header: 'Projects ongoing'
		       }
	        }
        });
      "&lt;/script&gt;
    &lt;/tail&gt;
  &lt;/html&gt;
}}
