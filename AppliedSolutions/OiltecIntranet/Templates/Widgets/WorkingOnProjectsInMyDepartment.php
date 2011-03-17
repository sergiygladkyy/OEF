var zone = $0?? 'zone_0' ;
var dateD = $1?? '' ;
var department = $2?? '' ;
<html> <head><link href="/ext/OEF/AppliedSolutions/AWPAnalytics/MindTouch/CSS/widget.css" media="print" type="text/css" rel="stylesheet"/> <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js">
</script><script type="text/javascript">
	"jQuery(document).ready(function() { jQuery('#"..zone.."').html('<div class=\"oef_content\"><h3 style=\"clear:both\">Working on projects in my department</h3><div id=\"oef_working_on_projects_in_my_department\">&nbsp;</div></div>'); });"
</script> </head> <body></body><tail>
<script type="text/javascript">
	"Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'WorkingOnProjectsInMyDepartment',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Date': '"..dateD.."',
		          'Department': '"..department.."'
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_working_on_projects_in_my_department',
		       'options': {}
	        }
         });"
</script> </tail> </html>