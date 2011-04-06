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
   $kind    = $subject->getKind();
   $type    = $subject->getType();
   $name    = $event['name'];
   $params  = $event['parameters'];
   $attrs   = array(array(
      'Employee'    => array(),
      'Person'      => array(),
      'StaffRecord' => array())
   );

   $uploadDir   = Utility::getUploadDir($kind, 'NaturalPersons', 'Photo');
   $form_prefix = 'aeform['.$kind.']['.$type.']';
   $container   = Container::getInstance();

   $gender = $container->getConfigManager()->getInternalConfigurationByKind('catalogs.field_prec', 'NaturalPersons');
   $gender = $gender['Gender']['in'];

   
      $odb = Container::getInstance()->getODBManager();

      // Retrieve last record
      $query = "SELECT * FROM catalogs.NaturalPersons ";
      if (null === ($rows = $odb->loadAssocList($query)))
      {
         throw new Exception('Database error');
      }
      $i=0;
      foreach($rows as $row)
      {
          $employee = $container->getModel('catalogs', 'Employees');
          if (!$employee->load($row['_id']))
          {
            throw new Exception('Database error');
          }
          $person = $employee->getAttribute('NaturalPerson');

          $attrs[$i]['Person'] = $person->toArray(array('with_link_desc' => true));

          $attrs[$i]['Employee'] = $employee->toArray(array('with_link_desc' => true));

          $query = "SELECT * FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".$attrs[$i]['Employee'][_id]." AND `Period` <= '".date('Y-m-d H:i:s')."'".
               "ORDER BY `Period` DESC LIMIT 1";
          
          $odb = Container::getInstance()->getODBManager();
          if (null === ($rowStaff = $odb->loadAssoc($query)))
          {
             throw new Exception('Database error');
          }

          if ($rowStaff && $rowStaff['RegisteredEvent'] != 'Firing')
          {
             $staff = $container->getModel('information_registry', 'StaffHistoricalRecords');

             if (!$staff->load($rowStaff['_id']))
             {
                throw new Exception('Database error');
             }

             $attrs[$i]['StaffRecord'] = $staff->toArray(array('with_link_desc' => true));
          }
          $i++;
      }


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
   $errors = array();
   $values = $event['values'];

   if (empty($values['_id']) || !is_numeric($values['_id']) || 0 >= (int) $values['_id'])
   {
      $event->setReturnValue(array('status' => false, 'result' => array('msg' => 'Unknow employee')));
      return;
   }
   
   $id = (int) $values['_id'];
   
   import('lib.model.base.BaseModel');
   
   if (!BaseModel::hasEntity('catalogs', 'NaturalPersons', $id))
   {
      $event->setReturnValue(array('status' => false, 'result' => array('msg' => 'Employee not exists')));
      return;
   }
   
   $controller = Container::getInstance()->getController('catalogs', 'NaturalPersons');
      
   $return = $controller->update($values);
   
   if ($return['status']) unset($return['result']['_id']);
   
   $event->setReturnValue($return);
}
