<?php
/**
 * Web-service action "getProjectList"
 * 
 * @param string $attributes
 * @return array
 */
function getProjectList(array $attributes)
{
   $container = Container::getInstance();
   
   $cmodel = $container->getCModel('catalogs', 'Projects');
   
   $list = $cmodel->getEntities();

   if (is_null($list)) throw new Exception('Internal model error');
   
   return $list;
}

/**
 * Web-service action "getProjectMembers"
 * 
 * @param string $attributes
 * @return array
 */
function getProjectMembers(array $attributes)
{
   if (empty($attributes['Project'])) throw new Exception('Unknow project');
   
   $container = Container::getInstance();
   
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array(
         'Employee Name',
         'Role',
         'Hours Spent',
         'Hours Budgeted',
         'Rate'
      )
   );
   
   $proj = (int) $attributes['Project'];
   $date = (!empty($attributes['Date']) && is_string($attributes['Date'])) ? date('Y-m-d', strtotime($attributes['Date'])) : date('Y-m-d');
   
   $project = $container->getModel('catalogs', 'Projects');
   
   if (!$project->load($proj))
   {
      return $result;
   }
   
   $db = $container->getODBManager();
   
   $query = "SELECT `Resource` AS `Employee`, `Period` AS `Date`, `Rate` FROM information_registry.ProjectAssignmentRecords ".
            "WHERE `Project` = ".$proj." AND `Period` <= '".$date."' ".
            "GROUP BY Employee, Period ORDER BY Employee ASC, Period ASC";
   
   if (null === ($employees = $db->loadAssocList($query, array('key' => 'Employee'))))
   {
      return $result;
   }
   
   if (empty($employees)) return $result;
   
   $query = "SELECT BudgetHRS, MAX(Period) FROM information_registry.ProjectRegistrationRecords ".
            "WHERE Project = ".$proj." AND Period <= '".$date."'";
   
   if (null === ($registration = $db->loadAssoc($query)))
   {
      return $result;
   }
   
   $emplIDS = array_keys($employees);
   
   $query = "SELECT `Employee`, `BusinessArea`, `HoursSpent` FROM information_registry.ProjectTimeRecords ".
            "WHERE `Project` = ".$proj.(!empty($emplIDS) ? " AND `Employee` IN (".implode(',', $emplIDS).")" : '')." AND `Date` <= '".$date."' ".
            "GROUP BY Employee, Date ORDER BY Employee ASC, Date ASC";
   
   if (null === ($times = $db->loadAssocList($query, array('key' => 'Employee'))))
   {
      return $result;
   }
   
   /* Result */
   
   $roleID = array();
   
   $cmodel = $container->getCModel('catalogs', 'Employees');
   $result['links']['Employee Name'] = $cmodel->retrieveLinkData($emplIDS);
   
   $fields =& $result['fields'];
   
   foreach ($employees as $id => $empl)
   {
      $rID = isset($times[$id]['BusinessArea']) ? $times[$id]['BusinessArea'] : 0;
      
      $roleID[$rID] = $rID;
      
      $result['list'][] = array(
         $fields[0] => $id,
         $fields[1] => $rID,
         $fields[2] => isset($times[$id]['HoursSpent']) ? $times[$id]['HoursSpent'] : 0,
         $fields[3] => $registration['BudgetHRS'],
         $fields[4] => $empl['Rate']
      );
   }
   
   if (empty($roleID))
   {
      $result['links']['Role'] = array();
      
      return $result;
   }
   
   $cmodel = $container->getCModel('catalogs', 'BusinessAreas');
   $result['links']['Role'] = $cmodel->retrieveLinkData($roleID);
   
   return $result;
}

/**
 * Web-service action "getUserProjects"
 * 
 * @param string $attributes
 * @return array
 */
function getUserProjects(array $attributes)
{
   $container = Container::getInstance();
   
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array(
         'Project',
         'My Budget, hrs',
         'Spent, hrs',
         'Deadline'
      )
   );
   
   $code   = $container->getUser()->getUsername();
   $date   = (!empty($attributes['Date']) && is_string($attributes['Date'])) ? date('Y-m-d', strtotime($attributes['Date'])) : date('Y-m-d');
   
   // Retrieve current employee (current user)
   $employee = $container->getModel('catalogs', 'Employees');
   
   if (!$employee->loadByCode($code))
   {
      return $result;
   }
   
   $db = $container->getODBManager();
   
   // Retrieve current employee projects
   $query = "SELECT `Project`, `Period` AS `Date`, `BudgetHRS` FROM information_registry.ProjectAssignmentRecords ".
            "WHERE `Resource` = ".$employee->getId()." AND `Period` <= '".$date."' ".
            "GROUP BY Project, Period ORDER BY Project ASC, Period ASC";
   
   if (null === ($projects = $db->loadAssocList($query, array('key' => 'Project'))))
   {
      return $result;
   }
   
   if (empty($projects)) return $result;
   
   // Retrieve deadlines for current employee projects
   $ids = array_keys($projects);
   
   $query = "SELECT `Project`, `Deadline`, `Period` AS `Date` FROM information_registry.ProjectRegistrationRecords ".
            "WHERE `Project` IN (".implode(',', $ids).") AND `Period` <= '".$date."' ".
            "GROUP BY `Project`, `Date` ORDER BY `Project` ASC, `Date` ASC";
   
   if (null === ($deadline = $db->loadAssocList($query, array('key' => 'Project'))))
   {
      return $result;
   }
   
   // Retrieve spent time
   $query = "SELECT `Project`, `HoursSpent` FROM information_registry.ProjectTimeRecords ".
            "WHERE `Employee` = ".$employee->getId()." AND `Project` IN (".implode(',', $ids).") AND `Date` <= '".$date."' ".
            "GROUP BY `Project`, `Date` ORDER BY `Project` ASC, `Date` ASC";
   
   if (null === ($spent = $db->loadAssocList($query, array('key' => 'Project'))))
   {
      return $result;
   }
   
   // Prepare result
   
   $fields =& $result['fields'];
   
   foreach ($projects as $id => $project)
   {
      $result['list'][] = array(
         $fields[0] => $id,
         $fields[1] => $project['BudgetHRS'],
         $fields[2] => isset($spent[$id]['HoursSpent'])  ? $spent[$id]['HoursSpent'] :  0,
         $fields[3] => isset($deadline[$id]['Deadline']) ? $deadline[$id]['Deadline'] : 'not set'
      );
   }
   
   $cmodel = $container->getCModel('catalogs', 'Projects');
   $result['links']['Project'] = $cmodel->retrieveLinkData($ids);
   
   
   
   return $result;
}

/**
 * Web-service action "getProjectCost"
 * 
 * @param string $attributes
 * @return array
 */
function getProjectCost(array $attributes)
{
   if (empty($attributes['Project'])) throw new Exception('Unknow project');
   
   $container = Container::getInstance();
   
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array(
         'Date'        => array('type' => 'string'),
         'Budget'      => array('type' => 'number'),
         'Expenditure' => array('type' => 'number')
      )
   );
   
   $proj = (int) $attributes['Project'];
   $date = (!empty($attributes['Date']) && is_string($attributes['Date'])) ? date('Y-m-d', strtotime($attributes['Date'])) : date('Y-m-d');
   
   $project = $container->getModel('catalogs', 'Projects');
   
   if (!$project->load($proj))
   {
      return $result;
   }
   
   $db = $container->getODBManager();
   
   // Retrieve project BudgetHRS
   $query = "SELECT BudgetHRS, MAX(Period) FROM information_registry.ProjectRegistrationRecords ".
            "WHERE Project = ".$proj." AND Period <= '".$date."'";
   
   if (null === ($projInfo = $db->loadAssoc($query)))
   {
      return $result;
   }
   
   // Retrieve spent time for project
   $query = "SELECT `Employee`, `HoursSpent` FROM information_registry.ProjectTimeRecords ".
            "WHERE  `Project` = ".$project->getId()." AND `Date` <= '".$date."' ".
            "GROUP BY `Employee`, `Date` ORDER BY `Employee` ASC, `Date` ASC";
   
   if (null === ($spent = $db->loadAssocList($query, array('key' => 'Employee'))))
   {
      return $result;
   }
   
   // Prepare result
   $expenditure = 0;
   
   foreach ($spent as $empl => $row)
   {
      $expenditure += $row['HoursSpent']; 
   }
   
   $result['list'][0][0] = MGlobal::getFormattedDate($date, '%d.%m.%Y');
   $result['list'][0][1] = $projInfo['BudgetHRS'];
   $result['list'][0][2] = $expenditure;
    
   return $result;
}
?>