<?php 

/**
 * Called after standart validation, before saving tabular Subprojects item
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingSubprojectsRecord($event)
{
   $model  = $event->getSubject();
   $attrs  = $model->toArray();
   $errors = array(); 
   
   $doc = Container::getInstance()->getModel('documents', 'ProjectRegistration');
   $doc->load($attrs['Owner']);
   
   $sub = Container::getInstance()->getModel('catalogs', 'SubProjects');
   if ($sub->load($attrs['SubProject']))
   {
      $d = $doc->toArray();
      
      if ($d['Project'] != $sub->getAttribute('Project')->getId())
      {
         $errors['SubProject'] = 'Invalid SubProject';
      }
   }
   else $errors['SubProject'] = 'Unknow SubProject';
   
   
   
   $event->setReturnValue($errors);
}

/**
 * Called after standart validation, before saving tabular Milestones item
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingMilestonesRecord($event)
{
   $model  = $event->getSubject();
   $attrs  = $model->toArray();
   $errors = array();
    
   $cmodel = Container::getInstance()->getCModel('documents.ProjectRegistration.tabulars', 'Milestones');

   $criterion = "WHERE `Owner` = ".$attrs['Owner']." AND `MileStoneName` = '".$attrs['MileStoneName']."'";
   
   if (!empty($attrs['_id'])) $criterion .= " AND `_id` <> ".$attrs['_id'];
   
   if (null === ($mils = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   if (!empty($mils))
   {
      $errors['MileStoneName'] = 'Duplicate';
   }
   
   $doc = Container::getInstance()->getModel('documents', 'ProjectRegistration');
   $doc->load($attrs['Owner']);
   
   if (($mts = strtotime($attrs['MileStoneDeadline'])) === -1)
   {
      $errors['MileStoneDeadline'] = 'Invalid date format';
   }
   elseif ($mts > strtotime($doc->getAttribute('DeliveryDate')))
   {
      $errors['MileStoneDeadline'] = 'Should not exceed project DeliveryDate';
   }
   
   /*if ($errors)
   {
      if ($cmodel->delete($attrs['Owner'], array('attributes' => 'Owner')))
      {
         throw new Exception('Database error');
      }
   }*/
   
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
   
   // Retrieve SubProjects
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Subprojects');
   
   if (null === ($subs = $cmodel->getEntities($id, array('attributes' => 'Owner'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }

   // Retrieve Milestones
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Milestones');
   
   if (null === ($mils = $cmodel->getEntities($id, array('attributes' => 'Owner', 'key' => '_id'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   $pReg = $container->getModel('information_registry', 'ProjectRegistrationRecords');
   $sReg = $container->getModel('information_registry', 'SubprojectRegistrationRecords');
   $mReg = $container->getModel('information_registry', 'MilestoneRecords');

   if (!$pReg->setRecorder($type, $id) || !$sReg->setRecorder($type, $id) || !$mReg->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   // Post document
   $errors = array();
   
   // ProjectRegistrationRecords
   $err  = array();
   $vals = $document->toArray();
   
   if (!$pReg->setAttribute('Project',           $vals['Project']))           $err[] = 'Invalid value for Project';
   if (!$pReg->setAttribute('ProjectDepartment', $vals['ProjectDepartment'])) $err[] = 'Invalid value for ProjectDepartment';
   if (!$pReg->setAttribute('ProjectManager',    $vals['ProjectManager']))    $err[] = 'Invalid value for ProjectManager';
   if (!$pReg->setAttribute('BudgetHRS',         $vals['BudgetHRS']))         $err[] = 'Invalid value for BudgetHRS';
   if (!$pReg->setAttribute('BudgetNOK',         $vals['BudgetNOK']))         $err[] = 'Invalid value for BudgetNOK';
   if (!$pReg->setAttribute('StartDate',         $vals['StartDate']))         $err[] = 'Invalid value for StartDate';
   if (!$pReg->setAttribute('DeliveryDate',      $vals['DeliveryDate']))      $err[] = 'Invalid value for DeliveryDate';
   if (!$pReg->setAttribute('Customer',          $vals['Customer']))          $err[] = 'Invalid value for Customer';
   
   if (!$err)
   {
      if ($err = $pReg->save()) $err = array('Row not added');
   }
   
   if ($err) throw new Exception(implode('<br>', $err));
   
   // SubprojectRegistrationRecords
   foreach ($subs as $values)
   {
      $err = array();
      $ir  = clone $sReg;
      
      if (!$ir->setAttribute('Project',    $vals['Project']))      $err[] = 'Invalid value for Project';
      if (!$ir->setAttribute('SubProject', $values['SubProject'])) $err[] = 'Invalid value for SubProject';
      
      if (!$err)
      {
         if ($err = $ir->save()) $errors[] = 'Row not added';
      }
      else $errors = array_merge($errors, $err);
   }
   
   // MilestoneRecords
   foreach ($mils as $values)
   {
      $err = array();
      $ir  = clone $mReg;
      
      if (!$ir->setAttribute('Project',          $vals['Project']))             $err[] = 'Invalid value for Project';
      if (!$ir->setAttribute('MileStoneName',    $values['MileStoneName']))     $err[] = 'Invalid value for MileStoneName';
      if (!$ir->setAttribute('MileStoneDeadline',$values['MileStoneDeadline'])) $err[] = 'Invalid value for MileStoneDeadline';
      
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
   
   $pModel = $container->getCModel('information_registry', 'ProjectRegistrationRecords');
   $sModel = $container->getCModel('information_registry', 'SubprojectRegistrationRecords');
   $mModel = $container->getCModel('information_registry', 'MilestoneRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   
   $pRes = $pModel->delete(true, $options);
   $sRes = $sModel->delete(true, $options);
   $mRes = $mModel->delete(true, $options);
   
   $return = (empty($pRes) && empty($sRes) && empty($mRes)) ? true : false;
   
   $event->setReturnValue($return);
}
