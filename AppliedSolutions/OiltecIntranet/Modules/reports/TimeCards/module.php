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
   $header    = 'Time Cards';
   
   /* Get data */

   if (!empty($headline['Employee']))
   {
      $employee = (int) $headline['Employee'];
      $criterion[0] = '`Employee` = '.$employee;
   }
   
   if (!empty($headline['Period']))
   {
      $period  = MGlobal::parseDatePeriodString($headline['Period'], true);
      $header .= ' from '.date('d.m.Y', strtotime($period[0]));
      $criterion[1] = "`StartDate` >= '".$period[0]."'";
      
      if (isset($period[1]))
      {
         $header .= ' to '.date('d.m.Y', strtotime($period[1]));
         $criterion[2] = "`EndDate` <= '".$period[1]."'";
      }
   }
   
   $odb = $container->getODBManager();
   $query = "SELECT `_id`, `Employee`, `StartDate`, `EndDate`, `_post` AS `Posted` ". 
            "FROM `documents`.`TimeCard` ".
            ($criterion ? 'WHERE '.implode(' AND ', $criterion).' ' : '').
            "ORDER BY `Employee`, `StartDate`";
   
   if (null === ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }
   
   $empIDS  = array();
   $periods = array();
   $docs    = array();
   $totals  = array();
   
   while ($row = $odb->fetchAssoc($res))
   {
      $docs[$row['_id']] = $row;
      
      $empIDS[$row['Employee']] = $row['Employee'];
      
      if ($row['Posted'] == '1')
      {
         $periods[$row['StartDate']][$row['EndDate']][$row['Employee']] = $row['Employee'];
      }
   }
   
   if (!empty($periods))
   {
      $cmodel = $container->getCModel('AccumulationRegisters', 'EmployeeHoursReported');
       
      foreach ($periods as $start => $param)
      {
         foreach ($param as $end => $ids)
         {
            $total = $cmodel->getTotals(array($start, date('Y-m-d', strtotime($end) + 86400)), array('criteria' => array('Employee' => $ids)));
            
            foreach ($total as $row)
            {
               if (isset($totals[$start][$end][$row['Employee']]))
               {
                  $totals[$start][$end][$row['Employee']]['Hours']    += $row['Hours'];
                  $totals[$start][$end][$row['Employee']]['Overtime'] += $row['OvertimeHours'];
                  $totals[$start][$end][$row['Employee']]['Extra']    += $row['ExtraHours'];
               }
               else
               {
                  $totals[$start][$end][$row['Employee']] = array(
                     'Hours'    => $row['Hours'],
                     'Overtime' => $row['OvertimeHours'],
                     'Extra'    => $row['ExtraHours']
                  );
               }
            }
         }
      }
      
      unset($periods);
   }
   
   $cards = array();
   $links = array();
   $total = array('Hours' => 0, 'Overtime' => 0, 'Extra' => 0);
   
   foreach ($docs as $id => $row)
   {
      $cards[$id] = array(
         'Employee' => $row['Employee'],
         'Period'   => '<nobr>Week '.MGlobal::getWeekNumber($row['StartDate']).' ('.date('d.m.Y', strtotime($row['StartDate'])).' - '.date('d.m.Y', strtotime($row['EndDate'])).')</nobr>',
         'Posted'   => $row['Posted'],
         'Hours'    => 0,
         'Overtime' => 0,
         'Extra'    => 0
      );
      
      if ($row['Posted'] == '1' && isset($totals[$row['StartDate']][$row['EndDate']][$row['Employee']]))
      {
         $tot =& $totals[$row['StartDate']][$row['EndDate']][$row['Employee']];
         
         $cards[$id]['Hours']    = $tot['Hours'];
         $cards[$id]['Overtime'] = $tot['Overtime'];
         $cards[$id]['Extra']    = $tot['Extra'];
         
         $total['Hours']    += $tot['Hours'];
         $total['Overtime'] += $tot['Overtime'];
         $total['Extra']    += $tot['Extra'];
      }
   }
   
   if ($empIDS)
   {
      $cmodel = $container->getCModel('catalogs', 'Employees');
      $links  = $cmodel->retrieveLinkData($empIDS);
   }  
   
   
   /* Generate report */
   
   $mockup = new Mockup(self::$templates_dir.'TimeCards.htm');
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
      
      $a_card->parameters['Period']   = $row['Period'];
      $a_card->parameters['Posted']   = $row['Posted'] == '1' ? 'posted' : 'not posted';
      $a_card->parameters['Hours']    = $row['Hours'];
      $a_card->parameters['Overtime'] = $row['Overtime'];
      $a_card->parameters['Extra']    = $row['Extra'];
      
      $a_card->decode['TimeCard'] = array(
         'uid'     => 'documents.TimeCard',
         'actions' => 'displayEditForm',
         'id'      => $doc
      );
      
      $report->put($a_card);
   }
   
   $area = $mockup->getArea('total');
   $area->parameters['THours']    = $total['Hours'];
   $area->parameters['TOvertime'] = $total['Overtime'];
   $area->parameters['TExtra']    = $total['Extra'];
   
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
