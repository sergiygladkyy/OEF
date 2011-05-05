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
   
   $date = date('Y-m-d');
   
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
      'msg' => 'Filled successfully'
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
   $empData  = $event['formData']['aeform'][$kind][$type]['tabulars']['Employees'];
   $emplIDS  = array();

   // Check tabular items
   foreach ($empData as $index => $row)
   {
      $result['items'][$index] = $row;
      
      if (empty($row['Employee']))
      {
         $result['errors'][$index]['Employee'] = 'Required';
      }
      elseif (isset($emplIDS[$row['Employee']]))
      {
         unset($result['items'][$index]);
      }
      else
      {
         $emplIDS[$row['Employee']] = $index;
      
         if ($errors = MVacation::checkVacationItem($result['items'][$index]))
         {
            $result['errors'][$index] = $errors;
         }
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
      'msg' => 'Calculated successfully'
   ));
}
