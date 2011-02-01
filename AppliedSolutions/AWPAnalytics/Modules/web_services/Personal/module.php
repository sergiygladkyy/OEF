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
   
   $result['daysAccounted'] = $prev[0]['VacationDays'] + $accum[0]['VacationDays'];
   $result['daysSpent']     = $result['daysAccounted'] - $total[0]['VacationDays'];
   
   // Retrieve endDate
   $day = date('w', $ts);
   $day = $day == 0 ? 6 : $day - 1;
   
   $start = mktime(0,0,0, date('m', $ts), date('d', $ts) + 7 - $day, date('Y'));
   $end   = MVacation::getEndDate($hist['Schedule'], date('Y-m-d', $start), $total[0]['VacationDays']);
   
   $result['nextMondayVacationEnds'] = date('Y-m-d', $end);
   
   return $result;
}
?>