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
         
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ResourcesSpentVsBudgeted',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Project': '00000001'
		       }
	        },
	        'view': {
		       'widget':  'ColumnChart',
		       'tag_id':  'oef_resources_spent_vs_budgeted_hrs',
		       'options': {
		          width:  400,
		          height: 240,
		          title: 'Project 00000001',
                  hAxis: {title: 'Date', titleTextStyle: {color: 'red'}},
                  vAxis: {title: 'HRS', titleTextStyle: {color: 'red'}, minValue: 0}
               }
	        }
         });
         
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ResourcesSpentVsBudgeted',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Project': '00000001',
		          'ResourceKind': 'NOK'
		       }
	        },
	        'view': {
		       'widget':  'ColumnChart',
		       'tag_id':  'oef_resources_spent_vs_budgeted_nok',
		       'options': {
		          width:  400,
		          height: 240,
		          title: 'Project 00000001',
                  hAxis: {title: 'Date', titleTextStyle: {color: 'red'}},
                  vAxis: {title: 'NOK', titleTextStyle: {color: 'red'}, minValue: 0}
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
         
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ResourcesAvailable',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {}
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_resources_available',
		       'options': {}
	        }
         });
         
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'WorkingOnMyProjects',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {}
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_working_on_my_projects',
		       'options': {}
	        }
         });
         
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ProjectMilestones',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Project': '00000001'
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_project_milestones',
		       'options': {}
	        }
         });
         
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ProjectsOngoing',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Department': '00001'
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_projects_ongoing',
		       'options': {}
	        }
         });
         
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'WorkingOnProjectsInMyDepartment',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Department': '00001'
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_working_on_projects_in_my_department',
		       'options': {}
	        }
         });
         
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'DepartmentHoursSpent',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Department': '00001'
		       }
	        },
	        'view': {
		       'widget':  'List',
		       'tag_id':  'oef_department_hours_spent',
		       'options': {}
	        }
         });
         
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Pm',
		       'method':   'ResourcesWorkload',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Department': '00001'
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_resources_workload',
		       'options': {}
	        }
         });
         
         Widgets.showWidget({
	        'load': {
		       'solution': 'AWPAnalytics',
		       'service':  'Personal',
		       'method':   'EmployeeProjects',
		       'authMethod': 'MTAuth',
		       'authtoken' : '"..user.authtoken.."',
		       'attributes': {
		          'Date': '2011-01-05'
		       }
	        },
	        'view': {
		       'widget':  'Grid',
		       'tag_id':  'oef_personal_employee_projects',
		       'options': {}
	        }
         });
         
       "&lt;/script&gt;
     &lt;/tail&gt;
   &lt;/html&gt;
}}
<div class="oef_content">
  <h3>Employee Hours</h3>
  <div id="oef_employee_hours">
    <div id='chart1_div' style="float: left;">&nbsp;</div>
    <div id='chart2_div' style="float: left;">&nbsp;</div>
    <div id='chart3_div' style="float: left;">&nbsp;</div>
  </div>

  <h3 style="clear:both">Employee Vacations Days</h3>
  <div id="oef_employee_vacation_days" style="clear:both">
    <div id='chart' style="float: left;">&nbsp;</div>
    <div id='table_chart' style="float: left;">&nbsp;</div>
  </div>
  
  <h3 style="clear:both">Project Overview</h3>
  <div id="oef_project_overview">&nbsp;</div>
  
  <h3 style="clear:both">Resources Available</h3>
  <div id="oef_resources_available">&nbsp;</div>
  
  <h3 style="clear:both">Working On My Projects</h3>
  <div id="oef_working_on_my_projects">&nbsp;</div>
  
  <h3 style="clear:both">Resources Spent Vs Budgeted HRS</h3>
  <div id="oef_resources_spent_vs_budgeted_hrs">&nbsp;</div>
  
  <h3 style="clear:both">Resources Spent Vs Budgeted NOK</h3>
  <div id="oef_resources_spent_vs_budgeted_nok">&nbsp;</div>
  
  <h3 style="clear:both">Project delivery dates</h3>
  <div id="oef_project_milestones">&nbsp;</div>
  
  <h3 style="clear:both">Projects Ongoing</h3>
  <div id="oef_projects_ongoing">&nbsp;</div>
  
  <h3 style="clear:both">Working On Projects In My Department</h3>
  <div id="oef_working_on_projects_in_my_department">&nbsp;</div>
  
  <h3 style="clear:both">Department Hours Spent</h3>
  <div id="oef_department_hours_spent">&nbsp;</div>
  
  <h3 style="clear:both">Resources Workload</h3>
  <div id="oef_resources_workload">&nbsp;</div>
  
  <h3 style="clear:both">Employee Projects</h3>
  <div id="oef_personal_employee_projects">&nbsp;</div>
</div>