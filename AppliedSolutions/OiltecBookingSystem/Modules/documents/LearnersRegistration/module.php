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
   
   if (null === ($appForm = $model->getAttribute('ApplicationForm')))
   {
      $errors['ApplicationForm'] = 'Unknow application form';
   }
   else
   {
      if (!ApplicationForm::hasCourse($appForm->getId(), $attrs['Course'], $attrs['CourseNumber']))
      {
         $errors['Course'] = 'Specified Application form not contents this Course';
      }
   }
   
   $event->setReturnValue($errors);
}

/**
 * Called after standart validation, before saving tabular Learners item
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingLearnersRecord($event)
{
   $model  = $event->getSubject();
   $attrs  = $model->toArray();
   $errors = array();
    
   $cmodel = Container::getInstance()->getCModel('documents.LearnersRegistration.tabulars', 'Learners');

   $criterion = "WHERE `Owner` = ".$attrs['Owner']." AND `Learner` = ".$attrs['Learner'];
   
   if (!empty($attrs['_id'])) $criterion .= " AND `_id` <> ".$attrs['_id'];
   
   if (null === ($rows = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   if (!empty($rows))
   {
      $errors['Learner'] = 'Duplicate';
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
   
   // Retrieve Learners
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Learners');
   
   if (null === ($rows = $cmodel->getEntities($id, array('attributes' => 'Owner'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }

   $rReg = $container->getModel('information_registry', 'LearnersRegistrationRecords');
   
   if (!$rReg->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   // Post document
   $errors = array();
   $attrs  = $document->toArray();
   $lModel = $container->getModel('catalogs', 'Learners');
   
   // LearnersRegistrationRecords
   foreach ($rows as $values)
   {
      $err = array();
      $ir  = clone $rReg;
      
      if (!$lModel->load($values['Learner']))
      {
         $errors[] = "Unknow Learner";
         
         continue;
      }
      
      $learner = $lModel->toArray();
      
      if (!$ir->setAttribute('ApplicationForm',      $attrs['ApplicationForm']))        $err[] = 'Invalid value for ApplicationForm';
      if (!$ir->setAttribute('Course',               $attrs['Course']))                 $err[] = 'Invalid value for Course';
      if (!$ir->setAttribute('Learner',              $values['Learner']))               $err[] = 'Invalid value for Learner';
      if (!$ir->setAttribute('CourseNumber',         $attrs['CourseNumber']))           $err[] = 'Invalid value for CourseNumber';
      if (!$ir->setAttribute('LearnersOrganization', $learner['LearnersOrganization'])) $err[] = 'Invalid value for LearnersOrganization';
      if (!$ir->setAttribute('Name',                 $learner['Name']))                 $err[] = 'Invalid value for Name';
      if (!$ir->setAttribute('Surname',              $learner['Surname']))              $err[] = 'Invalid value for Surname';
      if (!$ir->setAttribute('Email',                $learner['Email']))                $err[] = 'Invalid value for Email';
      
      if (!$err)
      {
         if ($err = $ir->save()) $errors[] = 'Can not add LearnersRegistration record: '.implode(", ", $err).".";
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
   
   $rModel = $container->getCModel('information_registry', 'LearnersRegistrationRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   
   $rRes = $rModel->delete(true, $options);
   
   $return = empty($rRes);
   
   $event->setReturnValue($return);
}
