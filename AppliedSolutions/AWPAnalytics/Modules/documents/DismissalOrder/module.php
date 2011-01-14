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

   $query = "SELECT `Employee`, `RegisteredEvent`, `Period` FROM information_registry.StaffHistoricalRecords ".
            "WHERE `Employee` IN (".implode(",", $emplIDS).") ".
            "GROUP BY `Employee`, `Period` ORDER BY `Employee` ASC, `Period` ASC";

   if (null === ($res = $odb->loadAssocList($query, array('key' => 'Employee'))))
   {
      throw new Exception('DataBase error');
   }

   foreach ($res as $employee => $row)
   {
      if (($period = strtotime($row['Period'])) === -1)
      {
         throw new Exception('Invalid date format');
      }
       
      if ($period >= $records[$employee]['DismissalDate'])
      {
         $errors[] = 'Record about '.$employees[$employee]['Description'].' for that date already exists';
         continue;
      }
       
      if ($row['RegisteredEvent'] == 'Firing')
      {
         $errors[] = 'The '.$employees[$employee]['Description'].' is fired';
      }
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
   $errors = array();
   
   if (!$histModel->setRecorder($type, $id) || !$persModel->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
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
      $pIR = clone $persModel;
      $hIR = clone $histModel;
      
      $values['DismissalDate'] = date('Y-m-d', $values['DismissalDate']);
      
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
      $ids   = array();
      $links = array();
      
      // Check related documents
      foreach ($employees as $id => $row)
      {
         $ids[] = $id;
         
         // Check RecruitingOrder and DismissalOrder
         $links = array_merge_recursive($links, MEmployees::getListMovements($row['EndDate'], $id));
         
         // Check PeriodicClosing
         $links = array_merge_recursive($links, MPeriodicClosing::getListMovements($row['EndDate'], $id));
      }
   
      if (!empty($links)) MGlobal::returnMessageByLinks($links);
      
      // Update catalog Employees
      $odb   = $container->getODBManager();
      $query = "UPDATE catalogs.Employees SET `NowEmployed` = 1 WHERE `_id` IN (".implode(',', $ids).")";
      
      if (null === $odb->executeQuery($query))
      {
         throw new Exception('DataBase error');
      }
   }

   $pRes = $persModel->delete(true, $options);
   $hRes = $histModel->delete(true, $options);
   
   $return = (empty($pRes) && empty($hRes)) ? true : false;
   
   $event->setReturnValue($return);
}
