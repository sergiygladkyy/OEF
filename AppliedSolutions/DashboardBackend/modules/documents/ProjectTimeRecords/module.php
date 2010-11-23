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
   $result   = $tsCModel->getEntities($id, array('attributes' => array('owner')));
   
   if (is_null($result) || isset($result['errors']))
   {
      $event->setReturnValue(false);
      return;
   }
   
   $notPosted = $container->getModel('information_registry', 'RejectedImportRecords');
   $irModel   = $container->getModel('information_registry', 'ResourcesAssignments');
   $return = true;
   $date = $document->getAttribute('date');
   $date = date('Y-m-d', MGlobal::dateToTimeStamp($date));
   
   // Check document period
   $db = $container->getODBManager();
   
   $query = "SELECT count(*) as cnt FROM `information_registry`.`ResourcesAssignments` ".
            "WHERE `Date` = '".$date."'";
   
   $res = $db->loadAssoc($query);
   
   if (is_null($res))
   {
      throw new Exception('Internal db error');
   }
   elseif ($res['cnt'] > 0)
   {
      throw new Exception('Data for this day has already been imported');
   }
   
   // Post document     
   foreach ($result as $values)
   {
      $cnt++;
      $errors = array();
      $ir  = clone $irModel;
      
      if (!$ir->setRecorder($type, $id))     $errors[] = 'Invalid recorder';
      if (!$ir->setAttribute('Date', $date)) $errors[] = 'Invalid type for "Date"';
      if (!$ir->setAttribute('Project', $values['Project']))       $errors[] = 'Invalid type for "Project"';
      if (!$ir->setAttribute('Subproject', $values['Subproject'])) $errors[] = 'Invalid type for "Subproject"';
      if (!$ir->setAttribute('Employee', $values['Employee']))     $errors[] = 'Invalid type for "Employee"';
      if (!$ir->setAttribute('BusinessArea', $values['BusinessArea']))       $errors[] = 'Invalid type for "BusinessArea"';
      if (!$ir->setAttribute('Number_of_hours', $values['Number_of_hours'])) $errors[] = 'Invalid type for "Number_of_hours"';
      
      if (!$errors) $errors = $ir->save();
      if (!$errors) continue;
      
      $ret = true;
      $ir  = clone $notPosted;
      
      $ret = $ret && $ir->setAttribute('Project', $values['Project']);
      $ret = $ret && $ir->setAttribute('Subproject', $values['Subproject']);
      $ret = $ret && $ir->setAttribute('Employee', $values['Employee']);
      $ret = $ret && $ir->setAttribute('BusinessArea', $values['BusinessArea']);
      $ret = $ret && $ir->setAttribute('Number_of_hours', $values['Number_of_hours']);
      $ret = $ret && $ir->setAttribute('DocumentType', $type);
      $ret = $ret && $ir->setAttribute('DocumentID', $id);
      $ret = $ret && $ir->setAttribute('Date', $date);
      $ret = $ret && $ir->setAttribute('Errors', implode('; ', $errors).'.');
      
      if ($ret)
      {
         if ($err = $ir->save()) $ret = false;
      }
      else $err[] = 'Some values have a wrong type';
      
      if (!$ret)
      {
         $return = false;
         break;
      }
   }
   
   if (!$return) throw new Exception('line: '.$cnt.'<br><pre>'.print_r($err, true).'</pre>');
   
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
   
   $irCModel = $container->getCModel('information_registry', 'ResourcesAssignments');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   $result = $irCModel->delete(true, $options);
   $return = empty($result) ? true : false;
   
   $event->setReturnValue($return);
}
?>
