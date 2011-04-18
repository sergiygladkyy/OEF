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
   $attrs   = array(
      'Employee'    => array(),
      'Person'      => array(),
      'StaffRecord' => array()
   );
   $doc['desc'] = 'not exists';
   
   $container   = Container::getInstance();
   $user        = $container->getUser();
   $uploadDir   = Utility::getUploadDir($kind, 'NaturalPersons', 'Photo');
   $form_prefix = 'aeform['.$kind.']['.$type.']';
   
   // Retrieve NaturalPerson and Employee
   $person = $employee = array();
   
   if (!empty($params['person']))
   {
      $pid = (int) $params['person'];
      $employee = $container->getCModel('catalogs', 'Employees')->getEntities($pid, array('attributes' => 'NaturalPerson'));

      if ($employee === null || isset($employee['error']))
      {
         throw new Exception('Database error');
      }
      
      $employee = isset($employee[0]) ? $employee[0] : array();
      $person   = $container->getModel('catalogs', 'NaturalPersons');
      
      if (!$person->load($pid))
      {
         throw new Exception('Database error');
      }
      
      $empId = $employee ? $employee['_id'] : 0;
      
      $isCurrentEmployee = ($empId && $empId == MEmployees::retrieveCurrentEmployee() ? true : false);
   }
   else
   {
      $isCurrentEmployee = true;
      
      $empId = MEmployees::retrieveCurrentEmployee();
      
      if ($empId > 0)
      {
         $employee = $container->getModel('catalogs', 'Employees');

         if (!$employee->load($empId))
         {
            throw new Exception('Database error');
         }

         $person = $employee->getAttribute('NaturalPerson');  
      }
   }
   
   $gender = $container->getConfigManager()->getInternalConfigurationByKind('catalogs.field_prec', 'NaturalPersons');
   $gender = $gender['Gender']['in'];
   
   $attrs['Person'] = is_object($person) ? $person->toArray(array('with_link_desc' => true)) : $person;
   
   if ($empId)
   {
      // StaffHistoricalRecords
      $odb = $container->getODBManager();
      
      // Retrieve last record
      $query = "SELECT * FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".$empId." AND `Period` <= '".date('Y-m-d H:i:s')."'".
               "ORDER BY `Period` DESC LIMIT 1";

      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }

      if ($row && $row['RegisteredEvent'] != 'Firing')
      {
         $staff = $container->getModel('information_registry', 'StaffHistoricalRecords');

         if (!$staff->load($row['_id']))
         {
            throw new Exception('Database error');
         }

         $attrs['StaffRecord'] = $staff->toArray(array('with_link_desc' => true));
         
         $doc['type'] = $attrs['StaffRecord']['_rec_type'];
         $doc['id']   = $attrs['StaffRecord']['_rec_id'];
         $doc['desc'] = $container->getCModel('documents', $doc['type'])->retrieveLinkData($doc['id']);
         $doc['desc'] = $doc['desc'][$doc['id']]['text'];
      }
      
      $attrs['Employee'] = is_object($employee) ? $employee->toArray() : $employee;
   }
   
   $tag_id = isset($params['tag_id']) ? $params['tag_id'] : 'oef_custom_form';
   
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
