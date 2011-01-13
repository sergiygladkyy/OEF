<?php 

/**
 * Update form data
 * 
 * @param object $event
 * @return void
 */
function onFormUpdateRequest($event)
{
   $params = $event['parameters'];
   $action = isset($params['action']) ? $params['action'] : null;
   
   switch($action)
   {
      case 'Fill':
         self::fill($event);
         break;
         
      case 'Calculate':
         self::calculate($event);
         break;
         
      default:
         throw new Exception('Module error');
   }
}

/**
 * Set default values for edit form
 * 
 * @param object $event
 * @return void
 */
function onBeforeOpening($event)
{
   $formName = $event['formName'];
   $options  = $event['options'];
   
   $departments = Container::getInstance()->getCModel('catalogs', 'OrganizationalUnits')->retrieveSelectData();
   
   $event->setReturnValue(array(
      'select' => array(
         'department' => $departments
      )
   ));
}

/**
 * Fill tabular section by department
 * 
 * @param object $event
 * @return void
 */
function fill(& $event)
{
   $subject   = $event->getSubject();
   $kind      = $subject->getKind();
   $type      = $subject->getType();
   $formData  = $event['formData']['aeform'][$kind][$type];
   $container = Container::getInstance();
   
   if (!empty($formData['attributes']['_id']))
   {
      $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Employees');
       
      if (null === ($res = $cmodel->countEntities((int) $formData['attributes']['_id'], array('attributes' => array('Owner')))))
      {
         throw new Exception('DataBase error');
      }
      elseif ($res > 0)
      {
         throw new Exception('Employees has already been filled');
      }
   }
   
   if (empty($formData['department']))
   {
      throw new Exception('You have not specified department');
   }
   
   $date = '2011-01-20';//date('Y-m-d');
   
   // Retrieve employees
   
   $odb   = $container->getODBManager();
   $query = "SELECT `Employee`, MAX(`Period`), `RegisteredEvent`, `OrganizationalUnit` ".
            "FROM information_registry.StaffHistoricalRecords ".
            "WHERE `Period` <= '".$date."' AND `OrganizationalUnit` IN (0,".((int) $formData['department']).") ".
            "GROUP BY `Employee` DESC"
   ;
   
   if (null === ($res = $odb->loadAssocList($query, array('key' => 'Employee'))))
   {
      throw new Exception('DataBase error');
   }
   
   $emplIDS = array();
   
   foreach ($res as $employee => $row)
   {
      if ($row['OrganizationalUnit'] != 0 && $row['RegisteredEvent'] != 'Firing')
      {
         $emplIDS[] = $employee;
      } 
   }
   
   unset($res);
   
   // Retrieve the employees who have vacation days
   $cmodel = $container->getCModel('AccumulationRegisters', 'EmployeeVacationDays');
   $totals = $cmodel->getTotals($date, array('criteria' => array('Employee' => $emplIDS)));
   
   unset($emplIDS);
   
   // Generate result
   $result = array();
   
   foreach ($totals as $row)
   {
      if ($row['VacationDays'] > 0)
      {
         $result[] = array(
            'Employee'  => $row['Employee'],
            'StartDate' => $date
         );
      }
   }
   
   unset($totals);
   
   $event->setReturnValue(array(
      'type' => 'array',
      'data' => array(
         "$kind" => array(
            "$type" => array(
               'tabulars' => array(
                  'Employees' => array(
                     'items' => $result
                  )
               )
            )
         )
      ),
      'msg' => 'Filled sucessfuly'
   ));
}

/**
 * Calculate vacation days
 * 
 * @param object $event
 * @return void
 */
function calculate(& $event)
{
   $result   = array();
   $subject  = $event->getSubject();
   $kind     = $subject->getKind();
   $type     = $subject->getType();
   $formData = $event['formData']['aeform'][$kind][$type];
   $empData  =& $formData['tabulars']['Employees'];
   
   $container = Container::getInstance();
   
   $emplIDS = array();
   $eCModel = $container->getCModel('catalogs', 'Employees');
   $odb     = $container->getODBManager();
   
   foreach ($empData as $index => $row)
   {
      // Check tabular attributes
      $result['items'][$index] = $row;
      
      $attrs =& $result['items'][$index];
      
      if (empty($attrs['Employee']))
      {
         $result['errors'][$index]['Employee'] = 'Required';
         continue;
      }
      elseif (null === ($res = $eCModel->countEntities($attrs['Employee'])))
      {
         throw new Exception('DataBase error');
      }
      elseif ($res == 0)
      {
         $result['errors'][$index]['Employee'] = 'Unknow employee';
         continue;
      }
      
      if (isset($emplIDS[$attrs['Employee']]))
      {
         unset($result['items'][$index]);
         continue;
      }
      
      $employee = $attrs['Employee'];
      $emplIDS[$employee] = $index;
      
      if (empty($attrs['StartDate']))
      {
         $start = mktime(0,0,0,01,20,2011);//time();
      }
      else
      {
         if (($start = strtotime($attrs['StartDate'])) === -1)
         {
            $result['errors'][$index]['StartDate'] = 'Date must be in the format YYYY-MM-DD';
            continue;
         }
      }
      
      $attrs['StartDate'] = date('Y-m-d', $start);
      
      // Check previous event
      $query = "SELECT `Employee`, MAX(`Period`), `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee` = ".$employee." AND `Period` <= '".date('Y-m-d', $start)."'";
      
      if (null === ($res = $odb->loadAssoc($query)))
      {
         throw new Exception('DataBase error');
      }
      elseif (empty($res))
      {
         $result['errors'][$index]['StartDate'] = 'Employee did not worked in this period';
         continue;
      }
      elseif ($res['RegisteredEvent'] == 'Firing')
      {
         $result['errors'][$index]['StartDate'] = 'Employee was firing in this period';
         continue;
      }
      
      // Check next events
      $query = "SELECT `Employee`, `Period`, `RegisteredEvent`, `_rec_type`, `_rec_id` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee` = ".$employee." AND `Period` > '".date('Y-m-d', $start)."' ".
               "ORDER BY `_rec_type` ASC";
      
      if (!($res = $odb->executeQuery($query)))
      {
         throw new Exception('DataBase error');
      }
      
      if ($odb->getNumRows($res))
      {
         $docs = array();
         $msg  = 'You must unposted the following documents:<br/>';
         
         while ($row = $odb->fetchAssoc($res))
         {
            $docs[$row['_rec_type']][] = $row['_rec_id'];
         }
         
         foreach ($docs as $_type => $ids)
         {
            $ids = array_unique($ids);
            $links = $container->getCModel('documents', $_type)->retrieveLinkData($ids);
            foreach ($links as $link)
            {
               $msg .= $link['text'].'<br/>';
            }
         }
         
         $result['errors'][$index]['StartDate'] = $msg;
         continue;
      }
      
      // Retrieve total vacation days for current Employee
      $cmodel = $container->getCModel('AccumulationRegisters', 'EmployeeVacationDays');
      $total  = $cmodel->getTotals(date('Y-m-d', $start), array('criteria' => array('Employee' => $employee)));
      
      // Calculate
      if (!isset($total[0]))
      {
         $result['errors'][$index]['StartDate'] = 'Vacation days are not charged. Perhaps you didn\'t posted a document PeriodicClosing';
         continue;
      }
      
      $total = $total[0];
      
      if ($total['VacationDays'] <= 0)
      {
         $result['errors'][$index]['StartDate']  = 'Has no vacation days';
         continue;
      }

      $vacatDays = 60*60*24*$total['VacationDays'];

      if (empty($attrs['EndDate']))
      {
         $attrs['EndDate'] = date('Y-m-d', $start + $vacatDays);
         continue;
      }

      if (($end = strtotime($attrs['EndDate'])) === -1)
      {
         $result['errors'][$index]['EndDate'] = 'Date must be in the format YYYY-MM-DD';
      }
      elseif ($end <= $start)
      {
         $result['errors'][$index]['EndDate'] = 'EndDate must be larger the StartDate';
      }
      elseif ($end - $start > $vacatDays)
      {
         $result['errors'][$index]['EndDate'] = 'The employee has '.$total['VacationDays'].
            ' vacation days. EndDate should not exceed '.date('Y-m-d', $start + $vacatDays)
         ;
      }
   }
   
   $event->setReturnValue(array(
      'type' => 'array',
      'data' => array(
         "$kind" => array(
            "$type" => array(
               'tabulars' => array(
                  'Employees' => $result
               )
            )
         )
      ),
      'msg' => 'Calculated sucessfuly'
   ));
}
