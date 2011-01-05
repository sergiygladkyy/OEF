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
   $NaturalPersons = array();
   
   foreach ($result as $row)
   {
      if (!$row['NaturalPerson'])
      {
         throw new Exception('Unknow Natural Person');
      }
      elseif (isset($records[$row['NaturalPerson']]))
      {
         throw new Exception('An Natural Person can not be taken twice');
      }
      
      $NaturalPersons[$row['NaturalPerson']] = $row['NaturalPerson'];
      
      if (($startdate = strtotime($row['StartDate'])) === -1)
      {
         throw new Exception('Invalid date format');
      }
      
      $row['StartDate'] = $startdate;
      
      $records[$row['NaturalPerson']] = $row;
   }
   
   unset($result);
   
   // Retrieve or create employees
   $cmodel    = $container->getCModel('catalogs', 'Employees');
   $employees = $cmodel->getEntities($NaturalPersons, array('attributes' => array('NaturalPerson'), 'key' => 'NaturalPerson'));
   
   if (is_null($employees) || isset($employees['errors']))
   {
      throw new Exception('DataBase error');
   }
   
   if (!empty($employees))
   {
      // Check - employee is Firing?
      $errors  = array();
      $emplIDS = array();
      $emplPer = array();
      $odb     = $container->getODBManager();
      
      foreach ($employees as $person => $row)
      {
         unset($NaturalPersons[$person]);
         $emplIDS[$person] = $row['_id'];
         $emplPer[$row['_id']] = $person;
      }
      
      $query = "SELECT `Employee`, `RegisteredEvent`, `Period` FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee` IN (".implode(",", $emplIDS).") ".
               "GROUP BY `Employee`, `Period` ORDER BY `Employee` ASC, `Period` ASC";
      
      if (null === ($res = $odb->loadAssocList($query, array('key' => 'Employee'))))
      {
         throw new Exception('DataBase error');
      }
      
      foreach ($res as $employee => $row)
      {
         $person = $emplPer[$employee];
         
         if (($period = strtotime($row['Period'])) === -1)
         {
            throw new Exception('Invalid date format');
         }
         
         if ($period >= $records[$person]['StartDate'])
         {
            $errors[] = 'Record about '.$employees[$person]['Description'].' for that date already exists';
            continue;
         }
         
         if ($row['RegisteredEvent'] != 'Firing')
         {
            $errors[] = 'The '.$employees[$person]['Description'].' is hired';
         }
      }
      
      if ($errors)
      {
         throw new Exception(implode('<br>', $errors));
      }
   }
   
   // Load NaturalPersons
   if (!empty($NaturalPersons))
   {
      $NaturalPersons = $container->getCModel('catalogs', 'NaturalPersons')->getEntities($NaturalPersons, array('key' => '_id'));
   }
   
   // Add records into IRs
   $emplClass = get_class($container->getModel('catalogs', 'Employees'));
   $persModel = $container->getModel('information_registry', 'StaffEmploymentPeriods');
   $histModel = $container->getModel('information_registry', 'StaffHistoricalRecords');
   $return = true;
   $errors = array();
   
   if (!$histModel->setRecorder($type, $id) || !$persModel->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   foreach ($records as $person => $values)
   {
      $err = array();
      $pIR = clone $persModel;
      $hIR = clone $histModel;
      
      $values['StartDate'] = date('Y-m-d', $values['StartDate']);
      
      // Employess
      if (isset($NaturalPersons[$person]))
      {
         $employee = new $emplClass('Employees');
         
         if (!$employee->setAttribute('Description', $NaturalPersons[$person]['Description'])) $err[] = 'Invalid value for attribute Description';
         if (!$employee->setAttribute('NaturalPerson', $person)) $err[] = 'Invalid value for attribute NaturalPerson';
         if (!$employee->setAttribute('NowEmployed', false))     $err[] = 'Invalid value for attribute NowEmployed';
         
         if (!$err)
         {
            if ($err = $employee->save())
            {
               throw new Exception('Can\'t add employee '.$NaturalPersons[$person]['Description']);
            }
         }
         else throw new Exception('Invalid attributes for employee '.$NaturalPersons[$person]['Description']);
         
         $emplIDS[$person] = $employee = $employee->getId();
      }
      elseif (!isset($emplIDS[$person]))
      {
         $event->setReturnValue(false);
         return;
      }
      else
      {
         $employee = $emplIDS[$person];
      }
      
      // StaffEmploymentPeriods
      if (!$pIR->setAttribute('Employee',  $employee))            $err[] = 'Invalid value for attribute Employee';
      if (!$pIR->setAttribute('StartDate', $values['StartDate'])) $err[] = 'Invalid value for attribute StartDate';
      if (!$pIR->setAttribute('EndDate',   '0000-00-00'))         $err[] = 'Invalid value for attribute EndDate';
      
      if (!$err)
      {
         if ($err = $pIR->save())
         {
            throw new Exception('Can\'t add record in StaffEmploymentPeriods');
         }
      }
      else throw new Exception('Invalid attributes for StaffEmploymentPeriods');
      
      // StaffHistoricalRecords
      if (!$hIR->setAttribute('RegisteredEvent', 1))             $err[] = 'Invalid value for attribute RegisteredEvent';
      if (!$hIR->setAttribute('Employee', $employee))            $err[] = 'Invalid value for attribute Employee';
      if (!$hIR->setAttribute('Schedule', $values['Schedule']))  $err[] = 'Invalid value for attribute Schedule';
      if (!$hIR->setAttribute('Period',   $values['StartDate'].' 00:00:00'))        $err[] = 'Invalid value for attribute Period';
      if (!$hIR->setAttribute('OrganizationalPosition', $values['Position']))       $err[] = 'Invalid value for attribute OrganizationalPosition';
      if (!$hIR->setAttribute('OrganizationalUnit', $values['OrganizationalUnit'])) $err[] = 'Invalid value for attribute OrganizationalUnit';
      if (!$hIR->setAttribute('InternalHourlyRate', $values['InternalHourlyRate'])) $err[] = 'Invalid value for attribute InternalHourlyRate';
      if (!$hIR->setAttribute('YearlyVacationDays', $values['YearlyVacationDays'])) $err[] = 'Invalid value for attribute YearlyVacationDays';
      
      if (!$err)
      {
         if ($err = $hIR->save())
         {
            throw new Exception('Can\'t add record in StaffHistoricalRecords');
         }
      }
      else throw new Exception('Invalid attributes for StaffHistoricalRecords');
   }
   
   // Update NowEmployed flag for Employees
   if ($emplIDS)
   {
      $query = "UPDATE catalogs.Employees SET `NowEmployed` = 1 WHERE `_id` IN (".implode(',', $emplIDS).")";
      
      if (null === $odb->executeQuery($query))
      {
         throw new Exception('DataBase error');
      }
   }
   
   $event->setReturnValue($return);
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
   
   if (null === ($employess = $persModel->getEntities(null, $options)) || isset($employess['errors']))
   {
      throw new Exception('DataBase error');
   }
   
   if (!empty($employess))
   {
      $odb   = $container->getODBManager();
      $query = "UPDATE catalogs.Employees SET `NowEmployed` = 0 WHERE `_id` IN (".implode(',', array_keys($employess)).")";
      
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
?>
