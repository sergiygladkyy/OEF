<?php
/**
 * Web-service action "getEmployeeHours"
 * 
 * @param string $attributes
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
?>