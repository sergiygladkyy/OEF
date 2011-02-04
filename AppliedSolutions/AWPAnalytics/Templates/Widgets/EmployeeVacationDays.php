var zone = $0?? 'zone_0' ;
		<html> <head> <script type="text/javascript" src="https://www.google.com/jsapi"></script> <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"></script> <script type="text/javascript">"
		Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Personal',
		       'method':   'EmployeeVacationDays',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
	        },
	        'view': {
		       'widget':  'EmployeeVacationDays',
		       'tag_id':  'oef_employee_vacation_days',
		       'options': {}
	        }
         });

         jQuery(document).ready(function() { jQuery('#"..zone.."').html('<div class=\"oef_content\"> <h3 style=\"clear:both\">Employee Vacations Days</h3><div id=\"chart\" style=\"float: left;\">&nbsp;</div><div id=\"table_chart\" style=\"float: left;\">&nbsp;</div></div>'); });
         	 	 "</script> </head> <body></body> <tail> </tail> </html>



