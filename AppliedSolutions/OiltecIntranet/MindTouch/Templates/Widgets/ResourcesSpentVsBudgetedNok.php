{{
  var zone    = $0 ?? 'zone_0';
  var project = $1 ? $1 : false;
  var dateD   = $2 ?? false;
  
  &lt;html&gt;
    &lt;head&gt;
      &lt;script type="text/javascript" src="https://www.google.com/jsapi"&gt;&lt;/script&gt;
      &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
        jQuery(document).ready(function() {
           jQuery('#"..zone.."').html('&lt;div class=\"oef_content\"&gt;&lt;h3 style=\"clear:both\"&gt;ResourcesSpentVsBudgeted NOK&lt;/h3&gt;&lt;div id=\"oef_resources_spent_vs_budgeted_nok\"&gt;&nbsp;&lt;/div&gt;&lt;/div&gt;');
        });
      "&lt;/script&gt;
      &lt;script type="text/javascript"&gt;"
	    Widgets.showWidget({
	        'load': {
		       'solution': 'OiltecIntranet',
		       'service':  'Pm',
		       'method':   'ResourcesSpentVsBudgeted',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		           'ResourceKind': 'NOK'
		           "..(project ? ',\'Project\': \''..project..'\'' : '').."
                   "..(dateD   ? ',\'Date\': \''..dateD..'\'' : '').."
		       }
	        },
	        'view': {
		       'widget':  'ColumnChart',
		       'tag_id':  'oef_resources_spent_vs_budgeted_nok',
		       'options': {
		          width:  400,
		          height: 240,
		          title: 'Project "..project.."',
                  hAxis: {title: 'Date', titleTextStyle: {color: 'red'}},
                  vAxis: {title: 'NOK', titleTextStyle: {color: 'red'}, minValue: 0}
               }
	        }
        });
      "&lt;/script&gt;
    &lt;/head&gt;
    &lt;body&gt;&lt;/body&gt;
    &lt;tail&gt;&lt;/tail&gt;
  &lt;/html&gt;
}}
