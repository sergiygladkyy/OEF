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

   if (!empty($headline['extra']['ex_projects']))
   {
      $headline['Projects'] = $headline['extra']['ex_projects'];
   }
   
   if (empty($headline['Projects']))
   {
      echo '<span>Unknow projects</span>'; return;
   }
   
   
   // Retrieve project
   $projs     = is_array($headline['Projects']) ? $headline['Projects'] : array($headline['Projects']);
   $proj_crit = "Project IN (".implode(',', $projs).") ";
    
   $date  = date('Y-m-d');
   $db    = $container->getODBManager();
   
   $query = "SELECT Project, Period, BudgetNOK, BudgetHRS FROM information_registry.ProjectRegistrationRecords ".
            "WHERE ".$proj_crit.
            "ORDER BY Project ASC, Period ASC";
   
   if (null === ($projects = $db->loadAssocList($query, array('key' => 'Project'))))
   {
      echo '<span>DataBase error</span>'; return;
   }
   
   if (!empty($projects))
   {
      $links['Project'] = $container->getCModel('catalogs', 'Projects')->retrieveLinkData($projs);
   }
   else
   {
      echo '<span><b>The projects is not registered<b></span>'; return;
   }
   
   // Retrieve project info
   $query = "SELECT Project, Resource, Period, BudgetHRS, Rate FROM information_registry.ProjectAssignmentRecords ".
            "WHERE ".$proj_crit." AND Period <= '".$date."' ".
            "ORDER BY Project ASC, Resource ASC, Period ASC";
   
   if (null === ($res = $db->loadAssocList($query)))
   {
      echo '<span>DataBase error</span>'; return;
   }
   
   if (!empty($res))
   {
      $emplIDS    = array();
      $assignment = array();
      
      foreach ($res as $row)
      {
         $assignment[$row['Project']][$row['Resource']] = $row;
         $emplIDS[$row['Resource']] = true;
      }
      
      $emplIDS = array_keys($emplIDS);
       
      $query = "SELECT Project, SubProject, Employee, HoursSpent FROM information_registry.ProjectTimeRecords ".
               "WHERE ".$proj_crit." AND Employee IN (".implode(',', $emplIDS).") AND `Date` <= '".$date."' ".
               "ORDER BY Project ASC, Employee ASC, SubProject ASC, Date ASC";
       
      if (null === ($res = $db->loadAssocList($query)))
      {
         echo '<span>DataBase error</span>'; return;
      }
      
      $times = array();
      
      foreach ($res as $row)
      {
         $times[$row['Employee']][$row['Project']][$row['SubProject']] = $row;
      }
   }

   
   /* Generate report */

   $mockup = new Mockup($_SERVER['DOCUMENT_ROOT'].'/ext/OEF/AppliedSolutions/AWPAnalytics/Templates/ProjectPerformance.htm');
   $report = new TabularDoc();

   $area = $mockup->getArea('header');
   $area->parameters['Date'] = date('d.m.Y');

   $report->put($area);
   $report->put($mockup->getArea('headline'));

   $a_body = $mockup->getArea('body');
   
   foreach ($projects as $project => $attributes)
   {
      $BudgetSpentHRS = 0;
      $BudgetSpentNOK = 0;
      
      foreach ($assignment[$project] as $employee => $params)
      {
         $time =& $times[$employee][$project];
         
         foreach ($time as $subproj => $values)
         {
            $BudgetSpentHRS += $values['HoursSpent'];
            $BudgetSpentNOK += $params['Rate'] * $values['HoursSpent'];
         }
      }
      
      $a_body->parameters['Project']           = $links['Project'][$project]['text'];
      $a_body->parameters['ApprovedBudgetHRS'] = $attributes['BudgetHRS'];
      $a_body->parameters['BudgetSpentHRS']    = $BudgetSpentHRS;
      $a_body->parameters['ApprovedBudgetNOK'] = $attributes['BudgetNOK'];
      $a_body->parameters['BudgetSpentNOK']    = $BudgetSpentNOK;
      
      $report->put($a_body);
   }
   
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
