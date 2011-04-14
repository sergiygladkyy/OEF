var zone = $0?? 'zone_0' ;
var period = $1?? 'This Month' ;
<html>
  <head>
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"></script>
    <script type="text/javascript">
      "Widgets.showWidget({
        'load': {
            'solution': 'OiltecIntranet',
            'service': 'Personal',
            'method': 'EmployeeHours',
            'authMethod': 'MTAuth',
            'authtoken' : '"..user.authtoken.."',
            'attributes':
            {
               'Period': '"..period.."'
            }
        },
        'view': {
            'widget': 'Speedometer',
            'tag_id': 'oef_employee_hours',
            'options': {}
        }
      });
      jQuery(document).ready(function() { jQuery('#"..zone.."').html('<div class=\"oef_content\"><h3 style=\"clear:both\">Employee Hours</h3><div id=\"chart1_div\" style=\"float: left;\">&nbsp;</div><div id=\"chart2_div\" style=\"float: left;\">&nbsp;</div><div id=\"chart3_div\" style=\"float: left;\">&nbsp;</div><div style=\"clear:both\">&nbsp;</div></div>'); });
    "</script>
  </head>
  <body></body>
  <tail></tail>
</html>
 