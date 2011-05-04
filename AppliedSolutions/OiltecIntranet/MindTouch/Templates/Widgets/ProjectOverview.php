{{
  var zone    = $0 ?? 'zone_0';
  var project = $1 ? $1 : false;
  var dateD   = $2 ?? false;
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
        jQuery(document).ready(function() {
           jQuery('#"..zone.."').html('&lt;div id=\"oef_project_overview\"&gt;&nbsp;&lt;/div&gt;');
        });
      "&lt;/script&gt;
    &lt;/head&gt;
    &lt;body&gt;&lt;/body&gt;
    &lt;tail&gt;
      &lt;script type="text/javascript"&gt;"
	    Widgets.showWidget({
          'load': {
	         'solution': 'OiltecIntranet',
             'service': 'Pm', 'method': 'ProjectOverview',
             'authMethod': 'MTAuth',
             'authtoken' : '"..user.authtoken.."',
             'attributes': {
                "..(project ? '\'Project\': \''..project..'\'' : '').."
                "..(dateD ? (project ? ',' : '')..'\'Date\': \''..dateD..'\'' : '').."
             }
          },
		  'view': {
			  'widget': 'ProjectOverview',
			  'tag_id': 'oef_project_overview',
			  'options': {
			     header: 'Project Overview'
			  }
          }
        });
      "&lt;/script&gt;
    &lt;/tail&gt;
  &lt;/html&gt;
}}
