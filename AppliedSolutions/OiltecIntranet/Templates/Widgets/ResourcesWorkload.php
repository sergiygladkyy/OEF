var zone = $0?? 'zone_0' ;
var period = $1?? 'this week' ;
var department = $2?? '' ;
<html> <head><link href="/ext/OEF/AppliedSolutions/AWPAnalytics/MindTouch/CSS/widget.css" media="print" type="text/css" rel="stylesheet"/> <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js">
</script><script type="text/javascript">
	"jQuery(document).ready(function() { jQuery('#"..zone.."').html('<div class=\"oef_content\"><h3 style=\"clear:both\">Resources workload</h3><div id=\"oef_resources_workload\">&nbsp;</div></div>'); });"
</script> </head> <body></body><tail>
<script type="text/javascript">
	"Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ResourcesWorkload',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Period': '"..period.."',
		          'Department': '"..department.."'
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_resources_workload',
		       'options': {}
	        }
         });"
</script> </tail> </html>
	