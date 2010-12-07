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

   $date = (!empty($headline['Date']) && is_string($headline['Date'])) ? date('Y-m-d', strtotime($headline['Date'])) : date('Y-m-d');
   $db   = $container->getODBManager();
   
   $query = "SELECT Project, Period, BudgetNOK, BudgetHRS, Deadline FROM information_registry.ProjectRegistrationRecords ".
            "WHERE Period <= '".$date."' ".
            "GROUP BY Project, Period ORDER BY Project ASC, Period ASC";
   
   if (null === ($projects = $db->loadAssocList($query, array('key' => 'Project'))))
   {
      echo '<span>DataBase error</span>'; exit;
   }
   
   if (!empty($projects))
   {
      $links = $container->getCModel('catalogs', 'Projects')->retrieveLinkData(array_keys($projects));
   }
   else $links = array();
   
   
   /* Generate report */

   $mockup = new Mockup($_SERVER['DOCUMENT_ROOT'].'/ext/OEF/AppliedSolutions/AWPAnalytics/Templates/RegisteredProjects.htm');
   $report = new TabularDoc();

   $area = $mockup->getArea('header');
   $area->parameters['header'] = 'Registered Projects report ('.date('d-m-Y H:i:s').')';

   $report->put($area);
   $report->put($mockup->getArea('headline'));

   $a_project = $mockup->getArea('project');
   
   foreach ($projects as $project => $attributes)
   {
      $a_project->parameters['ProjectName'] = $links[$project]['text'];
      $a_project->parameters['BudgetNOK']   = $attributes['BudgetNOK'];
      $a_project->parameters['BudgetHRS']   = $attributes['BudgetHRS'];
      $a_project->parameters['DeliverDate'] = $attributes['Deadline'];
      $a_project->decode['ProjectOverview'] = array(
         'uid'     => 'reports.ProjectOverview',
         'actions' => 'displayReportForm',
         'project' => $project
      );
      
      $report->put($a_project);
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
   list($decode, $param) = each($event['parameters']);

   switch($decode)
   {
      case 'ProjectOverview':
         $ret['reference'] = $param;
         $result =& $ret['reference'];
         $result['headline']['Project'] = $result['project'];
         unset($result['project']);
         $result['generate'] = true;
         break;

      default:
         $ret = null;
   }

   $event->setReturnValue($ret);
}
