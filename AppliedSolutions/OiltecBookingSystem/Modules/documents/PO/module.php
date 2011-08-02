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
      $lOrg = $appForm->getAttribute('LearnersOrganization');
      
      if ($lOrg === null || $lOrg->getId() != $attrs['LearnersOrganization'])
      {
         $errors['LearnersOrganization'] = 'Specified Application form contains other LearnersOrganization';
      }
   }
   
   $event->setReturnValue($errors);
}

/**
 * Called after standart validation, before saving tabular Orders item
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingOrdersRecord($event)
{
   $model  = $event->getSubject();
   $attrs  = $model->toArray();
   $errors = array();
   
   $doc = Container::getInstance()->getModel('documents', 'PO');
   
   if (!$doc->load($attrs['Owner']))
   {
      $errors[] = 'Database error';
   }
   else if (null === ($appForm = $doc->getAttribute('ApplicationForm')))
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
   
   if ($errors)
   {
      $event->setReturnValue($errors);
      
      return;
   }
   
   $cmodel = Container::getInstance()->getCModel('documents.PO.tabulars', 'Orders');

   $criterion = "WHERE `Owner` = ".$attrs['Owner']." AND `Course` = ".$attrs['Course']." AND `CourseNumber` = ".$attrs['CourseNumber'];
   
   if (!empty($attrs['_id'])) $criterion .= " AND `_id` <> ".$attrs['_id'];
   
   if (null === ($rows = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   if (!empty($rows))
   {
      $errors['Course'] = 'Duplicate';
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
   
   // Retrieve Orders
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Orders');
   
   if (null === ($rows = $cmodel->getEntities($id, array('attributes' => 'Owner'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }

   $pReg = $container->getModel('information_registry', 'PORecords');
   
   if (!$pReg->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   // Post document
   $errors = array();
   $attrs  = $document->toArray();
   
   // PORecords
   foreach ($rows as $values)
   {
      $err = array();
      $ir  = clone $pReg;
      
      if (!$ir->setAttribute('ApplicationForm',      $attrs['ApplicationForm']))      $err[] = 'Invalid value for ApplicationForm';
      if (!$ir->setAttribute('Course',               $values['Course']))              $err[] = 'Invalid value for Course';
      if (!$ir->setAttribute('CourseNumber',         $values['CourseNumber']))        $err[] = 'Invalid value for CourseNumber';
      if (!$ir->setAttribute('LearnersOrganization', $attrs['LearnersOrganization'])) $err[] = 'Invalid value for LearnersOrganization';
      if (!$ir->setAttribute('LearnersAmount',       $values['LearnersAmount']))      $err[] = 'Invalid value for LearnersAmount';
      
      if (!$err)
      {
         if ($err = $ir->save()) $errors[] = 'Can not add PO record: '.implode(", ", $err).".";
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
   
   $pModel = $container->getCModel('information_registry', 'PORecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   
   $pRes = $pModel->delete(true, $options);
   
   $return = empty($pRes);
   
   $event->setReturnValue($return);
}
