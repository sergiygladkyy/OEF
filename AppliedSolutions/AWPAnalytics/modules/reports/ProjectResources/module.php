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
   $criteria = array();
   $attrs    = array();
   $values   = array();
   if (empty($headline['Project']))
   {
      echo '<p>Choose an project</p>';
      return;
   }
   $values['Project'] = (int) $headline['Project'];
   $attrs[] = 'Project';
   $criteria[] = '`Project` = %%Project%%';
   
   if (!empty($headline['Date']))
   {
      $date = date('Y-m-d', MGlobal::dateToTimeStamp($headline['Date']));
      
      $criteria[] = "`Date` = '".$date."'";
   }

   $options['attributes']     = $attrs;
   $options['criterion']      = implode(' AND ', $criteria);
   $options['with_link_desc'] = true;

   $data = $irModel->getEntities($values, $options);

   $hours = array();
   $total = 0;
   
   foreach ($data['list'] as $row)
   {
      if (isset($hours[$row['Employee']]))
      {
         $hours[$row['Employee']] += $row['HoursSpent'];
      }
      else $hours[$row['Employee']] = $row['HoursSpent'];
      
      $total += $row['HoursSpent'];
   }


   /* Generate report */

   $mockup = new Mockup('mockup/ProjectResources.htm');
   $report = new TabularDoc();

   $area = $mockup->getArea('header');
   $area->parameters['header'] = 'Project ManHours report ('.date('d-m-Y H:i:s').')';

   $report->put($area);
   $report->put($mockup->getArea('C1.R2:C2.R2'));

   $a_employee = $mockup->getArea('employee');
   $a_hours    = $mockup->getArea('hours');
   
   foreach ($hours as $employee => $hours)
   {
      $a_employee->parameters['employee'] = $data['links']['Employee'][$employee]['text'];
      $a_employee->decode['Employee'] = array(
         'uid'      => 'catalogs.Employees',
         'actions'  => 'displayItemForm',
         'id'       => $employee
      );
      
      $report->put($a_employee);
       
      $a_hours->parameters['hours'] = $hours;
      
      $report->join($a_hours);
   }

   $a_total = $mockup->getArea('total');
   $a_total->parameters['total'] = $total;
   
   $report->put($a_total);
   
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
      case 'Employee':
         $ret['reference'] = $param;
         break;

      default:
         $ret = null;
   }

   $event->setReturnValue($ret);
}
