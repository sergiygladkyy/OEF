<p class="comment">Welcome to your user page! You can customize your page by removing the content below.</p>
<pre class="script">
  &lt;div id=&quot;settings&quot; style=&quot;display: none;&quot;&gt;{
     EmployeeHours: {
        Period: 'This Month'
     },
     EmployeeProjects: {
        Date: date.Format(date.now, "yyyy-MM-dd")
     },
     WorkingOnMyProjects: {
        Date: date.Format(date.now, "yyyy-MM-dd")
     },
     ProjectOverview: {
        Project: '0001',
        Date:    date.Format(date.now, "yyyy-MM-dd")
     },
     ResourcesAvailable: {
        Period:     'Next Month',
        Department: '0001'
     },
     ResourcesSpentVsBudgetedHrs: {
        Project: '0001',
        Date:    date.Format(date.now, "yyyy-MM-dd")
     },
     ResourcesSpentVsBudgetedNok: {
        Project: '0001',
        Date:    date.Format(date.now, "yyyy-MM-dd")
     },
     ProjectMilestones: {
        Project: '0001'
     },
     DepartmentHoursSpent: {
        Period:     false,
        Department: false
     },
     WorkingOnProjectsInMyDepartment: {
        Date:       false,
        Department: false
     },
     ProjectsOngoing: {
        Date:       false,
        Department: '0001'
     },
     ResourcesWorkload: {
        Period:     'This Week',
        Department: false
     }
  }&lt;/&gt;
</pre>
<p>{{ wiki.template(&quot;Template:MindTouch/Views/User_Welcome&quot;) }}</p>
<p>{{ awpskin.hidePageInfo(); }}</p>
