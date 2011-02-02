<!-- Include JS -->
{{
   &lt;html&gt;
     &lt;head&gt;
       <link href="/ext/OEF/AppliedSolutions/AWPAnalytics/MindTouch/CSS/widget.css" media="print" type="text/css" rel="stylesheet">
       &lt;script type="text/javascript" src="https://www.google.com/jsapi"&gt;&lt;/script&gt;
       &lt;script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_widgets.js"&gt;&lt;/script&gt;
       &lt;script type="text/javascript"&gt;"
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Personal',
		       'method':   'EmployeeHours',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Period': 'This Month'
		       }
	        },
	        'view': {
		       'widget':  'Speedometer',
		       'tag_id':  'oef_employee_hours',
		       'options': {}
	        }
         });

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

       "&lt;/script&gt;
     &lt;/head&gt;
     &lt;body&gt;&lt;/body&gt;
     &lt;tail&gt;
     
            &lt;script type="text/javascript"&gt;"
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ProjectOverview',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Project': '00000001'
		       }
	        },
	        'view': {
		       'widget':  'ProjectOverview',
		       'tag_id':  'oef_project_overview',
		       'options': {}
	        }
         });

       "&lt;/script&gt;
     
     &lt;/tail&gt;
   &lt;/html&gt;
}}
<div class="oef_content">
  <h3>Employee Hours</h3>

  <div id="oef_employee_hours"><div id='chart1_div' style="float: left;">&nbsp;</div><div id='chart2_div' style="float: left;">&nbsp;</div><div id='chart3_div' style="float: left;">&nbsp;</div></div>

</div>
<div style="clear:both">

<h3 >Employee Vacations Days</h3>
  <div id="oef_employee_vacation_days"><div id='chart' style="float: left;">&nbsp;</div><div id='table_chart' style="float: left;">&nbsp;</div></div>
    
      <h3>Project Overview</h3>
<div id="oef_project_overview">&nbsp;</div>
</div>