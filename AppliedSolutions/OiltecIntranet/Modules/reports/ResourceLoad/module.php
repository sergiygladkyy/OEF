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
   
   $data   = array();
   $hours  = array();
   $empIDS = array();
   $proIDS = array();
   $links  = array();
   
   /* Get data */
   
   $odb = $container->getODBManager();
    
   if (null === ($period = MGlobal::parseDatePeriodString($headline['Period'], true)))
   {
      throw new Exception('Invalid period');
   }
    
   $criterion[] = "`Date` >= '".$period[0]."'";
   $criterion[] = "`Date` <= '".$period[1]."'";
   
   if (!empty($headline['ReportType']) && $headline['ReportType'] == 'Project Workload')
   {
      $rkind  = 2;
      $header = 'Project Workload';
      $first  = 'Project';
      $second = 'Employee';
   }
   else
   {
      $rkind  = 1;
      $header = 'Who does what';
      $first  = 'Employee';
      $second = 'Project'; 
   }
   
   if (!empty($headline['Department']))
   {
      $criterion[] = '`ProjectDepartment` = '.(int) $headline['Department'];
   }
    
   if (!empty($headline['PM']))
   {
      $query = "SELECT `Project` FROM information_registry.ProjectRegistrationRecords ".
               "WHERE `ProjectManager` = ".((int) $headline['PM'])." AND `StartDate` <= '".$period[1]."' ".
               "ORDER BY `Project`";

      if (null === ($ids = $odb->loadAssocList($query, array('field' => 'Project'))))
      {
         throw new Exception('Database error');
      }

      if (!empty($ids))
      {
         $criterion[] = '`Project` IN ('.implode(',', $ids).')';
      }
      else $empty = true;
   }

   if (!isset($empty))
   {
      $query = "SELECT * FROM information_registry.ProjectAssignmentRecords ".
               ($criterion ? 'WHERE '.implode(' AND ', $criterion).' ' : '').
               "ORDER BY `".$first."`, `".$second."`, `Date`";
      
      if (null === ($res = $odb->executeQuery($query)))
      {
         throw new Exception('Database error');
      }
      
      while ($row = $odb->fetchAssoc($res))
      {
         $empIDS[$row['Employee']] = $row['Employee'];
         $proIDS[$row['Project']]  = $row['Project'];
         
         if (isset($data[$row[$first]][$row[$second]][$row['Date']]))
         {
            $data[$row[$first]][$row[$second]][$row['Date']] += $row['Hours'];
         }
         else $data[$row[$first]][$row[$second]][$row['Date']] = $row['Hours'];
         
         if (isset($hours[$row[$first]][$row['Date']]))
         {
            $hours[$row[$first]][$row['Date']] += $row['Hours'];
         }
         else $hours[$row[$first]][$row['Date']] = $row['Hours'];
      }
   
      if ($empIDS)
      {
         $cmodel = $container->getCModel('catalogs', 'Employees');
         $links['Employee'] = $cmodel->retrieveLinkData($empIDS);
      }
       
      if ($proIDS)
      {
         $cmodel = $container->getCModel('catalogs', 'Projects');
         $links['Project'] = $cmodel->retrieveLinkData($proIDS);
      }
   }
   
   
   /* Generate report */
   
   $mockup = new Mockup(self::$templates_dir.'ResourceLoad.htm');
   $report = new TabularDoc();

   $area = $mockup->getArea('header');
   $area->parameters['header'] = $header;

   $report->put($area);
   $report->put($mockup->getArea('C1.R3'));
   
   $a_head = $mockup->getArea('headline');
   
   $start = strtotime($period[0]);
   $cur = $start;
   $end = strtotime($period[1]);
   $day = 86400;
   
   while ($cur < $end)
   {
      $a_head->parameters['Date'] = strftime('%d.%m.%y', $cur);
      $report->join($a_head);
      
      $cur += $day;
   }
   
   $a_fgroup  = $mockup->getArea('first_group');
   $a_fhours  = $mockup->getArea('first_hours');
   $a_dfhours = $mockup->getArea('d_first_hours');
   $a_sgroup  = $mockup->getArea('second_group');
   $a_shours  = $mockup->getArea('second_hours');
   $a_dshours = $mockup->getArea('d_second_hours');
   
   foreach ($data as $fID => $rows)
   {
      $a_fgroup->parameters['Description'] = $links[$first][$fID]['text'];
      
      $report->put($a_fgroup);
      
      $cur = $start;
      
      while ($cur < $end)
      {
         $_date = date('Y-m-d', $cur);
         
         if (isset($hours[$fID][$_date]))
         {
            $a_dfhours->parameters['Hours'] = $hours[$fID][$_date];
            $a_dfhours->decode['AllocationRecords'] = array(
               'uid'     => 'reports.ResourceAllocationRecords',
               'actions' => 'displayReportForm',
               'period'  => $_date,
               $first    => array($fID)
            );
            $report->join($a_dfhours);
         }
         else
         {
            $a_fhours->parameters['Hours'] = '&nbsp;';
            
            $report->join($a_fhours);
         }
         
         $cur += $day;
      }
      
      foreach ($rows as $sID => $dates)
      {
         $a_sgroup->parameters['Description'] = $links[$second][$sID]['text'];
         
         $report->put($a_sgroup);
      
         $cur = $start;
         
         while ($cur < $end)
         {
            $_date = date('Y-m-d', $cur);
            
            if (isset($dates[$_date]))
            {
               $a_dshours->parameters['Hours'] = $dates[$_date];
               $a_dshours->decode['AllocationRecords'] = array(
                  'uid'     => 'reports.ResourceAllocationRecords',
                  'actions' => 'displayReportForm',
                  'period'  => $_date,
                  $first    => $fID,
                  $second   => $sID
               );
               $report->join($a_dshours);
            }
            else
            {
               $a_shours->parameters['Hours'] = '&nbsp;';
               
               $report->join($a_shours);
            }
            
            $cur += $day;
         }
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
   list($decode, $param) = each($event['parameters']);

   switch($decode)
   {
      case 'AllocationRecords':
         $ret['reference'] = $param;
         $result =& $ret['reference'];
         
         $result['headline']['Period'] = $result['period'];
         unset($result['period']);
         
         if (isset($result['Project']))
         {
            $result['headline']['Project'] = $result['Project'];
            unset($result['Project']);
         }
         
         if (isset($result['Employee']))
         {
            $result['headline']['Employee'] = $result['Employee'];
            unset($result['Employee']);
         }
         $result['generate'] = true;
         break;

      default:
         $ret = null;
   }

   $event->setReturnValue($ret);
}
