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
   $criterion = array();
   $header    = 'Time cards';
   
   /* Get data */

   if (!empty($headline['Employee']))
   {
      $employee = (int) $headline['Employee'];
      $criterion[0] = 'tc.`Employee` = '.$employee;
   }
   
   if (!empty($headline['Period']))
   {
      $period  = MGlobal::parseDatePeriodString($headline['Period']);
      $header .= ' from '.date('d.m.Y', strtotime($period[0]));
      $criterion[1] = "tc.`StartDate` >= '".$period[0]."'";
      
      if (isset($period[1]))
      {
         $header .= ' to '.date('d.m.Y', strtotime($period[1]));
         $criterion[2] = "tc.`EndDate` < '".$period[1]."'";
      }
   }
   
   $odb = $container->getODBManager();
   $query = "SELECT tc._id, tc.`Employee`, tc.`StartDate`, tc.`EndDate`, tc.`_post` AS `Posted`, SUM(tr.`Hours`) AS `Hours` ". 
            "FROM `documents`.`TimeCard` AS `tc`, `documents`.`TimeCard`.`tabulars`.`TimeRecords` AS `tr` ".
            "WHERE ".($criterion ? implode(' AND ', $criterion).' AND ' : '')." tc._id = tr.Owner ".
            "GROUP BY tr.Owner ".
            "ORDER BY tc.`Employee`, tc.`StartDate`";
   
   if (null === ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }
   
   $ids   = array();
   $cards = array();
   $links = array();
   $total = 0;
   
   while ($row = $odb->fetchAssoc($res))
   {
      $cards[$row['_id']] = array(
         'Employee' => $row['Employee'],
         'Period'   => date('d.m.Y', strtotime($row['StartDate'])).' - '.date('d.m.Y', strtotime($row['EndDate'])),
         'Posted'   => $row['Posted'],
         'Hours'    => $row['Hours']
      );
      
      $total += $row['Hours'];
      
      $ids[$row['Employee']] = $row['Employee'];
   }
   
   if ($ids)
   {
      $cmodel = $container->getCModel('catalogs', 'Employees');
      $links  = $cmodel->retrieveLinkData($ids);
   }  
   
   
   /* Generate report */
   
   $mockup = new Mockup('/var/www/dekiwiki/ext/OEF/AppliedSolutions/AWPAnalytics/Templates/reports/TimeCards/TimeCards.htm');
   $report = new TabularDoc();

   $area = $mockup->getArea('header');
   $area->parameters['header'] = $header;

   $report->put($area);
   
   $area = $mockup->getArea('headline');
   $report->put($area);
   
   $a_employee = $mockup->getArea('employee');
   $a_card     = $mockup->getArea('card');
   
   $prev = 0;
   
   foreach ($cards as $doc => $row)
   {
      if ($prev != $row['Employee'])
      {
         $prev = $row['Employee'];
         
         $a_employee->parameters['Employee'] = $links[$row['Employee']]['text'];
         $report->put($a_employee);
      }
      
      $a_card->parameters['Period'] = $row['Period'];
      $a_card->parameters['Posted'] = $row['Posted'] == '1' ? 'yes' : 'no';
      $a_card->parameters['Hours']  = $row['Hours'];
      
      $a_card->decode['TimeCard'] = array(
         'uid'     => 'documents.TimeCard',
         'actions' => 'displayEditForm',
         'id'      => $doc
      );
      
      $report->put($a_card);
   }
   
   $area = $mockup->getArea('total');
   $area->parameters['OverallHours'] = $total;
   
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
   list($decode, $param) = each($event['parameters']);

   switch($decode)
   {
      case 'TimeCard':
         $ret['reference'] = $param;
         break;

      default:
         $ret = null;
   }

   $event->setReturnValue($ret);
}
