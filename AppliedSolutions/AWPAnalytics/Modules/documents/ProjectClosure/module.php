<?php 

/**
 * Called after standart validation, before saving
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingRecord($event)
{
   $model  = $event->getSubject();
   $attrs  = $model->toArray();
   $errors = array(); 
   
   $container = Container::getInstance();
   
   $ir = $container->getModel('information_registry', 'ProjectRegistrationRecords');
   
   if (!$ir->loadByDimensions(array('Project' => $attrs['Project'])))
   {
      $event->setReturnValue(array('Project' => 'Not registered'));
      return;
   }

   if (($end = strtotime($attrs['ClosureDate'])) === -1)
   {
      $errors['ClosureDate'] = 'Invalid date format';
   }
   elseif (!($end > strtotime($ir->getAttribute('StartDate'))))
   {
      $errors['ClosureDate'] = 'Should exceed Project StartDate ('.$ir->getAttribute('StartDate').')';
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
   if ($links = MProjects::isClose($doc['Project']))
   {
      MGlobal::returnMessageByLinks($links);
   }
   
   $ir = $container->getModel('information_registry', 'ProjectClosureRecords');

   if (!$ir->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   // Post document
   $errors = array();
    
   if (!$ir->setAttribute('Project',     $doc['Project']))        $err[] = 'Invalid value for Project';
   if (!$ir->setAttribute('ClosureDate', $doc['ClosureDate']))    $err[] = 'Invalid value for ClosureDate';
   if (!$ir->setAttribute('Comment',     $doc['ClosureComment'])) $err[] = 'Invalid value for Comment';

   if (!$err)
   {
      if ($err = $ir->save()) $errors[] = 'Row not added';
   }
   else $errors = $err;

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
   
   $cModel = $container->getCModel('information_registry', 'ProjectClosureRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   $cRes = $cModel->delete(true, $options);
   
   $return = empty($cRes) ? true : false;
   
   $event->setReturnValue($return);
}
