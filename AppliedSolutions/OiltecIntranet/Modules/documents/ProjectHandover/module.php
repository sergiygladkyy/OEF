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
   
   $pReg = $container->getModel('information_registry', 'ProjectHandoverRecords');
   
   // Post document
   $errors = array();
   $values = $document->toArray();
   unset(
      $values['_id'],
      $values['Code'],
      $values['Date'],
      $values['_deleted'],
      $values['_post']
   );
   
   if ($err = $pReg->fromArray($values))
   {
      foreach ($err as $attr => $err)
      {
         $errors[] = 'Invalid value for '.$attr;
      }
   }
   
   if (!$pReg->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   if (!$errors && $err = $pReg->save())
   {
      $errors = array('Row not added');
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
   
   $pModel = $container->getCModel('information_registry', 'ProjectHandoverRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   
   $pRes = $pModel->delete(true, $options);
   
   $return = empty($pRes) ? true : false;
   
   $event->setReturnValue($return);
}

/**
 * Print document
 * 
 * @param object $event
 * @return void
 */
function onPrint($event)
{
   $mockup   = new Mockup(self::$layout_dir.'ProjectHandover.php');
   $document = new TabularDoc();
   
   $area = $mockup->getArea('C1.R1:C22.R97');
   //$area->parameters['header'] = 'Project ManHours report ('.date('d-m-Y H:i:s').')';

   $document->put($area);
   
   $document->setGridAttributes($mockup->getGridAttributes());
   $document->addCSS($mockup->getCSS());
   
   echo $document->show();
}
