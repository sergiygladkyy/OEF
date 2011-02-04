var zone = $0?? 'zone_0' ;
var project = $1?? '0' ;
<html> <head><link href="/ext/OEF/AppliedSolutions/AWPAnalytics/MindTouch/CSS/widget.css" media="print" type="text/css" rel="stylesheet"/> <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js">
</script><script type="text/javascript">
	"jQuery(document).ready(function() { jQuery('#"..zone.."').html('<div class=\"oef_content\"><h3 style=\"clear:both\">Project delivery dates</h3><div id=\"oef_project_milestones\">&nbsp;</div></div>'); });"
</script> </head> <body></body><tail>
<script type="text/javascript">
	"Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ProjectMilestones',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Project': '"..project.."'
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_project_milestones',
		       'options': {}
	        }
         });"
</script> </tail> </html>