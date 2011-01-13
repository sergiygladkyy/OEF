<?php

/**
 * Post document
 * 
 * @param object $event
 * @return void
 */
function onPost($event)
{
   $document  = $event->getSubject();
   $container = Container::getInstance();
   $period    = $document->getAttribute('Date');
   $type = $document->getType();
   $id   = $document->getId();
 
   if (($ts = strtotime($period)) === -1)
   {
      throw new Exception('Invalid date format');
   }
   
   $start  = mktime(0,0,0, date('m', $ts), 1, date('Y', $ts));
   $end    = mktime(23,59,59, date('m', $ts) + 1, 0, date('Y', $ts));
   $period = date('Y-m-d H:i:s', $end);
   
   // Retrieve worked Employees
   $cmodel = $container->getCModel('information_registry', 'StaffHistoricalRecords');
   
   $criterion  = "WHERE `Period` < '".date('Y-m-d H:i:s', $end)."' AND `RegisteredEvent` = 'Hiring' ";
   $criterion .= "GROUP BY `Employee`, `Period` ASC";
   
   $hiring = $cmodel->getEntities(null, array('criterion' => $criterion, 'key' => 'Employee'));
   
   if (is_null($hiring) || isset($hiring['errors']))
   {
      throw new Exception('DataBase error');
   }
   
   $criterion  = "WHERE `Period` > '".date('Y-m-d H:i:s', $start)."' AND `RegisteredEvent` = 'Firing' ";
   $criterion .= "GROUP BY `Employee`, `Period` DESC";
   
   $firing = $cmodel->getEntities(null, array('criterion' => $criterion, 'key' => 'Employee'));
   
   if (is_null($firing) || isset($firing['errors']))
   {
      throw new Exception('DataBase error');
   }
   
   // Calculate vacation days
   $days = array();
   
   foreach ($hiring as $employee => $row)
   {
      // Prepare dates
      if (($hDate = strtotime($row['Period'])) === -1)
      {
         throw new Exception('Invalid date format in StaffHistoricalRecords');
      }
      
      if (isset($firing[$employee]))
      {
         if (($fDate = strtotime($firing[$employee]['Period'])) === -1)
         {
            throw new Exception('Invalid date format in StaffHistoricalRecords');
         }
      }
      else $fDate = null;
      
      // Calculate
      if ($hDate <= $start)
      {
         if (is_null($fDate) || $fDate > $end)
         {
            $days[$employee] = $row['YearlyVacationDays']/12;
         }
         else
         {
            $days[$employee] = floor($row['YearlyVacationDays']/12*((int) date('d', $fDate) - 1)/((int) date('d', $end)));
         }
      }
      else
      {
         if (is_null($fDate) || $fDate > $end)
         {
            $days[$employee] = floor($row['YearlyVacationDays']/12*((int) date('d', $end) - (int) date('d', $hDate) + 1)/((int) date('d', $end)));
         }
         else
         {
            $days[$employee] = floor($row['YearlyVacationDays']/12*((int) date('d', $fDate) - (int) date('d', $hDate))/((int) date('d', $end)));
         }
      }
   }
   
   unset($hiring);
   unset($firing);
   
   $vacModel = $container->getModel('AccumulationRegisters', 'EmployeeVacationDays');

   if (!$vacModel->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   $vacModel->setOperation('+');
   $vacModel->setOption('auto_update_total', false);
   
   foreach ($days as $employee => $vacationDays)
   {
      $err = array();
      $AR  = clone $vacModel;
      
      // EmployeeVacationDays
      if (!$AR->setAttribute('Employee',     $employee))     $err[] = 'Invalid value for attribute Employee';
      if (!$AR->setAttribute('Period',       $period))       $err[] = 'Invalid value for attribute Period';
      if (!$AR->setAttribute('VacationDays', $vacationDays)) $err[] = 'Invalid value for attribute VacationDays';
      
      if (!$err)
      {
         if ($err = $AR->save())
         {
            throw new Exception('Can\'t add record in EmployeeVacationDays');
         }
      }
      else throw new Exception('Invalid attributes for EmployeeVacationDays');
   }
   
   // Calculate totals for EmployeeVacationDays
   if ($container->getCModel('AccumulationRegisters', 'EmployeeVacationDays')->countTotals(date('Y-m-d H:i:s', $period)))
   {
      throw new Exception('Can\'t recount totals for EmployeeVacationDays');
   }
   
   $event->setReturnValue(true);
}

/**
 * Clear posting
 * 
 * @param object $event
 * @return void
 */
function onUnpost($event)
{
   $document  = $event->getSubject();
   $container = Container::getInstance();
   
   $vacModel = $container->getCModel('AccumulationRegisters', 'EmployeeVacationDays');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   $return = ($vacModel->delete(true, $options)) ? false : true;
   
   $event->setReturnValue($return);
}
