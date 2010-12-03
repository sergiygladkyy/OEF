<!-- Include JS -->
{{
   &lt;html&gt;
     &lt;head&gt;
       &lt;script type="text/javascript" src="https://www.google.com/jsapi"&gt;&lt;/script&gt;
       &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
       &lt;script type="text/javascript"&gt;"
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ProjectCost',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Project': '263'
		       }
	        },
	        'view': {
		       'widget':  'ColumnChart',
		       'tag_id':  'oe_widget_3',
		       'options': {
				   width: 400,
				   height: 240,
				   title: 'Project cost',
				   hAxis: {title: 'Date', titleTextStyle: {color: 'red'}}
			   }
	        }
         });
       "&lt;/script&gt;
     &lt;/head&gt;
     &lt;body&gt;&lt;/body&gt;
     &lt;tail&gt;
       &lt;script type="text/javascript"&gt;"
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'UserProjects',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {}
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oe_widget_1',
		       'options': {}
	        }
         });
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ProjectMembers',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Project': '263'
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oe_widget_2',
		       'options': {}
	        }
         });
       "&lt;/script&gt;
     &lt;/tail&gt;
   &lt;/html&gt;
}}
<div class="oef_content">
  <h3>UserProjects</h3>
  <div id="oe_widget_1">&nbsp;</div>
  <h3>ProjectMembers</h3>
  <div id="oe_widget_2">&nbsp;</div>
  <h3>ProjectCost</h3>
  <div id="oe_widget_3">&nbsp;</div>
</div>
