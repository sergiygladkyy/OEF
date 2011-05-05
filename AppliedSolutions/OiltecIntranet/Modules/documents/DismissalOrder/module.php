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
   $type = $document->getType();
   $id   = $document->getId();
   
   // Retrieve records from tabular section Employees
   $tsCModel = $container->getCModel($document->getKind().'.'.$type.'.tabulars', 'Employees');
   $result   = $tsCModel->getEntities($id, array('attributes' => array('Owner')));
   
   if (is_null($result) || isset($result['errors']))
   {
      throw new Exception('DataBase error');
   }
   
   // Check records
   $records = array();
   $emplIDS = array();
   
   foreach ($result as $row)
   {
      if (!$row['Employee'])
      {
         throw new Exception('Unknow Employee');
      }
      elseif (isset($records[$row['Employee']]))
      {
         throw new Exception('The employee can not be fired twice');
      }
      
      $emplIDS[] = $row['Employee'];
      
      if (($disDate = strtotime($row['DismissalDate'])) === -1)
      {
         throw new Exception('Invalid date format');
      }
      
      $row['DismissalDate'] = $disDate;
      
      $records[$row['Employee']] = $row;
   }
   
   unset($result);
   
   // Retrieve employees
   if (empty($emplIDS))
   {
      $event->setReturnValue(true);
      return;
   }
   
   $cmodel    = $container->getCModel('catalogs', 'Employees');
   $employees = $cmodel->getEntities($emplIDS, array('key' => '_id'));
   
   if (is_null($employees) || isset($employees['errors']))
   {
      throw new Exception('DataBase error');
   }
   
   if (array_diff($emplIDS, array_keys($employees)))
   {
      throw new Exception('Wrong data');
   } 
   
   // Check - employee is Firing?
   $errors  = array();
   $odb     = $container->getODBManager();
   
   foreach ($emplIDS as $employee)
   {
      $disD  = date('Y-m-d H:i:s', $records[$employee]['DismissalDate']);
      
      // Check - employee is Hiring before..?
      $query = "SELECT `Employee`, `RegisteredEvent`, MAX(`Period`) AS `Period` FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".$employee." AND `Period` <= '".$disD."'";
      
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('DataBase error');
      }
      
      if (empty($row['Employee']))
      {
         $errors[] = 'The document cannot be posted because the employee '.$employees[$employee]['Description'].' was not hired.';
         continue;
      }
      
      if (($period = strtotime($row['Period'])) === -1)
      {
         throw new Exception('Invalid date format');
      }
       
      if ($period == $records[$employee]['DismissalDate'])
      {
         $errors[] = 'Record about '.$employees[$employee]['Description'].' for that date already exists.';
         continue;
      }
       
      if ($row['RegisteredEvent'] == 'Firing')
      {
         $errors[] = 'The document cannot be posted because the employee '.$employees[$employee]['Description'].' is fired.';
         continue;
      }
      
      // Check - employee is Firing after..?
      $query = "SELECT `Employee`, `RegisteredEvent`, MIN(`Period`) AS `Period` FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".$employee." AND `Period` > '".$disD."'";
      
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('DataBase error');
      }
      
      if (!empty($row['Employee']))
      {
         if ($row['RegisteredEvent'] == 'Firing')
         {
            $errors[] = 'The document cannot be posted because the employee '.$employees[$employee]['Description'].' has been fired after this date.';
            continue;
         }
         elseif ($row['RegisteredEvent'] == 'Move')
         {
            $errors[] = 'The document cannot be posted because the employee '.$employees[$employee]['Description'].' has been moved after this date.';
            continue;
         }
         
         if (($nHirihg = strtotime($row['Period'])) === -1)
         {
            throw new Exception('Invalid date format');
         }
      }
      else $nHirihg = null;
      
      // Check - project assignment
      $query = "SELECT * FROM information_registry.ProjectAssignmentPeriods ".
               "WHERE `Employee`=".$employee." AND DateTo > '".$disD."'".($nHirihg ? " AND `DateFrom` < '".$nHirihg."'" : '');
      
      if (null === ($arec = $odb->loadAssocList($query)))
      {
         throw new Exception('DataBase error');
      }
      elseif (!empty($arec))
      {
         $errors[] = 'The document cannot be posted because the employee '.$employees[$employee]['Description'].' has assignments.';
         continue;
      }
      
      // Check time cards
      /*if (!($hrec = MEmployees::getLastHiringRecord($employee, $disD)))
      {
         throw new Exception('Wrong data');
      }
      
      $query = "SELECT * FROM information_registry.TimeReportingRecords ".
               "WHERE `Employee`=".$employee." AND `Date` >= '".$hrec['Period']."' AND `Date` <= '".$disD."'";
      
      if (null === ($res = $odb->executeQuery($query)))
      {
         throw new Exception('DataBase error');
      }
      
      $trec = array();
      
      while ($row = $odb->fetchAssoc($res))
      {
         $trec[$row['Employee'].$row['Project'].$row['Date']] = $row['Date'];
      }
      
      $query = "SELECT * FROM information_registry.ProjectAssignmentRecords ".
               "WHERE `Employee`=".$employee." AND `Date` >= '".$hrec['Period']."' AND `Date` <= '".$disD."'";
      
      if (null === ($res = $odb->executeQuery($query)))
      {
         throw new Exception('DataBase error');
      }
      
      $_dates = array();
      
      while ($row = $odb->fetchAssoc($res))
      {
         if (isset($trec[$row['Employee'].$row['Project'].$row['Date']])) continue;
         
         $_dates[$row['Date']] = $row['Date'];
      }
      
      if (empty($_dates)) continue;
      
      $errors[] = 'The employee '.$employees[$employee]['Description'].' Week'.implode(', Week', MGlobal::getListWeeksByDates($_dates));*/
   }

   if ($errors)
   {
      throw new Exception(implode('<br>', $errors));
   }
   
   // Check VacationOrder
   $links = array();
   
   foreach ($records as $employee => $values)
   {
      $links = array_merge_recursive($links, MVacation::getListVacationOrder(date('Y-m-d', $values['DismissalDate']), $employee));
   }
   
   if (!empty($links)) MGlobal::returnMessageByLinks($links);
     
   // Initialize IRs
   $persModel = $container->getModel('information_registry', 'StaffEmploymentPeriods');
   $histModel = $container->getModel('information_registry', 'StaffHistoricalRecords');
   $vacModel  = $container->getModel('AccumulationRegisters', 'EmployeeVacationDays');
   $vacCModel = $container->getCModel('AccumulationRegisters', 'EmployeeVacationDays');
   $errors = array();
   
   if (!$histModel->setRecorder($type, $id) || 
       !$persModel->setRecorder($type, $id) ||
       !$vacModel ->setRecorder($type, $id)
   )
   {
      throw new Exception('Invalid recorder');
   }
   
   $vacModel->setOperation('-');
   $vacModel->setOption('auto_update_total', true);
   
   // Update NowEmployed flag for Employees
   $query = "UPDATE catalogs.Employees SET `NowEmployed` = 0 WHERE `_id` IN (".implode(',', $emplIDS).")";
   
   if (null === $odb->executeQuery($query))
   {
      throw new Exception('DataBase error');
   }
   
   // Add records into IRs
   foreach ($records as $employee => $values)
   {
      $err = array();
      
      $values['DismissalDate'] = date('Y-m-d', $values['DismissalDate']);
      
      $total = $vacCModel->getTotals($values['DismissalDate'], array('criteria' => array('Employee' => $employee)));
      
      if (isset($total[0]))
      {
         $total  = $total[0]['VacationDays'];
         $period = $values['DismissalDate'].' 00:00:00';
         
         $AR = clone $vacModel;
         
         // EmployeeVacationDays
         if (!$AR->setAttribute('Employee',     $employee)) $err[] = 'Invalid value for attribute Employee';
         if (!$AR->setAttribute('Period',       $period))   $err[] = 'Invalid value for attribute Period';
         if (!$AR->setAttribute('VacationDays', $total))    $err[] = 'Invalid value for attribute VacationDays';
         
         if (!$err)
         {
            if ($err = $AR->save())
            {
               throw new Exception('Can\'t add record in EmployeeVacationDays');
            }
         }
         else throw new Exception('Invalid attributes for EmployeeVacationDays');
      }
      
      $pIR = clone $persModel;
      $hIR = clone $histModel;
      
      // StaffEmploymentPeriods
      if (!$pIR->setAttribute('Employee',  $employee))              $err[] = 'Invalid value for attribute Employee';
      if (!$pIR->setAttribute('StartDate', '0000-00-00'))           $err[] = 'Invalid value for attribute StartDate';
      if (!$pIR->setAttribute('EndDate', $values['DismissalDate'])) $err[] = 'Invalid value for attribute EndDate';
      
      if (!$err)
      {
         if ($err = $pIR->save())
         {
            throw new Exception('Can\'t add record in StaffEmploymentPeriods');
         }
      }
      else throw new Exception('Invalid attributes for StaffEmploymentPeriods');
      
      // StaffHistoricalRecords
      if (!$hIR->setAttribute('RegisteredEvent', 2))  $err[] = 'Invalid value for attribute RegisteredEvent';
      if (!$hIR->setAttribute('Employee', $employee)) $err[] = 'Invalid value for attribute Employee';
      if (!$hIR->setAttribute('Period', $values['DismissalDate'].' 00:00:00')) $err[] = 'Invalid value for attribute Period';
      
      if (!$err)
      {
         if ($err = $hIR->save())
         {
            throw new Exception('Can\'t add record in StaffHistoricalRecords');
         }
      }
      else throw new Exception('Invalid attributes for StaffHistoricalRecords');
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
   
   $persModel = $container->getCModel('information_registry', 'StaffEmploymentPeriods');
   $histModel = $container->getCModel('information_registry', 'StaffHistoricalRecords');
   $vacModel  = $container->getCModel('AccumulationRegisters', 'EmployeeVacationDays');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId(),
      'key'        => 'Employee'
   );
   
   if (null === ($employees = $persModel->getEntities(null, $options)) || isset($employees['errors']))
   {
      throw new Exception('DataBase error');
   }
   
   if (!empty($employees))
   {
      $odb   = $container->getODBManager();
      $query = "UPDATE catalogs.Employees SET `NowEmployed` = 1 WHERE `_id` IN (".implode(',', array_keys($employees)).")";
      
      if (null === $odb->executeQuery($query))
      {
         throw new Exception('DataBase error');
      }
   }

   $pRes = $persModel->delete(true, $options);
   $hRes = $histModel->delete(true, $options);
   $vRes = $vacModel ->delete(true, $options);
   
   $return = (empty($pRes) && empty($hRes) && empty($vRes)) ? true : false;
   
   $event->setReturnValue($return);
}
