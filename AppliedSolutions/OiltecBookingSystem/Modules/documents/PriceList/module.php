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
   $kind = $document->getKind();
   $type = $document->getType();
   $id   = $document->getId();
   $date = $document->getAttribute('Date');
   
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Costs');
   
   if (null === ($costs = $cmodel->getEntities($id, array('attributes' => 'Owner'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   $irModel = $container->getModel('information_registry', 'PriceListRecords');

   if (!$irModel->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   // Post document
   $errors = array();
   
   foreach ($costs as $row)
   {
      $err = array();
      $ir  = clone $irModel;
      
      if (!$ir->setAttribute('Course', $row['Course'])) $err[] = 'Invalid value for Course';
      if (!$ir->setAttribute('Period', $date))          $err[] = 'Invalid value for Period';
      if (!$ir->setAttribute('Cost',   $row['Cost']))   $err[] = 'Invalid value for Cost';

      if (!$err)
      {
         if ($err = $ir->save()) $errors[] = 'Can not add Price list record: '.implode(", ", $err).".";
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
   
   $cModel = $container->getCModel('information_registry', 'PriceListRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   $cRes = $cModel->delete(true, $options);
   
   $return = empty($cRes) ? true : false;
   
   $event->setReturnValue($return);
}
