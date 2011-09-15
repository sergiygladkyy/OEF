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
   $data    = array();
   
   $uploadDir   = Utility::getUploadDir($kind, 'NaturalPersons', 'Photo');
   $form_prefix = 'aeform['.$kind.']['.$type.']';
   $container   = Container::getInstance();
   
   $data = self::getNotFiredEmployeesGroupByUnit();
   
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




/**
 * Return list of not fired Employees
 * 
 * @return array
 */
function getNotFiredEmployeesGroupByUnit()
{
   $data    = array();
   $staff   = array();
   $empIDS  = array();
   $posIDS  = array();
   $odb     = Container::getInstance()->getODBManager();
   
   $query = "SELECT `_id`, `Description`, `_deleted` FROM catalogs.OrganizationalUnits ORDER BY `Description` ASC";
   
   if (!($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }

   while ($row = $odb->fetchAssoc($res))
   {
      $data[$row['_id']] = array('unit' => $row, 'list' => array());
   }
   
   $query = "SELECT `Employee`, MAX(`Period`) AS `Period`, `OrganizationalUnit`, `OrganizationalPosition`, `RegisteredEvent` ".
            "FROM information_registry.StaffHistoricalRecords ".
            "GROUP BY `Employee`";

   if (!($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }

   while ($row = $odb->fetchAssoc($res))
   {
      if ($row['RegisteredEvent'] == 'Firing') continue;
       
      $staff[$row['Employee']]  = $row;
      $empIDS[$row['Employee']] = $row['Employee'];
      $posIDS[$row['OrganizationalPosition']] = $row['OrganizationalPosition'];
   }

   $query = "SELECT np.`Description`, np.`Name`, np.`Surname`, np.`Phone`, np.`Photo`, np.`_id` AS `NaturalPerson`, e.`_id` AS `Employee` ".
            "FROM catalogs.Employees AS e, catalogs.NaturalPersons AS np ".
            "WHERE e.`_id` IN (".implode(',', $empIDS).") AND e.`NaturalPerson`=np.`_id` AND e.`_deleted` = 0 ".
            "ORDER BY np.`Description`";

   if (!($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }

   // Link Description
   if(!empty($posIDS))
   {
      if (null === ($links['OrganizationalPosition'] = Container::getInstance()->getCModel('catalogs', 'OrganizationalPositions')->retrieveLinkData($posIDS)))
      {
         throw new Exception('Database error');
      }
   }
   
   // Prepare data
   while ($row = $odb->fetchAssoc($res))
   {
      $pid  = $row['NaturalPerson'];
      $unit = $staff[$row['Employee']]['OrganizationalUnit'];
      
      $data[$unit]['list'][$pid] = array_merge($row, $staff[$row['Employee']]);
      $data[$unit]['list'][$pid]['OrganizationalPosition'] = $links['OrganizationalPosition'][$data[$unit]['list'][$pid]['OrganizationalPosition']];
   }
   
   return $data;
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