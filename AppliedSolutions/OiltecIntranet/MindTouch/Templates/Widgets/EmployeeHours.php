{{
  var zone   = $0 ?? 'zone_0' ;
  var period = $1 ?? 'This Month' ;
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="https://www.google.com/jsapi"&gt;&lt;/script&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
        Widgets.showWidget({
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
        
        var html;
        html  = '&lt;div class=\"oef_content\"&gt;&lt;h3 style=\"clear:both\"&gt;Employee Hours&lt;/h3&gt;';
        html += '&lt;div id=\"chart1_div\" style=\"float: left;\"&gt;&nbsp;&lt;/div&gt;';
        html += '&lt;div id=\"chart2_div\" style=\"float: left;\"&gt;&nbsp;&lt;/div&gt;';
        html += '&lt;div id=\"chart3_div\" style=\"float: left;\"&gt;&nbsp;&lt;/div&gt;';
        html += '&lt;div style=\"clear:both\"&gt;&nbsp;&lt;/div&gt;&lt;/div&gt;';
        
        jQuery(document).ready(function() { jQuery('#"..zone.."').html(html); });
      "&lt;/script&gt;
    &lt;/head&gt;
    &lt;body&gt;&lt;/body&gt;
    &lt;tail&gt;&lt;/tail&gt;
  &lt;/html&gt;
}}
