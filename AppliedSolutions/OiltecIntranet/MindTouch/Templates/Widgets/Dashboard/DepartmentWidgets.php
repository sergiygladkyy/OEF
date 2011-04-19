{{
   var d_root = 'Template:OEF/OiltecIntranet/Dashboard';
   var w_root = 'Template:OEF/OiltecIntranet/Widgets';
   
   /* Widget settings */
   
   var home_page = wiki.page(page.path);
   var settings  = xml.text(home_page, "*/div[@id='settings']");
   let settings  = (#settings > 0 ? string.Eval(settings) : {});
   
   var DHS_Period     = settings.DepartmentHoursSpent && settings.DepartmentHoursSpent.Period     ? settings.DepartmentHoursSpent.Period     : false;
   var DHS_Department = settings.DepartmentHoursSpent && settings.DepartmentHoursSpent.Department ? settings.DepartmentHoursSpent.Department : false;
   
   var WPMD_Date       = settings.WorkingOnProjectsInMyDepartment && settings.WorkingOnProjectsInMyDepartment.Date       ? settings.WorkingOnProjectsInMyDepartment.Date       : false;
   var WPMD_Department = settings.WorkingOnProjectsInMyDepartment && settings.WorkingOnProjectsInMyDepartment.Department ? settings.WorkingOnProjectsInMyDepartment.Department : false;
   
   var PO_Date       = settings.ProjectsOngoing && settings.ProjectsOngoing.Date       ? settings.ProjectsOngoing.Date       : false;
   var PO_Department = settings.ProjectsOngoing && settings.ProjectsOngoing.Department ? settings.ProjectsOngoing.Department : false;
   
   var RW_Period     = settings.ResourcesWorkload && settings.ResourcesWorkload.Period     ? settings.ResourcesWorkload.Period     : false;
   var RW_Department = settings.ResourcesWorkload && settings.ResourcesWorkload.Department ? settings.ResourcesWorkload.Department : false;
   
   
   /* Include Widgets */
   
   wiki.template(d_root..'/Vertical');
   
   wiki.template(w_root..'/DepartmentHoursSpent',            ['zone_0', DHS_Period, DHS_Department]);
   wiki.template(w_root..'/WorkingOnProjectsInMyDepartment', ['zone_1', WPMD_Date, WPMD_Department]);
   wiki.template(w_root..'/ProjectsOngoing',                 ['zone_2', PO_Date, PO_Department]);
   wiki.template(w_root..'/ResourcesWorkload',               ['zone_3', RW_Period, RW_Department]);
}}
