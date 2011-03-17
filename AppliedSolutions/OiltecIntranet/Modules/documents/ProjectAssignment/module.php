<?php 

/**
 * Called after standart validation, before saving tabular Resources item
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
   
   $doc = $container->getModel('documents', 'ProjectAssignment');
   $doc->load($attrs['Owner']);
   
   $cmodel = $container->getCModel('documents.ProjectAssignment.tabulars', 'Resources');

   $criterion  = "WHERE `Owner` = ".$attrs['Owner']." AND `Employee` = '".$attrs['Employee']."'";
   
   if (!empty($attrs['_id'])) $criterion .= " AND `_id` <> ".$attrs['_id'];
   
   if (null === ($res = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($result['errors']))
   {
      $event->setReturnValue(array('Employee' => 'Validation error'));
      return;
   }
   
   if (!empty($res))
   {
      $errors['Employee'] = 'Duplicate';
   }
   
   if (null === ($prID = $doc->getAttribute('Project')))
   {
      $event->setReturnValue(array('SubProject' => 'Validation error'));
      return;
   }
   
   $prID = $prID->getId();
   
   $ir = $container->getModel('information_registry', 'ProjectRegistrationRecords');
   
   if (!$ir->loadByDimensions(array('Project' => $prID)))
   {
      $event->setReturnValue(array('SubProject' => 'Validation error'));
      return;
   }

   if ($attrs['SubProject'] > 0)
   {
      $sub = $container->getModel('catalogs', 'SubProjects');
      
      if ($sub->load($attrs['SubProject']) && $prID != $sub->getAttribute('Project')->getId())
      {
         $errors['SubProject'] = 'Invalid SubProject';
      }
   }
   
   if (($start = strtotime($attrs['StartDate'])) === -1)
   {
      $errors['StartDate'] = 'Invalid date format';
   }
   elseif ($start < strtotime($ir->getAttribute('StartDate')))
   {
      $errors['StartDate'] = 'Should exceed Project StartDate ('.$ir->getAttribute('StartDate').')';
   }
   
   if (($end = strtotime($attrs['EndDate'])) === -1)
   {
      $errors['EndDate'] = 'Invalid date format';
   }
   elseif ($start != -1 && $start >= $end)
   {
      $errors['EndDate'] = 'Should exceed StartDate';
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
   
   // Check Closing
   if ($links = MProjects::isClose($doc['Project'], date('Y-m-d')))
   {
      MGlobal::returnMessageByLinks($links);
   }
   
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Resources');
   
   if (null === ($result = $cmodel->getEntities($id, array('attributes' => 'Owner'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   if (empty($result))
   {
      $event->setReturnValue(true);
      return;
   }
   
   $model = $container->getCModel('information_registry', 'ProjectRegistrationRecords');
   
   if (null === ($res = $model->getEntities($doc['Project'], array('attributes' => 'Project'))) || isset($res['errors']))
   {
      throw new Exception('Database error');
   }
   elseif (empty($res[0]))
   {
      throw new Exception('Unknow project');
   }
   
   $department = $res[0]['ProjectDepartment'];
   
   $cmodel  = $container->getCModel('information_registry', 'Schedules');
   $arModel = $container->getModel('information_registry', 'ProjectAssignmentRecords');
   $apModel = $container->getModel('information_registry', 'ProjectAssignmentPeriods');

   if (!$arModel->setRecorder($type, $id) || !$apModel->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   // Post document
   $errors = array();
   
   foreach ($result as $values)
   {
      // Check Worked
      if ($err = MEmployees::checkByPeriod($values['Employee'], $values['StartDate'], $values['EndDate']))
      {
         throw new Exception('Invalid record in document tabular section Resources:<br>'.implode('<br>', $err));
      }
      
      // Check Vacation
      if ($err = MVacation::checkByPeriod($values['Employee'], $values['StartDate'], $values['EndDate']))
      {
         throw new Exception('Invalid record in document tabular section Resources:<br>'.implode('<br>', $err));
      }
      
      // Retrieve employee params
      $odb = $container->getODBManager();
      
      $query = "SELECT `Period`, `OrganizationalUnit`, `Schedule` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".(int) $values['Employee']." AND `Period` <= '".$values['StartDate']."' ".
               "GROUP BY `Period`";
      
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      $edep = $row['OrganizationalUnit'];
      $shed = $row['Schedule'];
      
      // Retrieve schedule
      $criterion  = 'WHERE `Schedule`='.$shed." AND `Date` >= '".$values['StartDate']."' AND `Date` < '".$values['EndDate']."' ";
      $criterion .= "ORDER BY `Date` ASC";
      
      if (null === ($schedule = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($schedule['errors']))
      {
         throw new Exception('Database error');
      }
      elseif (empty($schedule))
      {
         continue;
      }
      
      // ProjectAssignmentPeriods
      $err = array();
      $ap  = clone $apModel;
      
      if (!$ap->setAttribute('Employee', $values['Employee']))     $err[] = 'Invalid value for Employee';
      if (!$ap->setAttribute('Project',  $doc['Project']))         $err[] = 'Invalid value for Project';
      if (!$ap->setAttribute('DateFrom', $values['StartDate']))    $err[] = 'Invalid value for DateFrom';
      if (!$ap->setAttribute('DateTo',   $values['EndDate']))      $err[] = 'Invalid value for DateTo';
      if (!$ap->setAttribute('SubProject', $values['SubProject'])) $err[] = 'Invalid value for SubProject';
      if (!$ap->setAttribute('ProjectDepartment',  $department))   $err[] = 'Invalid value for ProjectDepartment';
      if (!$ap->setAttribute('EmployeeDepartment', $edep))         $err[] = 'Invalid value for EmployeeDepartment';
      if (!$ap->setAttribute('Comment', $values['Comment']))       $err[] = 'Invalid value for Comment';

      if (!$err)
      {
         if ($err = $ap->save()) $errors[] = 'Row not added';
      }
      else $errors = array_merge($errors, $err);
      
      // ProjectAssignmentRecords
      $err = array();
      
      foreach ($schedule as $row)
      {
         if ($row['Hours'] == 0) continue;
         
         $ar = clone $arModel;

         if (!$ar->setAttribute('Employee', $values['Employee']))     $err[] = 'Invalid value for Employee';
         if (!$ar->setAttribute('Project',  $doc['Project']))         $err[] = 'Invalid value for Project';
         if (!$ar->setAttribute('Date',     $row['Date']))            $err[] = 'Invalid value for Period';
         if (!$ar->setAttribute('Hours',    $values['HoursPerDay']))  $err[] = 'Invalid value for Hours';
         if (!$ar->setAttribute('SubProject', $values['SubProject'])) $err[] = 'Invalid value for SubProject';
         if (!$ar->setAttribute('ProjectDepartment',  $department))   $err[] = 'Invalid value for ProjectDepartment';
         if (!$ar->setAttribute('EmployeeDepartment', $edep))         $err[] = 'Invalid value for EmployeeDepartment';
         if (!$ar->setAttribute('Comment', $values['Comment']))       $err[] = 'Invalid value for Comment';

         if (!$err)
         {
            if ($err = $ar->save()) $errors[] = 'Row not added';
         }
         else $errors = array_merge($errors, $err);
      }
   }
   
   if ($errors) throw new Exception(implode('<br>', $errors));
   
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
   
   $arModel = $container->getCModel('information_registry', 'ProjectAssignmentRecords');
   $apModel = $container->getCModel('information_registry', 'ProjectAssignmentPeriods');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   $arRes = $arModel->delete(true, $options);
   $apRes = $apModel->delete(true, $options);
   
   $return = (empty($arRes) && empty($apRes)) ? true : false;
   
   $event->setReturnValue($return);
}
