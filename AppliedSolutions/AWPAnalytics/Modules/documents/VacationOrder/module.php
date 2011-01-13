<?php

/**
 * Called after standart validation, before saving tabular Employees item
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingEmployeesRecord($event)
{
   $model = $event->getSubject();
   $attrs = $model->toArray();

   $errors = MVacation::checkVacationItem($attrs);
   
   $event->setReturnValue($errors);
}

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
   
   // Retrieve records from tabular section Employees
   $tsCModel = $container->getCModel($document->getKind().'.'.$type.'.tabulars', 'Employees');
   $result   = $tsCModel->getEntities($id, array('attributes' => array('Owner')));
   
   if (is_null($result) || isset($result['errors']))
   {
      throw new Exception('DataBase error');
   }
   
   $varModel = $container->getModel('information_registry', 'ScheduleVarianceRecords');
   $vacModel = $container->getModel('AccumulationRegisters', 'EmployeeVacationDays');

   if (!$varModel->setRecorder($type, $id) || !$vacModel->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   $vacModel->setOperation('-');
   $vacModel->setOption('auto_update_total', false);
   
   $from = false;
   
   foreach ($result as $row)
   {
      // Check record
      if ($errors = MVacation::checkVacationItem($row))
      {
         throw new Exception('Invalid record in document tabular section Employees');
      }
      
      $err = array();
      $IR  = clone $varModel;
      $AR  = clone $vacModel;
      
      if (($start = strtotime($row['StartDate'])) === -1)
      {
         throw new Exception('Invalid record in document tabular section Employees');
      }
      
      if (($end = strtotime($row['EndDate'])) === -1)
      {
         throw new Exception('Invalid record in document tabular section Employees');
      }
      
      if (!$from || $from > $start) $from = $start;
      
      $vacationDays = floor(($end - $start)/(24*60*60));
      
      // ScheduleVarianceRecords
      if (!$IR->setAttribute('Employee', $row['Employee']))  $err[] = 'Invalid value for attribute Employee';
      if (!$IR->setAttribute('DateFrom', $row['StartDate'])) $err[] = 'Invalid value for attribute DateFrom';
      if (!$IR->setAttribute('DateTo',   $row['EndDate']))   $err[] = 'Invalid value for attribute DateTo';
      if (!$IR->setAttribute('VarianceKind', 1))             $err[] = 'Invalid value for attribute VarianceKind';
      
      if (!$err)
      {
         if ($err = $IR->save())
         {
            throw new Exception('Can\'t add record in ScheduleVarianceRecords');
         }
      }
      else throw new Exception('Invalid attributes for ScheduleVarianceRecords');
      
      // EmployeeVacationDays
      if (!$AR->setAttribute('Employee', $row['Employee']))  $err[] = 'Invalid value for attribute Employee';
      if (!$AR->setAttribute('Period',   $period))           $err[] = 'Invalid value for attribute Period';
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
   if ($from && $container->getCModel('AccumulationRegisters', 'EmployeeVacationDays')->countTotals(date('Y-m-d H:i:s', $from)))
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
   
   $varModel = $container->getCModel('information_registry', 'ScheduleVarianceRecords');
   $vacModel = $container->getCModel('AccumulationRegisters', 'EmployeeVacationDays');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   
   $irRes = $varModel->delete(true, $options);
   $arRes = $vacModel->delete(true, $options);
   
   $return = (empty($irRes) && empty($arRes)) ? true : false;
   
   $event->setReturnValue($return);
}
