{{
  var zone  = $0 ?? 'zone_0';
  var dateD = $1 ?? '';
  var department = $2 ?? '';
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
	    jQuery(document).ready(function() {
		   jQuery('#"..zone.."').html('&lt;div class=\"oef_content\"&gt;&lt;h3 style=\"clear:both\"&gt;Projects ongoing&lt;/h3&gt;&lt;div id=\"oef_projects_ongoing\"&gt;&nbsp;&lt;/div&gt;&lt;/div&gt;');
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
		          'Date': '"..dateD.."',
		          'Department': '"..department.."'
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_projects_ongoing',
		       'options': {}
	        }
        });
      "&lt;/script&gt;
    &lt;/tail&gt;
  &lt;/html&gt;
}}
