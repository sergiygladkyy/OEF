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
   
   $proj = (int) $attributes['Project'];
   $date = (!empty($attributes['Date']) && is_string($attributes['Date'])) ? date('Y-m-d', strtotime($attributes['Date'])) : date('Y-m-d');
   
   $result  = array('list' => array(), 'links' => array());
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
    
   foreach ($employees as $id => $empl)
   {
      $rID = isset($times[$id]['BusinessArea']) ? $times[$id]['BusinessArea'] : 0;
      
      $roleID[$rID] = $rID;
      
      $result['list'][] = array(
         'Employee Name'  => $id,
         'Role'           => $rID,
         'Hours Spent'    => isset($times[$id]['HoursSpent']) ? $times[$id]['HoursSpent'] : 0,
         'Hours Budgeted' => $registration['BudgetHRS'],
         'Rate'           => $empl['Rate']
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
?>