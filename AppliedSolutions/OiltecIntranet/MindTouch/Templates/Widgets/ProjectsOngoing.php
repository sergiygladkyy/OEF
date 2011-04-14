var zone = $0?? 'zone_0' ;
var dateD = $1?? '' ;
var department = $2?? '' ;
<html>
  <head>
    <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"></script>
    <script type="text/javascript">
	  "jQuery(document).ready(function() { jQuery('#"..zone.."').html('<div class=\"oef_content\"><h3 style=\"clear:both\">Projects ongoing</h3><div id=\"oef_projects_ongoing\">&nbsp;</div></div>'); });"
    </script>
  </head>
  <body></body>
  <tail>
    <script type="text/javascript">
	  "Widgets.showWidget({
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
      });"
    </script>
  </tail>
</html>
