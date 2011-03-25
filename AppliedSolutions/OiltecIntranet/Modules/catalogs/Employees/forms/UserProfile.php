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
   $attrs   = array(
      'Employee'    => array(),
      'Person'      => array(),
      'StaffRecord' => array()
   );
   $kind = $subject->getKind();
   $type = $subject->getType();
   $uploadDir=Utility::getUploadDir($kind, 'NaturalPersons', 'Photo');
   $form_prefix = 'aeform['.$subject->getKind().']['.$subject->getType().']';
   $container   = Container::getInstance();

   // Retrieve employee
   $empId    = !empty($params['employee']) ? (int) $params['employee'] : 0;
   $employee = $container->getModel('catalogs', 'Employees');

   if ($empId > 0)
   {
      if (!$employee->load($empId))
      {
         throw new Exception('Database error');
      }

      $person = $employee->getAttribute('NaturalPerson');

      $attrs['Person'] = $person->toArray(array('with_link_desc' => true));

      // StaffHistoricalRecords
      $odb = Container::getInstance()->getODBManager();

      // Retrieve last record
      $query = "SELECT * FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".$empId." AND `Period` <= '".date('Y-m-d H:i:s')."'".
               "ORDER BY `Period` DESC LIMIT 1";

      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }

      if ($row['RegisteredEvent'] != 'Firing')
      {
         $staff = $container->getModel('information_registry', 'StaffHistoricalRecords');

         if (!$staff->load($row['_id']))
         {
            throw new Exception('Database error');
         }

         $attrs['StaffRecord'] = $staff->toArray(array('with_link_desc' => true));
      }
   }

   $attrs['Employee'] = $employee->toArray(array('with_link_desc' => true));

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

   $event->setReturnValue(array(
      'status'   => false,
      'result'   => array('msg' => 'Not implemented<br><pre>'.print_r($values,true).'</pre>'),
      'errors'   => $errors
   ));
}
