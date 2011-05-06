{{
   awpskin.hideAllStandardPanes();

   var d_root = 'Template:OEF/OiltecIntranet/Dashboard';
   var w_root = 'Template:OEF/OiltecIntranet/Widgets';
   
   /* Widget settings */
   
   var home_page = wiki.page(page.path);
   var settings  = xml.text(home_page, "*/div[@id='settings']");
   let settings  = (#settings > 0 ? string.Eval(settings) : {});
   
   var WOMP_Date = settings.WorkingOnMyProjects && settings.WorkingOnMyProjects.Date ? settings.WorkingOnMyProjects.Date : false;
   
   var PO_Project = settings.ProjectOverview && settings.ProjectOverview.Project ? settings.ProjectOverview.Project : false;
   var PO_Date    = settings.ProjectOverview && settings.ProjectOverview.Date    ? settings.ProjectOverview.Date    : false;
   
   var RA_Period     = settings.ResourcesAvailable && settings.ResourcesAvailable.Period     ? settings.ProjectOverview.Period     : false;
   var RA_Department = settings.ResourcesAvailable && settings.ResourcesAvailable.Department ? settings.ProjectOverview.Department : false;
   
   var RSBH_Project = settings.ResourcesSpentVsBudgetedHrs && settings.ResourcesSpentVsBudgetedHrs.Project ? settings.ResourcesSpentVsBudgetedHrs.Project : false;
   var RSBH_Date    = settings.ResourcesSpentVsBudgetedHrs && settings.ResourcesSpentVsBudgetedHrs.Date    ? settings.ResourcesSpentVsBudgetedHrs.Date    : false;
   
   var RSBN_Project = settings.ResourcesSpentVsBudgetedNok && settings.ResourcesSpentVsBudgetedNok.Project ? settings.ResourcesSpentVsBudgetedNok.Project : false;
   var RSBN_Date    = settings.ResourcesSpentVsBudgetedNok && settings.ResourcesSpentVsBudgetedNok.Date    ? settings.ResourcesSpentVsBudgetedNok.Date    : false;
   
   var PM_Project = settings.ProjectMilestones && settings.ProjectMilestones.Project ? settings.ProjectMilestones.Project : false;
   
   
   /* Include Widgets */
   
   wiki.template(d_root..'/Vertical');
   
   wiki.template(w_root..'/WorkingOnMyProjects',         ['zone_0', WOMP_Date]);
   wiki.template(w_root..'/ProjectOverview',             ['zone_1', PO_Project, PO_Date]);
   wiki.template(w_root..'/ResourcesAvailable',          ['zone_2', RA_Period, RA_Department]);
   wiki.template(w_root..'/ResourcesSpentVsBudgetedHrs', ['zone_3', RSBH_Project, RSBH_Date]);
   wiki.template(w_root..'/ResourcesSpentVsBudgetedNok', ['zone_4', RSBN_Project, RSBN_Date]);
   wiki.template(w_root..'/ProjectMilestones',           ['zone_5', PM_Project]);
}}
