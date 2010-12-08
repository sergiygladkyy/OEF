<?php
/**
 * Generate report
 *
 * @param object $event
 * @return void
 */
function onGenerate($event)
{
   $headline  = $event['headline'];
   $container = Container::getInstance();

   /* Get data */

   if (empty($headline['Project']))
   {
      echo '<span>Unknow project</span>'; return;
   }
   
   // Retrieve project
   $proj = (int) $headline['Project'];
   $date = date('Y-m-d');
   $db   = $container->getODBManager();
   
   $query = "SELECT Project, Period, BudgetNOK, BudgetHRS FROM information_registry.ProjectRegistrationRecords ".
            "WHERE Project = ".$proj." ".
            "ORDER BY Period DESC";
   
   if (null === ($project = $db->loadAssoc($query)))
   {
      echo '<span>DataBase error</span>'; return;
   }
   
   if (!empty($project))
   {
      $links['Project'] = $container->getCModel('catalogs', 'Projects')->retrieveLinkData($proj);
   }
   else
   {
      echo '<span>Unknow project</span>'; return;
   }
   
   // Retrieve project info
   $query = "SELECT Resource AS Employee, Period, BudgetHRS, Rate FROM information_registry.ProjectAssignmentRecords ".
            "WHERE Project = ".$proj." AND Period <= '".$date."' ".
            "GROUP BY Resource, Period ORDER BY Resource ASC, Period ASC";
   
   if (null === ($employees = $db->loadAssocList($query, array('key' => 'Employee'))))
   {
      echo '<span>DataBase error</span>'; return;
   }
   
   if (!empty($employees))
   {
      $links['Employee'] = $container->getCModel('catalogs', 'Employees')->retrieveLinkData(array_keys($employees));
   }
   else
   {
      $links['Employee'] = array();
   }
   
   $total = array('OverallHours' => 0, 'OverallNOK' => 0);
   
   /* Generate report */

   $mockup = new Mockup($_SERVER['DOCUMENT_ROOT'].'/ext/OEF/AppliedSolutions/AWPAnalytics/Templates/ProjectOverview.htm');
   $report = new TabularDoc();

   $area = $mockup->getArea('header');
   $area->parameters['ProjectName'] = $links['Project'][$proj]['text'];
   $area->parameters['BudgetNOK']   = $project['BudgetNOK'];
   $area->parameters['BudgetHRS']   = $project['BudgetHRS'];

   $report->put($area);
   $report->put($mockup->getArea('headline'));

   $a_body = $mockup->getArea('body');
   
   foreach ($employees as $employee => $attributes)
   {
      $a_body->parameters['Employee']        = $links['Employee'][$employee]['text'];
      $a_body->parameters['BudgetedHours']   = $attributes['BudgetHRS'];
      $a_body->parameters['RateForCustomer'] = $attributes['Rate'];
      $a_body->parameters['AllocatedBudget'] = $attributes['BudgetHRS'] * $attributes['Rate'];
      
      $total['OverallHours'] += $attributes['BudgetHRS'];
      $total['OverallNOK']   += $a_body->parameters['AllocatedBudget'];
      
      $report->put($a_body);
   }
   
   $area = $mockup->getArea('total');
   $area->parameters['OverallHours'] = $total['OverallHours'];
   $area->parameters['OverallNOK']   = $total['OverallNOK'];
   
   $report->put($area);
   
   echo $report->show();
}
 
/**
 * Decode item value
 *
 * @param object $event
 * @return void
 */
function onDecode($event)
{
   $event->setReturnValue(true);
}
