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
   
   if (empty($params['filter']) || !is_string($params['filter']))
   {
      $params['filter'] = 'All';
   }
   
   switch($params['filter'])
   {
      case 'ActiveEmployee':
         $data = self::getActiveEmployees();
         break;
         
      case 'AllEmployee':
         $data = self::getAllEmployees();
         break;
         
      default:
         $data = self::getAllNaturalPersons();
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




/**
 * Return list of active Employee
 * 
 * @return array
 */
function getActiveEmployees()
{
   $data    = array();
   $staff   = array();
   $empIDS  = array();
   $unitIDS = array();
   $posIDS  = array();
   $odb     = Container::getInstance()->getODBManager();
    
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
      $unitIDS[$row['OrganizationalUnit']]    = $row['OrganizationalUnit'];
      $posIDS[$row['OrganizationalPosition']] = $row['OrganizationalPosition'];
   }

   $query = "SELECT np.`Name`, np.`Surname`, np.`Phone`, np.`Photo`, np.`_id` AS `NaturalPerson`, e.`_id` AS `Employee` ".
            "FROM catalogs.Employees AS e, catalogs.NaturalPersons AS np ".
            "WHERE e.`_id` IN (".implode(',', $empIDS).") AND e.`NaturalPerson`=np.`_id` AND e.`_deleted` = 0";

   if (!($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }

   // Link Description
   if (!empty($unitIDS))
   {
      if (null === ($links['OrganizationalUnit'] = Container::getInstance()->getCModel('catalogs', 'OrganizationalUnits')->retrieveLinkData($unitIDS)))
      {
         throw new Exception('Database error');
      }
   }

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
      $pid = $row['NaturalPerson'];
      
      $data[$pid] = array_merge($row, $staff[$row['Employee']]);
      
      $data[$pid]['OrganizationalUnit']     = $links['OrganizationalUnit'][$data[$pid]['OrganizationalUnit']];
      $data[$pid]['OrganizationalPosition'] = $links['OrganizationalPosition'][$data[$pid]['OrganizationalPosition']];
   }
   
   return $data;
}

/**
 * Return list of all Employee
 * 
 * @return array
 */
function getAllEmployees()
{
   $data    = array();
   $np      = array();
   $empIDS  = array();
   $staff   = array();
   $unitIDS = array();
   $posIDS  = array();
   $odb     = Container::getInstance()->getODBManager();
   
   $query = "SELECT np.`Name`, np.`Surname`, np.`Phone`, np.`Photo`, np.`_id` AS `NaturalPerson`, e.`_id` AS `Employee` ".
            "FROM catalogs.Employees AS e, catalogs.NaturalPersons AS np ".
            "WHERE e.`NaturalPerson`=np.`_id` AND e.`_deleted` = 0";

   if (!($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }

   while ($row = $odb->fetchAssoc($res))
   {
      $data[$row['NaturalPerson']] = $row;
      
      if (empty($row['Employee'])) continue;
      
      $empIDS[$row['Employee']] = $row['Employee'];
      $np[$row['Employee']] = $row['NaturalPerson'];
   }
   
   if (!empty($empIDS))
   {
      $query = "SELECT `Employee`, MAX(`Period`) AS `Period`, `OrganizationalUnit`, `OrganizationalPosition`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee` IN (".implode(',', $empIDS).")".
               "GROUP BY `Employee`";

      if (!($res = $odb->executeQuery($query)))
      {
         throw new Exception('Database error');
      }
       
      while ($row = $odb->fetchAssoc($res))
      {
         $staff[$row['Employee']] = $row;
         $unitIDS[$row['OrganizationalUnit']]    = $row['OrganizationalUnit'];
         $posIDS[$row['OrganizationalPosition']] = $row['OrganizationalPosition'];
      }
       
      // Link Description
      if (!empty($unitIDS))
      {
         if (null === ($links['OrganizationalUnit'] = Container::getInstance()->getCModel('catalogs', 'OrganizationalUnits')->retrieveLinkData($unitIDS)))
         {
            throw new Exception('Database error');
         }
      }

      if(!empty($posIDS))
      {
         if (null === ($links['OrganizationalPosition'] = Container::getInstance()->getCModel('catalogs', 'OrganizationalPositions')->retrieveLinkData($posIDS)))
         {
            throw new Exception('Database error');
         }
      }
       
      // Prepare data
      foreach ($staff as $eid => $row)
      {
         if (isset($np[$eid]) && isset($data[$np[$eid]]))
         {
            $pid = $np[$eid];
            
            $data[$pid] = array_merge($data[$np[$eid]], $row);
            
            $data[$pid]['OrganizationalUnit']     = $links['OrganizationalUnit'][$data[$pid]['OrganizationalUnit']];
            $data[$pid]['OrganizationalPosition'] = $links['OrganizationalPosition'][$data[$pid]['OrganizationalPosition']];
         }
      }
   }
   
   return $data;
}

/**
 * Return list of all Natural Persons
 * 
 * @return array
 */
function getAllNaturalPersons()
{
   $data    = array();
   $np      = array();
   $empIDS  = array();
   $staff   = array();
   $unitIDS = array();
   $posIDS  = array();
   $odb     = Container::getInstance()->getODBManager();
    
   $query = "SELECT np.`Name`, np.`Surname`, np.`Phone`, np.`Photo`, np.`_id` AS `NaturalPerson`, e.`_id` AS `Employee` ".
            "FROM catalogs.NaturalPersons AS np LEFT JOIN catalogs.Employees AS e ".
            "ON (e.`NaturalPerson`=np.`_id` AND e.`_deleted` = 0)";

   if (!($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }

   while ($row = $odb->fetchAssoc($res))
   {
      $data[$row['NaturalPerson']] = $row;
      
      if (empty($row['Employee'])) continue;
      
      $empIDS[$row['Employee']] = $row['Employee'];
      $np[$row['Employee']] = $row['NaturalPerson'];
   }
   
   if (!empty($empIDS))
   {
      $query = "SELECT `Employee`, MAX(`Period`) AS `Period`, `OrganizationalUnit`, `OrganizationalPosition`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee` IN (".implode(',', $empIDS).")".
               "GROUP BY `Employee`";

      if (!($res = $odb->executeQuery($query)))
      {
         throw new Exception('Database error');
      }

      while ($row = $odb->fetchAssoc($res))
      {
         $staff[$row['Employee']] = $row;
         $unitIDS[$row['OrganizationalUnit']]    = $row['OrganizationalUnit'];
         $posIDS[$row['OrganizationalPosition']] = $row['OrganizationalPosition'];
      }
      
      // Link Description
      if (!empty($unitIDS))
      {
         if (null === ($links['OrganizationalUnit'] = Container::getInstance()->getCModel('catalogs', 'OrganizationalUnits')->retrieveLinkData($unitIDS)))
         {
            throw new Exception('Database error');
         }
      }
      
      if(!empty($posIDS))
      {
         if (null === ($links['OrganizationalPosition'] = Container::getInstance()->getCModel('catalogs', 'OrganizationalPositions')->retrieveLinkData($posIDS)))
         {
            throw new Exception('Database error');
         }
      }
      
      // Prepare data
      foreach ($staff as $eid => $row)
      {
         if (isset($np[$eid]) && isset($data[$np[$eid]]))
         {
            $pid = $np[$eid];
            
            $data[$pid] = array_merge($data[$np[$eid]], $row);
            
            $data[$pid]['OrganizationalUnit']     = $links['OrganizationalUnit'][$data[$pid]['OrganizationalUnit']];
            $data[$pid]['OrganizationalPosition'] = $links['OrganizationalPosition'][$data[$pid]['OrganizationalPosition']];
         }
      }
   }
   
   return $data;
}
