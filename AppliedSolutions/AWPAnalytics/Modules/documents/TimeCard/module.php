<?php 

/**
 * Called after standart validation, before saving tabular TimeRecords item
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingResourcesRecord($event)
{
   $model  = $event->getSubject();
   $attrs  = $model->toArray();
   $errors = array(); 
   
   $container = Container::getInstance();
   
   $ir = $container->getModel('information_registry', 'ProjectRegistrationRecords');
   
   if (!$ir->loadByDimensions(array('Project' => $attrs['Project'])))
   {
      $event->setReturnValue(array('Project' => 'Unknow project'));
      return;
   }

   if ($attrs['SubProject'] > 0)
   {
      $sub = $container->getModel('catalogs', 'SubProjects');
      
      if ($sub->load($attrs['SubProject']) && $attrs['Project'] != $sub->getAttribute('Project')->getId())
      {
         $errors['SubProject'] = 'Invalid SubProject';
      }
   }
   
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
   $kind = $document->getKind();
   $type = $document->getType();
   $id   = $document->getId();
   $doc  = $document->toArray();
   
   $employee = $doc['Employee'];
   
   // Retrieve TimeRecords
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'TimeRecords');
   
   if (null === ($result = $cmodel->getEntities($id, array('attributes' => 'Owner'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   if (empty($result))
   {
      $event->setReturnValue(true);
      return;
   }
   
   $cmodel  = $container->getCModel('information_registry', 'Schedules');
   $irModel = $container->getModel('information_registry', 'TimeReportingRecords');
   $arModel = $container->getModel('AccumulationRegisters', 'EmployeeHoursReported');

   if (!$irModel->setRecorder($type, $id) || !$arModel->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   $arModel->setOperation('+');
   $arModel->setOption('auto_update_total', false);
   
   $dates = array();
   
   // Post document
   $errors = array();
   $pdepts = array();
   $edepts = array();
   $esched = array();
   $ehours = array();
   
   foreach ($result as $values)
   {
      // Check Employee
      if ($err = MEmployees::checkByPeriod($employee, $values['Date'], $values['Date']))
      {
         throw new Exception('Invalid record in document tabular section TimeRecords:<br>&nbsp;'.implode('<br>&nbsp;', $err));
      }
      
      // Check Vacation
      if ($err = MVacation::checkByPeriod($employee, $values['Date'], $values['Date']))
      {
         throw new Exception('Invalid record in document tabular section TimeRecords:<br>&nbsp;'.implode('<br>&nbsp;', $err));
      }
      
      // Check Project
      if ($links = MProjects::isClose($values['Project'], date('Y-m-d')))
      {
         MGlobal::returnMessageByLinks($links);
      }
      
      // Retrieve project params
      if (!isset($pdepts[$values['Project']]))
      {
         $model = $container->getCModel('information_registry', 'ProjectRegistrationRecords');

         if (null === ($res = $model->getEntities($values['Project'], array('attributes' => 'Project'))) || isset($res['errors']))
         {
            throw new Exception('Database error');
         }
         elseif (empty($res[0]))
         {
            throw new Exception('Unknow project');
         }

         $pdepts[$values['Project']] = $res[0]['ProjectDepartment'];
      }
      
      $department = $pdepts[$values['Project']];
      
      // Retrieve employee params
      if (!isset($edepts[$employee][$values['Date']]))
      {
         $odb = $container->getODBManager();

         $query = "SELECT `Period`, `OrganizationalUnit`, `Schedule` ".
                  "FROM information_registry.StaffHistoricalRecords ".
                  "WHERE `Employee`=".$employee." AND `Period` <= '".$values['Date']."' ".
                  "GROUP BY `Period`";

         if (null === ($row = $odb->loadAssoc($query)))
         {
            throw new Exception('Database error');
         }
         
         $edepts[$employee][$values['Date']] = $row['OrganizationalUnit'];
         $esched[$employee][$values['Date']] = $row['Schedule'];
      }
      
      $edep = $edepts[$employee][$values['Date']];
      $shed = $esched[$employee][$values['Date']];
      
      // Retrieve schedule
      if (!isset($ehours[$employee][$values['Date']]))
      {
         $criterion = 'WHERE `Schedule`='.$shed." AND `Date` = '".$values['Date']."' ";

         if (null === ($schedule = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($schedule['errors']))
         {
            throw new Exception('Database error');
         }
         elseif (empty($schedule))
         {
            throw new Exception('Invalid schedule');
         }
         
         $ehours[$employee][$values['Date']] = $schedule[0]['Hours'];
      }
      
      $hours = $ehours[$employee][$values['Date']];
      
      // TimeReportingRecords
      $ir = clone $irModel;
      
      if (!$ir->setAttribute('Employee', $employee))               $err[] = 'Invalid value for Employee';
      if (!$ir->setAttribute('Project',  $values['Project']))      $err[] = 'Invalid value for Project';
      if (!$ir->setAttribute('Date',     $values['Date']))         $err[] = 'Invalid value for Period';
      if (!$ir->setAttribute('Hours',    $values['Hours']))        $err[] = 'Invalid value for Hours';
      if (!$ir->setAttribute('SubProject', $values['SubProject'])) $err[] = 'Invalid value for SubProject';
      if (!$ir->setAttribute('ProjectDepartment',  $department))   $err[] = 'Invalid value for ProjectDepartment';
      if (!$ir->setAttribute('EmployeeDepartment', $edep))         $err[] = 'Invalid value for EmployeeDepartment';
      if (!$ir->setAttribute('Comment', $values['Comment']))       $err[] = 'Invalid value for Comment';
      
      if (!$err)
      {
         if ($err = $ir->save())
         {
            throw new Exception('Can\'t add record in TimeReportingRecords');
         }
      }
      else throw new Exception('Invalid attributes for TimeReportingRecords');
      
      // EmployeeHoursReported
      $date    = strtotime($values['Date']);
      $dates[] = $values['Date'];
      
      if ($hours == 0)
      {
         $owertime = 0;
         $extra    = $values['Hours'];
      }
      else
      { 
         $owertime = ($values['Hours'] > $hours) ? $values['Hours'] - $hours : 0;
         $extra    = 0;
      }
      
      $ar = clone $arModel;
      
      if (!$ar->setAttribute('Employee', $employee))                $err[] = 'Invalid value for Employee';
      if (!$ar->setAttribute('Project',  $values['Project']))       $err[] = 'Invalid value for Project';
      if (!$ar->setAttribute('EmployeeDepartment', $edep))          $err[] = 'Invalid value for EmployeeDepartment';
      if (!$ar->setAttribute('Period', date('Y-m-d H:i:s', $date))) $err[] = 'Invalid value for attribute Period';
      if (!$ar->setAttribute('Hours',    $values['Hours']))         $err[] = 'Invalid value for Hours';
      if (!$ar->setAttribute('OvertimeHours', $owertime))           $err[] = 'Invalid value for OvertimeHours';
      if (!$ar->setAttribute('ExtraHours',    $extra))              $err[] = 'Invalid value for ExtraHours';
      
      if (!$err)
      {
         if ($err = $ar->save())
         {
            throw new Exception('Can\'t add record in EmployeeHoursReported');
         }
      }
      else throw new Exception('Invalid attributes for EmployeeHoursReported'); 
   }
   
   // Calculate totals for EmployeeHoursReported
   if (!empty($dates) && $container->getCModel('AccumulationRegisters', 'EmployeeHoursReported')->countTotals($dates))
   {
      throw new Exception('Can\'t recount totals for EmployeeHoursReported');
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
   
   $irModel = $container->getCModel('information_registry', 'TimeReportingRecords');
   $arModel = $container->getCModel('AccumulationRegisters', 'EmployeeHoursReported');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   $iRes = $irModel->delete(true, $options);
   $aRes = $arModel->delete(true, $options);
   
   $return = (empty($iRes) && empty($aRes)) ? true : false;
   
   $event->setReturnValue($return);
}
