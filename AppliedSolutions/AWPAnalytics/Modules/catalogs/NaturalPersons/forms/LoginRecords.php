<?php
/**
 * Generate form
 * 
 * @param object $event
 * @return void
 */
function onGenerate($event)
{
   $subject = $event->getSubject();
   $name    = $event['name'];
   $params  = $event['parameters'];
   
   if (!defined('IS_SECURE')) return;
   
   $form_prefix = 'aeform['.$subject->getKind().']['.$subject->getType().']';
   $container   = Container::getInstance();
   
   // Retrieve list of all users
   $cmodel = $container->getCModel('catalogs', 'SystemUsers');
   
   $criterion = "ORDER BY `User` ASC, `AuthType` ASC";
   
   if (null === ($users = $cmodel->getEntities(null, array('criterion' => $criterion, 'key' => '_id'))) || isset($users['errors']))
   {
      throw new Exception('Database error');
   }
   
   // Retrieve list of associated users
   $cmodel = $container->getCModel('information_registry', 'LoginRecords');
   
   if (null === ($records = $cmodel->getEntities(null, array('with_link_desc' => true))) || isset($records['errors']))
   {
      throw new Exception('Database error');
   }
   /*echo '<pre>'.print_r($users, true).'</pre>';
   echo '<pre>'.print_r($records, true).'</pre>';
   */
   // Current person
   $person = empty($params['person']) ? 0 : $params['person'];
   
   foreach ($records['list'] as $row)
   {
      $users[$row['SystemUser']]['NaturalPerson'] = $row['NaturalPerson'];
      $users[$row['SystemUser']]['LoginRecords']  = $row['_id'];
   }
   
   $plinks =& $records['links']['NaturalPerson'];
   
   include(self::$templates_dir.$name.'.php');
}

/**
 * Process form
 * 
 * @param object $event
 * @return void
 */
function onProcess($event)
{
   $container = Container::getInstance();
   
   $errors  = array();
   $values  = $event['values'];
   $records = empty($values['Records']) ? array() : $values['Records'];
   
   unset($values['Records']);
   
   $model = $container->getModel('catalogs', 'NaturalPersons');
    
   if (!($ret = $model->fromArray($values)))
   {
      if ($ret = $model->save()) $errors = $ret;
   }
   else $errors = $ret;
   
   if ($errors)
   {
      $event->setReturnValue(array(
         'status' => false,
         'result' => array(
            'msg' => 'Catalog not '.(isset($values['_id']) ? 'updated' : 'created')
         ),
         'errors' => $errors
      ));
      
      return;
   }
   
   $result = array();
   $person = $model->getId();
   
   if (!isset($values['_id']))
   {
      $result['_id'] = $person;
   }
   
   // Check Login records
   $ids = array();
   $add = array();
   
   $cmodel = $container->getCModel('information_registry', 'LoginRecords');
   
   foreach ($records as $val)
   {
      $vals = explode(' ', $val);
      
      if (isset($vals[2]))
      {
         $ids[] = $vals[2];
      }
      else
      {
         $criterion = "WHERE `SystemUser`=".$vals[0]." AND `AuthType`='".$vals[1]."'";
         
         if (null === ($res = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($res['errors']))
         {
            throw new Exception('Database error');
         }
         
         if (!empty($res) && $res[0]['NaturalPerson'] != $person)
         {
            $errors[] = 'One User can not be associated with two NaturalPersons';
         }
         else
         {
            $add[] = array(
               'SystemUser' => $vals[0],
               'AuthType'   => $vals[1]
            );
         }
      }
   }
   
   if ($errors)
   {
      $event->setReturnValue(array(
         'status' => false,
         'result' => array(
            'msg' => 'Login Records not updated'
         ),
         'errors' => $errors
      ));
      
      return;
   }
   
   // Delete records
   if (!empty($ids))
   {
      $options = array(
         'attributes' => array('%pkey'),
         'criterion'  => "`NaturalPerson`=".$person." AND `%pkey` NOT IN (".implode(',', $ids).")"
      );
       
      if ($cmodel->delete(true, $options))
      {
         throw new Exception('Database error');
      }
   }
   
   // Add records
   $model = $container->getModel('information_registry', 'LoginRecords');
   
   foreach ($add as $vals)
   {
      $ir = clone $model;
      
      if (!$ir->setAttribute('NaturalPerson', $person))          $err[] = 'Invalid value for NaturalPerson';
      if (!$ir->setAttribute('AuthType',   $vals['AuthType']))   $err[] = 'Invalid value for AuthType';
      if (!$ir->setAttribute('SystemUser', $vals['SystemUser'])) $err[] = 'Invalid value for SystemUser';
      
      if (!$err)
      {
         if ($err = $ir->save()) $errors = array_merge($errors, $err);
      }
      else $errors = array_merge($errors, $err);
   }
   
   if (!$errors)
   {
      $status = true;
      $result['msg'] = (isset($values['_id']) ? 'Updated' : 'Created').' sucessfully';
   }
   else
   {
      $status = false;
      $result['msg'] = 'Login Records not updated';
   }
   
   $event->setReturnValue(array(
      'status'   => $status,
      'result'   => $result,
      'errors'   => $errors
   ));
}
