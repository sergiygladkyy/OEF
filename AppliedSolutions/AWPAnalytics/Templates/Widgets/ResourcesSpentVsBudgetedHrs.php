var zone = $0?? 'zone_0' ;
var project = $1?? '0' ;
<html> <head> <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js">
</script><script type="text/javascript">
	"jQuery(document).ready(function() { jQuery('#"..zone.."').html('<div class=\"oef_content\"><h3 style=\"clear:both\">ResourcesSpentVsBudgeted HRS</h3><div id=\"oef_resources_spent_vs_budgeted_hrs\">&nbsp;</div></div>'); });"
</script>
<script type="text/javascript">
	"Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ResourcesSpentVsBudgeted',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Project': '"..project.."'
		       }
	        },
	        'view': {
		       'widget':  'ColumnChart',
		       'tag_id':  'oef_resources_spent_vs_budgeted_hrs',
		       'options': {
		          width:  400,
		          height: 240,
		          title: 'Project "..project.."',
                  hAxis: {title: 'Date', titleTextStyle: {color: 'red'}},
                  vAxis: {title: 'HRS', titleTextStyle: {color: 'red'}, minValue: 0}
               }
	        }
         });	"
</script> </head> <body></body><tail> </tail> </html>