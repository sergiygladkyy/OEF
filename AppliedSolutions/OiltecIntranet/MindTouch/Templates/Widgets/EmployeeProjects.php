var zone = $0?? 'zone_0' ;
var dateD = $1?? '' ;
<html>
  <head>
    <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"></script>
    <script type="text/javascript">
      "jQuery(document).ready(function() { jQuery('#"..zone.."').html('<div class=\"oef_content\"><h3 style=\"clear:both\">Employee projects</h3><div id=\"oef_employee_projects\">&nbsp;</div></div>'); });"
    </script>
  </head>
  <body></body>
  <tail>
    <script type="text/javascript">
	  "Widgets.showWidget({
	        'load': {
		       'solution': 'OiltecIntranet',
		       'service':  'Personal',
		       'method':   'EmployeeProjects',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Date': '"..dateD.."'
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_employee_projects',
		       'options': {}
	        }
      });"
    </script>
  </tail>
</html>