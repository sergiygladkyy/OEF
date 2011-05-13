<?php
/**
 * Web-service action "getEmployeeHours"
 * 
 * @param array $attributes
 * @return array
 */
function getEmployeeHours(array $attributes)
{
   $period   = empty($attributes['Period']) ? 'This Month' : $attributes['Period'];
   $employee = MEmployees::retrieveCurrentEmployee();
   
   $result = array(
      'Hours' => array(
         'actual' => 0,
         'max'    => 0
      ),
      'Overtime' => array(
         'actual' => 0,
         'max'    => 0
      ),
      'Extra' => array(
         'actual' => 0,
         'max'    => 0
      )
   );
   
   if (null === ($period = MGlobal::parseDatePeriodString($period)))
   {
      throw new Exception('Invalid period');
   }
   
   $container = Container::getInstance();
   
   // Retrieve actual
   $cmodel = $container->getCModel('AccumulationRegisters', 'EmployeeHoursReported');
   $totals = $cmodel->getTotals($period, array('criteria' => array('Employee' => $employee)));
   
   foreach ($totals as $row)
   {
      $result['Hours']['actual']    += $row['Hours'];
      $result['Overtime']['actual'] += $row['OvertimeHours'];
      $result['Extra']['actual']    += $row['ExtraHours'];
   }
   
   // Calculate max
   $schedules = MEmployees::retrieveSchedulesByPeriod($employee, $period['from'], $period['to']);
   
   if (empty($schedules)) throw new Exception('Unknow schedules');
   
   $vacations = MVacation::getScheduleVarianceDays($employee, $period['from'], $period['to']);
   
   foreach ($schedules as $schedID => $periods)
   {
      foreach ($periods as $dates)
      {
         $schedule = MSchedules::getSchedule($schedID, $dates['from'], $dates['to']);
         
         if (empty($schedule)) throw new Exception('Unknow schedule');
         
         foreach ($schedule as $date => $hours)
         {
            if (isset($vacations[$date])) continue;
            
            if ($hours > 0)
            {
               $result['Hours']['max']    += $hours;
               $result['Overtime']['max'] += 24 - $hours;
            }
            else
            {
               $result['Extra']['max'] += 24;
            }
         }
      }
   }
   
   return $result;
}

/**
 * Web-service action "getEmployeeVacationDays"
 * 
 * @param array $attributes
 * @return array
 */
function getEmployeeVacationDays(array $attributes)
{
   $result = array(
      'daysEligible'  => 0,
      'daysAccounted' => 0,
      'daysSpent'     => 0,
      'nextMondayVacationEnds' => ''
   );
   
   if (0 === ($employee = MEmployees::retrieveCurrentEmployee()))
   {
      throw new Exception('Unknow employee');
   }
   
   $ts   = time();
   $date = date('Y-m-d', $ts);
   
   // Retrieve altogether
   $hist = MEmployees::getLastHiringRecord($employee, $date);
   
   if (empty($hist)) throw new Exception('Employee not hiring');
   
   $result['daysEligible'] = $hist['YearlyVacationDays'];
   
   // Retrieve actual
   $container = Container::getInstance();
   
   $cmodel = $container->getCModel('AccumulationRegisters', 'EmployeeVacationDays');
   $prev   = $cmodel->getTotals(date('Y', $ts).'-01-01', array('criteria' => array('Employee' => $employee)));
   $total  = $cmodel->getTotals($date, array('criteria' => array('Employee' => $employee)));
   $accum  = $cmodel->getTotals(array(date('Y', $ts).'-01-01', $date), array('operation' => '+', 'criteria' => array('Employee' => $employee)));
   
   $pvd = isset($prev[0]['VacationDays'])  ? $prev[0]['VacationDays']  : 0;
   $avd = isset($accum[0]['VacationDays']) ? $accum[0]['VacationDays'] : 0;
   $tvd = isset($total[0]['VacationDays']) ? $total[0]['VacationDays'] : 0;
   
   $result['daysAccounted'] = $pvd + $avd;
   $result['daysSpent']     = $result['daysAccounted'] - $tvd;
   
   // Retrieve endDate
   $day = date('w', $ts);
   $day = $day == 0 ? 6 : $day - 1;
   
   $start = mktime(0,0,0, date('m', $ts), date('d', $ts) + 7 - $day, date('Y'));
   $end   = MVacation::getEndDate($hist['Schedule'], date('Y-m-d', $start), $tvd);
   
   $result['nextMondayVacationEnds'] = date('Y-m-d', $end);
   
   return $result;
}

/**
 * Web-service action "getEmployeeProjects"
 * 
 * @param array $attributes
 * @return array
 */
function getEmployeeProjects(array $attributes)
{
   // Check attributes
   $date     = empty($attributes['Date']) ? date('Y-m-d') : $attributes['Date'];
   $employee = MEmployees::retrieveCurrentEmployee();
   
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array('Project', 'Nearest Ms', 'Hrs Allocated/Spent')
   );
   
   if (empty($employee)) return $result;
   
   $projects = MProjects::getEmployeeProjects($employee, $date, $date, true, array('key' => 'Project'));
   
   if (empty($projects)) return $result;
   
   $proIDS    = array_keys($projects);
   $container = Container::getInstance();
   
   $odb   = $container->getODBManager();
   $query = "SELECT `Project`, MIN(`MileStoneDeadline`) AS `MileStoneDeadline` ".
            "FROM information_registry.MilestoneRecords ".
            "WHERE `Project` IN (".implode(',', $proIDS).") AND `MileStoneDeadline` >= '".$date."'".
            "GROUP BY `Project`";
   
   if (null === ($ms = $odb->loadAssocList($query, array('key' => 'Project'))))
   {
      throw new Exception('Database error');
   }
   
   // Allocated Hours
   $aHours = MEmployees::getHoursAllocated($employee, $proIDS);
   
   // Hours SPENT
   $model = $container->getCModel('AccumulationRegisters', 'EmployeeHoursReported');
   $sRows = $model->getTotals(array(date('Y-m', strtotime($date)).'-01 00:00:00', $date), array('criteria' => array('Project' => $proIDS, 'Employee' => $employee)));
   $spent = array();
   
   foreach ($sRows as $row)
   {
      if (isset($spent[$row['Project']]))
      {
         $spent[$row['Project']] += $row['Hours'];
      }
      else
      {
         $spent[$row['Project']] = $row['Hours'];
      }
   }
   
   // Result
   foreach ($proIDS as $project)
   {
      $al = isset($aHours[$project]) ? $aHours[$project]['HoursAllocated'] : 0;
      $sp = isset($spent[$project])  ? $spent[$project]  : 0;
      
      $result['list'][] = array(
         0 => $project,
         1 => (isset($ms[$project]) ? $ms[$project]['MileStoneDeadline'] : ''),
         2 => $al.'/'.$sp
      );
   }
   
   $result['links']['Project'] = $container->getCModel('catalogs', 'Projects')->retrieveLinkData($proIDS);
   
   return $result;
}
?>