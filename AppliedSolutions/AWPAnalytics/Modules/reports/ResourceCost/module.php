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

   if (!empty($headline['extra']['ex_employees']))
   {
      $headline['Employees'] = $headline['extra']['ex_employees'];
   }
   
   if (empty($headline['Employees']))
   {
      echo '<span>Unknow employees</span>'; return;
   }
   
   
   $empls = is_array($headline['Employees']) ? $headline['Employees'] : array($headline['Employees']);
   $date  = date('Y-m-d');
   $db    = $container->getODBManager();
   
   $query = "SELECT Project, Resource, Period, Rate FROM information_registry.ProjectAssignmentRecords ".
            "WHERE Resource IN (".implode(',', $empls).") AND Period <= '".$date."' ".
            "ORDER BY Project ASC, Resource ASC, Period ASC";
   
   if (null === ($res = $db->loadAssocList($query)))
   {
      echo '<span>DataBase error</span>'; return;
   }
   
   if (empty($res))
   {
      echo '<span><b>Resource Assignments is empty</b></span>'; return;
   }
   
   $emplIDS    = array();
   $projIDS    = array();
   $assignment = array();

   foreach ($res as $row)
   {
      $assignment[$row['Resource']][$row['Project']] = $row;
      $emplIDS[$row['Resource']] = true;
      $projIDS[$row['Project']] = true;
   }

   $links['Employee'] = $container->getCModel('catalogs', 'Employees')->retrieveLinkData(array_keys($emplIDS));
   $links['Project']  = $container->getCModel('catalogs', 'Projects' )->retrieveLinkData(array_keys($projIDS));
   

   /* Generate report */

   $mockup = new Mockup($_SERVER['DOCUMENT_ROOT'].'/ext/OEF/AppliedSolutions/AWPAnalytics/Templates/ResourceCost.htm');
   $report = new TabularDoc();

   $area = $mockup->getArea('header');
   $area->parameters['Date'] = date('d.m.Y');

   $report->put($area);
   $report->put($mockup->getArea('headline'));

   $a_employee = $mockup->getArea('employee');
   $a_project  = $mockup->getArea('project');
   
   foreach ($assignment as $employee => $params)
   {
      $a_employee->parameters['Employee'] = $links['Employee'][$employee]['text'];
      
      $report->put($a_employee);
      
      foreach ($params as $project => $attributes)
      {
         $a_project->parameters['Project'] = $links['Project'][$project]['text'];
         $a_project->parameters['Rate']    = $attributes['Rate'];
      
         $report->put($a_project);
      }
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
