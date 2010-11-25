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

   $irModel  = $container->getCModel('information_registry', 'ProjectTimeRecords');
   
   if (!empty($headline['Date']))
   {
      $date = date('Y-m-d', MGlobal::dateToTimeStamp($headline['Date']));
      
      $options['criterion'] = "WHERE `Date`='".$date."'";
   }

   $options['with_link_desc'] = true;

   $data = $irModel->getEntities(array(), $options);

   $hours = array();

   foreach ($data['list'] as $row)
   {
      if (isset($hours[$row['Project']]))
      {
         $hours[$row['Project']] += $row['HoursSpent'];
      }
      else $hours[$row['Project']] = $row['HoursSpent'];
   }


   /* Generate report */

   $mockup = new Mockup('templates/ProjectManHours.htm');
   $report = new TabularDoc();

   $area = $mockup->getArea('header');
   $area->parameters['header'] = 'Project ManHours report ('.date('d-m-Y H:i:s').')';

   $report->put($area);
   $report->put($mockup->getArea('C1.R2:C2.R2'));

   $a_project = $mockup->getArea('project');
   $a_hours   = $mockup->getArea('hours');

   foreach ($hours as $project => $hours)
   {
      $a_project->parameters['project'] = $data['links']['Project'][$project]['text'];
      
      $report->put($a_project);
       
      $a_hours->parameters['hours'] = $hours;
      $a_hours->decode['ProjectResources'] = array(
         'uid'      => 'reports.ProjectResources',
         'actions'  => 'displayReportForm',
         'headline' => $headline,
         'project'  => $project
      );
      
      $report->join($a_hours);
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
      case 'ProjectResources':
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
