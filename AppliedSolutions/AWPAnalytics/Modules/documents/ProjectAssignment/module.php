<?php 

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
   $type = $document->getType();
   $id   = $document->getId();
   
   $tsCModel = $container->getCModel($document->getKind().'.'.$type.'.tabulars', 'Records');
   $result   = $tsCModel->getEntities($id, array('attributes' => array('Owner')));
   
   if (is_null($result) || isset($result['errors']))
   {
      $event->setReturnValue(false);
      return;
   }
   
   $irModel   = $container->getModel('information_registry', 'ProjectAssignmentRecords');
   $return = true;
   $date = $document->getAttribute('Date');
   $date = date('Y-m-d H:i:s', MGlobal::dateToTimeStamp($date));
   
   $errors = array();
   
   // Post document     
   foreach ($result as $values)
   {
      $cnt++;
      $err = array();
      $ir  = clone $irModel;
      
      if (!$ir->setRecorder($type, $id))                         $err[] = 'Invalid recorder';
      if (!$ir->setAttribute('Resource', $values['Resource']))   $err[] = 'Invalid value for "Resource"';
      if (!$ir->setAttribute('Project', $values['Project']))     $err[] = 'Invalid value for "Project"';
      if (!$ir->setAttribute('Period', $date))                   $err[] = 'Invalid value for "Period"';
      if (!$ir->setAttribute('BudgetHRS', $values['BudgetHRS'])) $err[] = 'Invalid value for "BudgetHRS"';
      if (!$ir->setAttribute('Rate', $values['Rate']))           $err[] = 'Invalid value for "Rate"';
      
      if (!$err)
      {
         if ($err = $ir->save()) $errors[] = 'Row not added';
      }
      else $errors = array_merge($errors, $err);
   }
   
   if ($errors) throw new Exception(implode('<br>', $errors));
   
   $event->setReturnValue($return);
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
   
   $irCModel = $container->getCModel('information_registry', 'ProjectAssignmentRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   $result = $irCModel->delete(true, $options);
   $return = empty($result) ? true : false;
   
   $event->setReturnValue($return);
}
?>
