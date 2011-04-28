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
   $select  = array();
   $attrs   = array(
      'Employee'    => array(),
      'Person'      => array(),
      'StaffRecord' => array(),
      'Locations'   => array()
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
      
      // Locations
      $cmodel    = $container->getCModel('catalogs.Employees.tabulars', 'Locations');
      $locations = $cmodel->getEntities($empId, array('attributes' => 'Owner', 'with_link_desc' => true));
      
      if ($locations === null || isset($locations['errors']))
      {
         throw new Exception('Database error');
      }
      
      $attrs['Locations'] = $locations;
      $select = $cmodel->retrieveSelectDataForRelated('Location');
   }
   
   $tag_id = isset($params['tag_id']) ? $params['tag_id'] : 'oef_custom_form';
   $tab = empty($params['tab']) || !in_array($params['tab'], array('tab1', 'tab2')) ? 'tab1' : $params['tab'];
   
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
   $object = new self();
   $method = isset($_POST['tab']) ? 'process'.ucfirst($_POST['tab']) : null;
   
   if (!$method || !is_callable(array($object, $method), true))
   {
      $event->setReturnValue(array('status' => false, 'result' => array('msg' => 'Unknow form')));
      return;
   }
   
   $return = call_user_func(array($object, $method), $values);

   $event->setReturnValue($return);
}

/**
 * Process tab1 form
 * 
 * @param array $values
 * @return array
 */
function processTab1(array $values)
{
   if (empty($values['_id']) || !is_numeric($values['_id']) || 0 >= (int) $values['_id'])
   {
      return array('status' => false, 'result' => array('msg' => 'Unknow natural person'));
   }

   $id = (int) $values['_id'];

   import('lib.model.base.BaseModel');

   if (!BaseModel::hasEntity('catalogs', 'NaturalPersons', $id))
   {
      $event->setReturnValue(array('status' => false, 'result' => array('msg' => 'Natural person not exists')));
      return;
   }

   $controller = Container::getInstance()->getController('catalogs', 'NaturalPersons');

   $return = $controller->update($values);

   if ($return['status']) unset($return['result']['_id']);
   
   return $return;
}

/**
 * Process tab2 form
 * 
 * @param array $params
 * @return array
 */
function processTab2(array $params)
{
   if (empty($params['_id']) || !is_numeric($params['_id']) || 0 >= (int) $params['_id'])
   {
      return array('status' => false, 'result' => array('msg' => 'Unknow employee'));
   }
   
   $result = array();
   $owner  = (int) $params['_id'];
   $params = $params['tabulars']['Locations'];
   
   import('lib.model.base.BaseModel');

   if (!BaseModel::hasEntity('catalogs', 'Employees', $owner))
   {
      return array('status' => false, 'result' => array('msg' => 'Employee not exists'));
   }

   $controller = Container::getInstance()->getController('catalogs.Employees.tabulars', 'Locations');

   // Delete
   if (isset($params['deleted']))
   {
      if (!empty($params['deleted']))
      {
         $options = array(
            'attributes' => array('%pkey', 'Owner'),
            'criterion'  => '`Owner` = %%Owner%% AND `%pkey` IN (%%pkey%%)'
         );
         $result['delete'] = $controller->delete(array('%pkey' => $params['deleted'], 'Owner' => $owner), $options);
      }

      unset($params['deleted']);
   }

   // Save all
   foreach ($params as $key => $values)
   {
      $values['Owner'] = $owner;

      $action = isset($values['_id']) ? 'update' : 'create';

      $result[$key] = $controller->$action($values);

      if ($result[$key]['status'] && $action == 'update')
      {
         unset($result[$key]['result']['_id']);
      }
   }
   
   return array(
      'status'   => true,
      'result'   => array('msg' => 'Updated successfully'),
      'tabulars' => array(
         'Locations' => $result
      )
   );
}

/**
 * Include template
 * 
 * @param string $name - template name
 * @param $params      - list of attributes
 * @return string
 */
function include_template($name, $params)
{
   extract($params, EXTR_OVERWRITE);
   
   ob_start();
   
   include(self::$templates_dir.'_'.$name.'.php');
   
   return ob_get_clean();
}
