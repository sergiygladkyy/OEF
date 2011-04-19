{{
   var d_root = 'Template:OEF/OiltecIntranet/Dashboard';
   var w_root = 'Template:OEF/OiltecIntranet/Widgets';
   
   wiki.template(d_root..'/Vertical');
   
   wiki.template(w_root..'/DepartmentHoursSpent',            ['zone_0']);
   wiki.template(w_root..'/WorkingOnProjectsInMyDepartment', ['zone_1']);
   wiki.template(w_root..'/ProjectsOngoing',                 ['zone_2']);
   wiki.template(w_root..'/ResourcesWorkload',               ['zone_3']);
}}
