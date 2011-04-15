{{
   var d_root = 'Template:OEF/OiltecIntranet/Dashboard';
   var w_root = 'Template:OEF/OiltecIntranet/Widgets';
   
   wiki.template(d_root..'/Vertical');
   
   wiki.template(w_root..'/EmployeeHours',        ['zone_0', 'This Month']);
   wiki.template(w_root..'/EmployeeProjects',     ['zone_1', 'This Month']);
   wiki.template(w_root..'/EmployeeVacationDays', ['zone_2']);
}}
