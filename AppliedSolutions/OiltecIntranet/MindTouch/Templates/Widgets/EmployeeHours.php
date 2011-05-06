{{
  var zone   = $0 ?? 'zone_0';
  var period = $1 ? $1 : 'This Month';
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="https://www.google.com/jsapi"&gt;&lt;/script&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
        jQuery(document).ready(function() {
           var html;
           html  = '&lt;div id=\"oef_employee_hours\"&gt;';
           html += '&lt;div id=\"hours_div\" style=\"float: left;\"&gt;&nbsp;&lt;/div&gt;';
           html += '&lt;div id=\"overtime_div\" style=\"float: left;\"&gt;&nbsp;&lt;/div&gt;';
           html += '&lt;div id=\"extra_div\" style=\"float: left;\"&gt;&nbsp;&lt;/div&gt;';
           html += '&lt;div style=\"clear:both\"&gt;&nbsp;&lt;/div&gt;&lt;/div&gt;';
           
           jQuery('#"..zone.."').html(html);
        });
        
        Widgets.showWidget({
          'load': {
              'solution': 'OiltecIntranet',
              'service':  'Personal',
              'method':   'EmployeeHours',
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
              'options': {
                 header: 'Employee Hours',
                 width:  510,
                 hours: {
                    width:  170,
                    height: 170,
                    minorTicks: 7,
                    label: 'Worked'
                 },
                 overtime: {
                    width:  170,
                    height: 170,
                    minorTicks: 7,
                    label: 'Overtime'
                 },
                 extra: {
                    width:  170,
                    height: 170,
                    minorTicks: 7,
                    label: 'Extra'
                 }
              }
          }
        });
        
        
      "&lt;/script&gt;
    &lt;/head&gt;
    &lt;body&gt;&lt;/body&gt;
    &lt;tail&gt;&lt;/tail&gt;
  &lt;/html&gt;
}}
