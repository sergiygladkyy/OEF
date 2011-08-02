<?php 

/**
 * Called after standart validation, before saving item
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingRecord($event)
{
   $model  = $event->getSubject();
   $attrs  = $model->toArray();
   $errors = array(); 
   
   if (!Learners::hasOrganization($attrs['LearnersOrganization']))
   {
      $errors['LearnersOrganization'] = "This organization is not a learners organization";
   }
   
   $event->setReturnValue($errors);
}

/**
 * Called after standart validation, before saving tabular Courses item
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingCoursesRecord($event)
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
   
   // Retrieve Courses
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Courses');
   
   if (null === ($courses = $cmodel->getEntities($id, array('attributes' => 'Owner'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }

   $aReg = $container->getModel('information_registry', 'ApplicationFormRecords');
   
   if (!$aReg->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   // Post document
   $errors = array();
   
   $lo = $document->getAttribute('LearnersOrganization')->getId();
   
   // ApplicationFormRecords
   foreach ($courses as $values)
   {
      $err = array();
      $ir  = clone $aReg;
      
      if (!$ir->setAttribute('LearnersOrganization', $lo))                 $err[] = 'Invalid value for LearnersOrganization';
      if (!$ir->setAttribute('Course',         $values['Course']))         $err[] = 'Invalid value for Course';
      if (!$ir->setAttribute('CourseNumber',   $values['CourseNumber']))   $err[] = 'Invalid value for CourseNumber';
      if (!$ir->setAttribute('LearnersAmount', $values['LearnersAmount'])) $err[] = 'Invalid value for LearnersAmount';
      if (!$ir->setAttribute('StartDate',      $values['StartDate']))      $err[] = 'Invalid value for StartDate';
      
      if (!$err)
      {
         if ($err = $ir->save()) $errors[] = 'Row not added';
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
   
   $aModel = $container->getCModel('information_registry', 'ApplicationFormRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   
   $aRes = $aModel->delete(true, $options);
   
   $return = empty($aRes);
   
   $event->setReturnValue($return);
}
