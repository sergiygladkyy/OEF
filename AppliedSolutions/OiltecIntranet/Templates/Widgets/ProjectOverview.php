var zone = $0?? 'zone_0' ;
var project = $1?? '0' ;
<html>
  <head>
    <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"></script>
    <script type="text/javascript">
      "jQuery(document).ready(function() { jQuery('#"..zone.."').html('<div class=\"oef_content\"><h3 style=\"clear:both\">Project Overview</h3><div id=\"oef_project_overview\">&nbsp;</div></div>'); });"
    </script>
  </head>
  <body></body>
  <tail>
    <script type="text/javascript">
	  "Widgets.showWidget({
        'load': {
	       'solution': 'OiltecIntranet',
           'service': 'Pm', 'method': 'ProjectOverview',
           'authMethod': 'MTAuth',
           'authtoken' : '"..user.authtoken.."',
           'attributes': {
              'Project': '"..project.."'
           }
        },
		'view': {
			'widget': 'ProjectOverview',
			'tag_id': 'oef_project_overview',
			'options': {}
        }
      });"
    </script>
  </tail>
</html>
