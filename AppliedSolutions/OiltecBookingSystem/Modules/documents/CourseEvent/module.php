<?php 

/**
 * Called after standart validation, before saving item
 * 
 * @param object $event
 * @return void
 */
/*function onBeforeAddingRecord($event)
{
   $model  = $event->getSubject();
   $attrs  = $model->toArray();
   $errors = array(); 
   
   if (!LearnersOrgs::hasOrganization($attrs['LearnersOrganization']))
   {
      $errors['LearnersOrganization'] = "This organization is not a learners organization";
   }
   
   $event->setReturnValue($errors);
}*/

/**
 * Called after standart validation, before saving tabular Schedule item
 * 
 * @param object $event
 * @return void
 */
/*function onBeforeAddingCoursesRecord($event)
{
   $model  = $event->getSubject();
   $attrs  = $model->toArray();
   $errors = array();
    
   $cmodel = Container::getInstance()->getCModel('documents.ApplicationForm.tabulars', 'Courses');

   $criterion = "WHERE `Owner` = ".$attrs['Owner']." AND `Course` = ".$attrs['Course']." AND `CourseNumber` = ".$attrs['CourseNumber'];
   
   if (!empty($attrs['_id'])) $criterion .= " AND `_id` <> ".$attrs['_id'];
   
   if (null === ($courses = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   if (!empty($courses))
   {
      $errors['Course'] = 'Duplicate';
      $errors['CourseNumber'] = 'Must be unique for Course';
   }
   
   $event->setReturnValue($errors);
}*/

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
   
   // Retrieve Schedule
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Schedule');
   
   if (null === ($rows = $cmodel->getEntities($id, array('attributes' => 'Owner'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }

   $rReg = $container->getModel('information_registry', 'RoomsScheduleRecords');
   $iReg = $container->getModel('information_registry', 'InstructorsScheduleRecords');
   
   if (!$rReg->setRecorder($type, $id) || !$iReg->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   // Post document
   $errors = array();
   $attrs  = $document->toArray();
   
   foreach ($rows as $values)
   {
      // RoomsScheduleRecords
      
      $err = array();
      $ir  = clone $rReg;
      
      if (!$ir->setAttribute('ApplicationForm',      $attrs['ApplicationForm']))      $err[] = 'Invalid value for ApplicationForm';
      if (!$ir->setAttribute('LearnersOrganization', $attrs['LearnersOrganization'])) $err[] = 'Invalid value for LearnersOrganization';
      if (!$ir->setAttribute('Course',               $attrs['Course']))               $err[] = 'Invalid value for Course';
      if (!$ir->setAttribute('CourseNumber',         $attrs['CourseNumber']))         $err[] = 'Invalid value for CourseNumber';
      if (!$ir->setAttribute('Room',                 $values['Room']))                $err[] = 'Invalid value for Room';
      if (!$ir->setAttribute('DateTimeFrom',         $values['DateTimeFrom']))        $err[] = 'Invalid value for DateTimeFrom';
      if (!$ir->setAttribute('DateTimeTo',           $values['DateTimeTo']))          $err[] = 'Invalid value for DateTimeTo';
      
      if (!$err)
      {
         if ($err = $ir->save()) $errors[] = 'Can not add Rooms schedule record';
      }
      else $errors = array_merge($errors, $err);
   
      
      // InstructorsScheduleRecords
   
      $err = array();
      $ir  = clone $iReg;
      
      if (!$ir->setAttribute('ApplicationForm',      $attrs['ApplicationForm']))      $err[] = 'Invalid value for ApplicationForm';
      if (!$ir->setAttribute('LearnersOrganization', $attrs['LearnersOrganization'])) $err[] = 'Invalid value for LearnersOrganization';
      if (!$ir->setAttribute('Course',               $attrs['Course']))               $err[] = 'Invalid value for Course';
      if (!$ir->setAttribute('CourseNumber',         $attrs['CourseNumber']))         $err[] = 'Invalid value for CourseNumber';
      if (!$ir->setAttribute('Instructor',           $values['Instructor']))          $err[] = 'Invalid value for Instructor';
      if (!$ir->setAttribute('DateTimeFrom',         $values['DateTimeFrom']))        $err[] = 'Invalid value for DateTimeFrom';
      if (!$ir->setAttribute('DateTimeTo',           $values['DateTimeTo']))          $err[] = 'Invalid value for DateTimeTo';
      
      if (!$err)
      {
         if ($err = $ir->save()) $errors[] = 'Can not add Instructors schedule record';
      }
      else $errors = array_merge($errors, $err);
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
   
   $rModel = $container->getCModel('information_registry', 'RoomsScheduleRecords');
   $iModel = $container->getCModel('information_registry', 'InstructorsScheduleRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   
   $rRes = $rModel->delete(true, $options);
   $iRes = $iModel->delete(true, $options);
   
   $return = empty($rRes) && empty($iRes);
   
   $event->setReturnValue($return);
}
