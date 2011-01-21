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

   $criterion = "WHERE `Owner` = ".$attrs['Owner']." AND `Employee` = '".$attrs['Employee']."'";
   
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
      $event->setReturnValue($return);
      return;
   }
   
   $model = $container->getCModel('information_registry', 'ProjectRegistrationRecords');
   
   if (null === ($res = $model->getEntities($doc['Project'], array('attributes' => 'Project'))) || isset($schedule['errors']))
   {
      throw new Exception('Database error');
   }
   elseif (empty($res[0]))
   {
      throw new Exception('Unknow project');
   }
   
   $department = $res[0]['ProjectDepartment'];
   
   $cmodel  = $container->getCModel('information_registry', 'Schedules');
   $irModel = $container->getModel('information_registry', 'ProjectAssignmentRecords');

   if (!$irModel->setRecorder($type, $id))
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
         throw new Exception('Database error '.$criterion);
      }
      elseif (empty($schedule))
      {
         continue;
      }
      
      foreach ($schedule as $row)
      {
         if ($row['Hours'] == 0) continue;
         
         $ir = clone $irModel;

         if (!$ir->setAttribute('Employee', $values['Employee']))     $err[] = 'Invalid value for Employee';
         if (!$ir->setAttribute('Project',  $doc['Project']))         $err[] = 'Invalid value for Project';
         if (!$ir->setAttribute('Date',     $row['Date']))            $err[] = 'Invalid value for Period';
         if (!$ir->setAttribute('Hours',    $values['HoursPerDay']))  $err[] = 'Invalid value for Hours';
         if (!$ir->setAttribute('SubProject', $values['SubProject'])) $err[] = 'Invalid value for SubProject';
         if (!$ir->setAttribute('ProjectDepartment',  $department))   $err[] = 'Invalid value for ProjectDepartment';
         if (!$ir->setAttribute('EmployeeDepartment', $edep))         $err[] = 'Invalid value for EmployeeDepartment';
         if (!$ir->setAttribute('Comment', $values['Comment']))       $err[] = 'Invalid value for Comment';

         if (!$err)
         {
            if ($err = $ir->save()) $errors[] = 'Row not added';
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
   
   $aModel = $container->getCModel('information_registry', 'ProjectAssignmentRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   $aRes = $aModel->delete(true, $options);
   
   $return = empty($aRes) ? true : false;
   
   $event->setReturnValue($return);
}
