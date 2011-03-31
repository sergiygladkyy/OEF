<?php 

/**
 * Called after standart validation, before saving tabular Milestones item
 * 
 * @param object $event
 * @return void
 */
/*function onBeforeAddingMilestonesRecord($event)
{
   $model  = $event->getSubject();
   $attrs  = $model->toArray();
   $errors = array();
   
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
   
   // Retrieve Milestones
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Milestones');
   
   if (null === ($mils = $cmodel->getEntities($id, array('attributes' => 'Owner', 'key' => '_id'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   $cReg = $container->getModel('information_registry', 'ContractRecords');
   $mReg = $container->getModel('information_registry', 'ContractMilestoneRecords');

   if (!$cReg->setRecorder($type, $id) || !$mReg->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   // Post document
   $errors = array();
   
   // ContractRecords
   $err  = array();
   $vals = $document->toArray();
   
   if (!$cReg->setAttribute('ContractNumber',        $vals['ContractNumber']))        $err[] = 'Invalid value for ContractNumber';
   if (!$cReg->setAttribute('Kind',                  $vals['Kind']))                  $err[] = 'Invalid value for Kind';
   if (!$cReg->setAttribute('ContractConclusionDate',$vals['ContractConclusionDate']))$err[] = 'Invalid value for ContractConclusionDate';
   if (!$cReg->setAttribute('TotalAmountNOK',        $vals['TotalAmountNOK']))        $err[] = 'Invalid value for TotalAmountNOK';
   if (!$cReg->setAttribute('DeliveryDate',          $vals['DeliveryDate']))          $err[] = 'Invalid value for DeliveryDate';
   
   if (!$err)
   {
      if ($err = $cReg->save()) $err = array('Row not added');
   }
   
   if ($err) throw new Exception(implode('<br>', $err));
   
   // ContractMilestoneRecords
   foreach ($mils as $values)
   {
      $err = array();
      $ir  = clone $mReg;
      
      if (!$ir->setAttribute('MilestoneName',      $values['MilestoneName']))      $err[] = 'Invalid value for MilestoneName';
      if (!$ir->setAttribute('MilestoneDeadline',  $values['MilestoneDeadline']))  $err[] = 'Invalid value for MilestoneDeadline';
      if (!$ir->setAttribute('MilestoneAmountNOK', $values['MilestoneAmountNOK'])) $err[] = 'Invalid value for MilestoneAmountNOK';
      
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
   
   $cModel = $container->getCModel('information_registry', 'ContractRecords');
   $mModel = $container->getCModel('information_registry', 'ContractMilestoneRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   
   $cRes = $cModel->delete(true, $options);
   $mRes = $mModel->delete(true, $options);
   
   $return = (empty($cRes) && empty($mRes)) ? true : false;
   
   $event->setReturnValue($return);
}
