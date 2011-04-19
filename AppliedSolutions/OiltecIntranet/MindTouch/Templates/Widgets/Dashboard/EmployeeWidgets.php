{{
   var d_root = 'Template:OEF/OiltecIntranet/Dashboard';
   var w_root = 'Template:OEF/OiltecIntranet/Widgets';
   
   /* Widget settings */
   
   var home_page = wiki.page(page.path);
   var settings  = xml.text(home_page, "*/div[@id='settings']");
   let settings  = (#settings > 0 ? string.Eval(settings) : {});
   
   var EH_Period = settings.EmployeeHours && settings.EmployeeHours.Period ? settings.EmployeeHours.Period : false;
   
   var EP_Date = settings.EmployeeProjects && settings.EmployeeProjects.Date ? settings.EmployeeProjects.Date : false;
   
   
   /* Include Widgets */
   
   wiki.template(d_root..'/Vertical');
   
   wiki.template(w_root..'/EmployeeHours',        ['zone_0', EH_Period]);
   wiki.template(w_root..'/EmployeeProjects',     ['zone_1', EP_Date]);
   wiki.template(w_root..'/EmployeeVacationDays', ['zone_2']);
}}
