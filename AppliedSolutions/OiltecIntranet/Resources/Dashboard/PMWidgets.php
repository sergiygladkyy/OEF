{{
   var d_root = 'Template:OEF/OiltecIntranet/Dashboard';
   var w_root = 'Template:OEF/OiltecIntranet/Widgets';
   
   wiki.template(d_root..'/Vertical');
   
   wiki.template(w_root..'/WorkingOnMyProjects',         ['zone_0']);
   wiki.template(w_root..'/ProjectOverview',             ['zone_1', '0001']);
   wiki.template(w_root..'/ResourcesAvailable',          ['zone_2']);
   wiki.template(w_root..'/ResourcesSpentVsBudgetedHrs', ['zone_3', '0001']);
   wiki.template(w_root..'/ResourcesSpentVsBudgetedNok', ['zone_4', '0001']);
   wiki.template(w_root..'/ProjectMilestones',           ['zone_5', '0001']);
}}
