{{
  var zone   = $0 ?? 'zone_0';
  var period = $1 ? $1 : 'This Week';
  var department = $2 ? $2 : false;
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
        jQuery(document).ready(function() {
           jQuery('#"..zone.."').html('&lt;div id=\"oef_resources_workload\"&gt;&nbsp;&lt;/div&gt;');
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
		       'method':   'ResourcesWorkload',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
                  "..(period ? '\'Period\': \''..period..'\'' : '').."
                  "..(department ? (period ? ',' : '')..'\'Department\': \''..department..'\'' : '').."
               }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_resources_workload',
		       'options': {
		          header: 'Resources workload'
		       }
	        }
        });
      "&lt;/script&gt;
    &lt;/tail&gt;
  &lt;/html&gt;
}}
