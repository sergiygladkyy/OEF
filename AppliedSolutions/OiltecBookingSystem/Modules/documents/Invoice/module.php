<?php 

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
   
   $doc = Container::getInstance()->getModel('documents', 'Invoice');
   
   if (!$doc->load($attrs['Owner']))
   {
      $errors[] = 'Database error';
   }
   else if (null === ($PO = $doc->getAttribute('PO')))
   {
      $errors['PO'] = 'Unknow PO';
   }
   else
   {
      if (!PO::hasCourse($PO->getId(), $attrs['Course'], $attrs['CourseNumber']))
      {
         $errors['Course'] = 'Specified PO not contents this Course';
      }
   }
   
   if ($errors)
   {
      $event->setReturnValue($errors);
      
      return;
   }
   
   $cmodel = Container::getInstance()->getCModel('documents.Invoice.tabulars', 'Courses');

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
   
   // Retrieve Courses
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Courses');
   
   if (null === ($rows = $cmodel->getEntities($id, array('attributes' => 'Owner'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }

   $iReg = $container->getModel('information_registry', 'InvoiceRecords');
   
   if (!$iReg->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   // Post document
   $errors = array();
   
   if (null === ($pModel = $document->getAttribute('PO')))
   {
      throw new Exception('Database error');
   }
   
   if (null === ($lModel = $pModel->getAttribute('LearnersOrganization')))
   {
      throw new Exception('Database error');
   }
   
   $PO   = $pModel->getId();
   $lOrg = $lModel->getId();
   
   // PORecords
   foreach ($rows as $values)
   {
      $err = array();
      $ir  = clone $iReg;
      
      if (!$ir->setAttribute('PO',                   $PO))                     $err[] = 'Invalid value for PO';
      if (!$ir->setAttribute('Course',               $values['Course']))       $err[] = 'Invalid value for Course';
      if (!$ir->setAttribute('CourseNumber',         $values['CourseNumber'])) $err[] = 'Invalid value for CourseNumber';
      if (!$ir->setAttribute('LearnersOrganization', $lOrg))                   $err[] = 'Invalid value for LearnersOrganization';
      if (!$ir->setAttribute('Discount',             $values['Discount']))     $err[] = 'Invalid value for Discount';
      if (!$ir->setAttribute('Total',                $values['Total']))        $err[] = 'Invalid value for Total';
      
      if (!$err)
      {
         if ($err = $ir->save()) $errors[] = 'Can not add Invoice record: '.implode(", ", $err).".";
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
   
   $iModel = $container->getCModel('information_registry', 'InvoiceRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   
   $iRes = $iModel->delete(true, $options);
   
   $return = empty($iRes);
   
   $event->setReturnValue($return);
}
