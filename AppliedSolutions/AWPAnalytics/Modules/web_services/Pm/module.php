<?php
/**
 * Web method ProjectOverview
 * 
 * @param array $attributes
 * @return array
 */
function getProjectOverview(array $attributes)
{
   // Check attributes
   if (empty($attributes['Project']))
   {
      throw new Exception('Unknow project');
   }
   
   $container = Container::getInstance();
   $model     = $container->getModel('catalogs', 'Projects');
   
   if (!$model->loadByCode($attributes['Project']))
   {
      throw new Exception('Unknow project');
   }
   
   $project = $model->getId();
   $date    = empty($attributes['Date']) ? date('Y-m-d') : $attributes['Date'];
   
   $result = array(
      'Project' => '',
      'Date'    => '',
      'ProjectOverview' => array(
         'list' => array()
      ),
      'Employees' => array(
         'list'   => array(),
         'links'  => array(),
         'fields' => array('Employee', 'From', 'To', 'Hours Allocated/Spent')
      ),
      'Milestones' => array(
         'list'   => array(),
         'links'  => array(),
         'fields' => array('Milestone', 'Deadline')
      )
   );
   
   // Retrieve ProjectRegistrationRecords
   $odb   = $container->getODBManager();
   $query = "SELECT * FROM information_registry.ProjectRegistrationRecords ".
            "WHERE `Project` = ".$project;
   
   if (null === ($precords = $odb->loadAssoc($query)))
   {
      throw new Exception('Database error');
   }
   elseif (empty($precords))
   {
      throw new Exception('Project not registered');
   }
   
   $result['Project'] = $model->getAttribute('Description');
   $result['Date']    = $date;
   
   $oview =& $result['ProjectOverview']['list'];
   $model = $container->getModel('catalogs', 'OrganizationalUnits');
   
   $oview[0][0]['label'] = 'Department:';
   $oview[0][0]['value'] = $model->load($precords['ProjectDepartment']) ? $model->getAttribute('Description') : 'unknow';
   $oview[0][1] = array();
   
   $model = $container->getModel('catalogs', 'Employees');
   
   $oview[1][0]['label'] = 'Pm:';
   $oview[1][0]['value'] = $model->load($precords['ProjectManager']) ? $model->getAttribute('Description') : 'unknow';
   $oview[1][1] = array();
   $oview[2][0] = array('label' => 'Start Date:',    'value' => $precords['StartDate']);
   $oview[2][1] = array('label' => 'Delivery Date:', 'value' => $precords['DeliveryDate']);
   $oview[3][0] = array('label' => 'Budget NOK:',    'value' => $precords['BudgetNOK']);
   $oview[3][1] = array('label' => 'Actual Cost:',   'value' => '');
   $oview[4][0] = array('label' => 'ETC:',           'value' => '');
   $oview[4][1] = array('label' => 'Planned Value:', 'value' => '');
   $oview[5][0] = array('label' => 'Invoiced:',      'value' => 0);
   $oview[5][1] = array('label' => 'Earned Value:',  'value' => '');
   $oview[6][0] = array('label' => 'Budget Hours:',  'value' => $precords['BudgetHRS']);
   $oview[6][1] = array('label' => 'Hours Spent:',   'value' => '');
   
   // Retrieve MilestoneRecords
   $result['Milestones']['list'] = MProjects::getMilestones($project);
   
   // Assignment Employees
   $aRows = MProjects::getAssignmentEmployees($project, $date, array('key' => 'Employee'));
   
   if (empty($aRows)) return $result;
   
   $empIDS = array_keys($aRows);
   
   // InternalRate
   $hRows  = MEmployees::getLastNotFiringRecord($empIDS, $date, array('key' => 'Employee'));
   
   // Allocated Hours
   $aHours = MProjects::getHoursAllocated($project, $empIDS);
   
   // Hours SPENT
   $model = $container->getCModel('AccumulationRegisters', 'EmployeeHoursReported');
   $sRows = $model->getTotals($date, array('Project' => $project, 'Employee' => $empIDS));
   
   
   // Calculate project parameters
   $spents = array();
   $Spent = 0;
   $AC = 0;
   
   foreach ($sRows as $row)
   {
      if (!isset($hRows[$row['Employee']]))
      {
         throw new Exception('Unknow InternalHourlyRate');
      }
      
      $spents[$row['Employee']] = $row;
      
      $Spent += $row['Hours'];
      
      $AC += $hRows[$row['Employee']]['InternalHourlyRate']*$row['Hours'];
   }
   
   $assign = array();
   $PV = 0;
   
   foreach ($aRows as $row)
   {
      if (!isset($aHours[$row['Employee']]))
      {
         throw new Exception('Unknow Hours Allocated');
      }
      
      $assign[] = array(
         0 => $row['Employee'],
         1 => $row['DateFrom'],
         2 => $row['DateTo'],
         3 => ($aHours[$row['Employee']]['HoursAllocated'].'/'.(isset($spents[$row['Employee']]) ? $spents[$row['Employee']]['Hours'] : 0))
      );
   
      if (!isset($hRows[$row['Employee']]))
      {
         throw new Exception('Unknow InternalHourlyRate');
      }
      
      $PV += $hRows[$row['Employee']]['InternalHourlyRate']*$aHours[$row['Employee']]['HoursAllocated'];
   }
   
   $ETC = $PV - $AC;
   $EV  = $precords['BudgetHRS'] > 0 ? $precords['BudgetNOK']*$Spent/$precords['BudgetHRS'] - $AC : 0;
   
   
   // Update return value
   $oview[3][1] = array('label' => 'Actual Cost:',   'value' => $AC);
   $oview[4][0] = array('label' => 'ETC:',           'value' => $ETC);
   $oview[4][1] = array('label' => 'Planned Value:', 'value' => $PV);
   $oview[5][1] = array('label' => 'Earned Value:',  'value' => $EV);
   $oview[6][1] = array('label' => 'Hours Spent:',   'value' => $Spent);
   
   $result['Employees']['list'] = $assign;
   $result['Employees']['links']['Employee'] = $container->getCModel('catalogs', 'Employees')->retrieveLinkData($empIDS);
   
   
   return $result;
}

/**
 * Web method ResourcesAvailable
 * 
 * @param array $attributes
 * @return array
 */
function getResourcesAvailable(array $attributes)
{
   $period = empty($attributes['Period']) ? 'Next Month' : $attributes['Period'];
    
   if (!empty($attributes['Department']))
   {
      $model = Container::getInstance()->getModel('catalogs', 'OrganizationalUnits');

      if (!$model->loadByCode($attributes['Department']))
      {
         throw new Exception('Unknow department');
      }
      
      $department = $model->getId();
   }
   else $department = 0;
   
   if (null === ($period = MGlobal::parseDatePeriodString($period)))
   {
      throw new Exception('Invalid period');
   }
   
   return self::ResourcesAvailableHours($period, $department, array('filter_full_loaded'));
}

/**
 * Web method WorkingOnMyProjects
 * 
 * @param array $attributes
 * @return array
 */
function getWorkingOnMyProjects(array $attributes)
{
   // Check attributes
   $date     = empty($attributes['Date']) ? date('Y-m-d') : $attributes['Date'];
   $employee = MEmployees::retrieveCurrentEmployee();
   
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array('Name', 'Project', 'Hours Spent/Planned')
   );
   
   if (empty($employee)) return $result;
   
   // Check employee
   $hist = MEmployees::getLastHiringRecord($employee, $date);
   
   if (empty($hist))
   {
      throw new Exception('Unknow employee'); 
   }
   
   if ($hist['OrganizationalPosition'] != Constants::get('ProjectManagerPosition'))
   {
      return $result;
   }
   
   // Retrieve MyProjects from ProjectRegistrationRecords
   $container = Container::getInstance();
   
   $odb   = $container->getODBManager();
   $query = "SELECT * FROM information_registry.ProjectRegistrationRecords ".
            "WHERE `ProjectManager` = ".$employee." AND `StartDate` <= '".$date."'";
   
   if (null === ($projects = $odb->loadAssocList($query, array('key' => 'Project'))))
   {
      throw new Exception('Database error');
   }
   
   if (empty($projects)) return $result;
   
   // Check closure
   $query = "SELECT `Project` FROM information_registry.ProjectClosureRecords ".
            "WHERE `Project` IN (".implode(',', array_keys($projects)).") AND `ClosureDate` <= '".$date."'";
   
   if (null === ($closure = $odb->loadAssocList($query, array('key' => 'Project'))))
   {
      throw new Exception('Database error');
   }
   
   $projects = array_diff_key($projects, $closure);
   
   if (empty($projects)) return $result;
   
   $prIDS = array_keys($projects);
   
   // Employees and Hours
   $query = "SELECT ap.`Project`, ap.`Employee`, SUM(ar.`Hours`) as `HoursAllocated`, SUM(tr.`Hours`) as `HoursSpent` ".
            "FROM information_registry.ProjectAssignmentPeriods AS `ap` ".
            "  INNER JOIN information_registry.ProjectAssignmentRecords AS `ar` ".
            "    ON ap.`Project` IN (".implode(',', $prIDS).") AND ap.`DateFrom` <= '".$date."' AND ap.`DateTo` > '".$date."' ".
            "      AND ap.`Project` = ar.`Project` AND ap.`Employee` = ar.`Employee` ".
            "  LEFT JOIN information_registry.TimeReportingRecords AS `tr` ".
            "    ON ar.`Project` = tr.`Project` AND ar.`Employee` = tr.`Employee` AND tr.`Date` <= '".$date."' ".
            "GROUP BY ap.`Project`, ap.`Employee`";
   
   if (null === ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }
   
   $empIDS = array();
   
   while ($row = $odb->fetchAssoc($res))
   {
      if ($row['HoursAllocated'] === null) $row['HoursAllocated'] = '0';
      if ($row['HoursSpent'] === null)     $row['HoursSpent'] = '0';
      if ($row['Employee'] > 0)
      {
         $empIDS[$row['Employee']] = $row['Employee'];
      }
      else $row['Employee'] = '-';
      
      $result['list'][] = array(
         0 => $row['Employee'],
         1 => $row['Project'],
         2 => $row['HoursSpent'].'/'.$row['HoursAllocated']
      );
      
   }

   $result['links']['Project'] = $container->getCModel('catalogs', 'Projects')->retrieveLinkData($prIDS);
   
   if (!empty($empIDS))
   {
      $result['links']['Name'] = $container->getCModel('catalogs', 'Employees')->retrieveLinkData($empIDS);
   }
   
   return $result;
}

/**
 * Web method ResourcesSpentVsBudgeted
 * 
 * @param array $attributes
 * @return array
 */
function getResourcesSpentVsBudgeted(array $attributes)
{
   // Check attributes
   if (empty($attributes['Project']))
   {
      throw new Exception('Unknow project');
   }
   
   $container = Container::getInstance();
   $model     = $container->getModel('catalogs', 'Projects');
   
   if (!$model->loadByCode($attributes['Project']))
   {
      throw new Exception('Unknow project');
   }
   
   $project = $model->getId();
   $date    = empty($attributes['Date']) ? date('Y-m-d') : $attributes['Date'];
   $kind    = (empty($attributes['ResourceKind']) || !in_array($attributes['ResourceKind'], array('NOK', 'HRS'))) ? 'HRS' : $attributes['ResourceKind'];
   
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array(
         'Date'      => array('type' => 'string'),
         'Budgeted'  => array('type' => 'number'),
         'Allocated' => array('type' => 'number'),
         'Spent'     => array('type' => 'number'))
   );
   
   // Retrieve ProjectRegistrationRecords
   $odb   = $container->getODBManager();
   $query = "SELECT * FROM information_registry.ProjectRegistrationRecords ".
            "WHERE `Project` = ".$project;
   
   if (null === ($precords = $odb->loadAssoc($query)))
   {
      throw new Exception('Database error');
   }
   elseif (empty($precords))
   {
      throw new Exception('Project not registered');
   }
   
   // Hours SPENT
   $model = $container->getCModel('AccumulationRegisters', 'EmployeeHoursReported');
   $sRows = $model->getTotals($date, array('Project' => $project));
   $spent = 0;
   
   $result['list'][0][0] = MGlobal::getFormattedDate($date, '%d.%m.%Y');
   
   if ($kind != 'NOK')
   {
      // Allocated Hours
      $aHours = MProjects::getHoursAllocated($project);
      
      foreach ($sRows as $row)
      {
         $spent += $row['Hours'];
      }
      
      // Result
      $result['list'][0][1] = $precords['BudgetHRS'];
      $result['list'][0][2] = isset($aHours['HoursAllocated']) ? $aHours['HoursAllocated'] : 0;
      $result['list'][0][3] = $spent;
      
      return $result;
   }
   
   $result['list'][0][1] = $precords['BudgetNOK'];
   
   // Assignment Employees
   $aRows = MProjects::getAssignmentEmployees($project, $date, array('key' => 'Employee'));
   
   if (empty($aRows)) return $result;
   
   $empIDS = array_keys($aRows);
   
   // InternalRate
   $hRows  = MEmployees::getLastNotFiringRecord($empIDS, $date, array('key' => 'Employee'));
   
   // Allocated Hours
   $aHours = MProjects::getHoursAllocated($project, $empIDS);
   
   // Calculate
   foreach ($sRows as $row)
   {
      if (!isset($hRows[$row['Employee']]))
      {
         throw new Exception('Unknow InternalHourlyRate');
      }
      
      $spent += $hRows[$row['Employee']]['InternalHourlyRate']*$row['Hours'];
   }
   
   $PV = 0;
   
   foreach ($aRows as $row)
   {
      if (!isset($aHours[$row['Employee']]))
      {
         throw new Exception('Unknow Hours Allocated');
      }
      
      if (!isset($hRows[$row['Employee']]))
      {
         throw new Exception('Unknow InternalHourlyRate');
      }
      
      $PV += $hRows[$row['Employee']]['InternalHourlyRate']*$aHours[$row['Employee']]['HoursAllocated'];
   }
   
   // Result
   $result['list'][0][2] = $PV;
   $result['list'][0][3] = $spent;
   
   return $result;
}

/**
 * Web method ProjectMilestones
 * 
 * @param array $attributes
 * @return array
 */
function getProjectMilestones(array $attributes)
{
   // Check attributes
   if (empty($attributes['Project']))
   {
      throw new Exception('Unknow project');
   }
   
   $container = Container::getInstance();
   $model     = $container->getModel('catalogs', 'Projects');
   
   if (!$model->loadByCode($attributes['Project']))
   {
      throw new Exception('Unknow project');
   }
   
   $project = $model->getId();
   
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array('Milestone', 'Deadline')
   );
   
   // Retrieve MilestoneRecords
   $result['list'] = MProjects::getMilestones($project);
   
   return $result;
}

/**
 * Web method ProjectsOngoing
 * 
 * @param array $attributes
 * @return array
 */
function getProjectsOngoing(array $attributes)
{
   $container  = Container::getInstance();
   $department = 0;
   
   // Check attributes
   if (!empty($attributes['Department']))
   {
      $model = $container->getModel('catalogs', 'OrganizationalUnits');

      if (!$model->loadByCode($attributes['Department']))
      {
         throw new Exception('Unknow department');
      }
      
      $department = $model->getId();
   }
   
   $date   = empty($attributes['Date']) ? date('Y-m-d') : $attributes['Date'];
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array(
         0 => 'Project',
         1 => 'Start Date',
         2 => 'Delivery'
      )
   );
   
   // ProjectRegistrationRecords
   $odb   = $container->getODBManager();
   $query = "SELECT * FROM information_registry.ProjectRegistrationRecords ".
            "WHERE ".($department ? '`ProjectDepartment` = '.$department.' AND ' : '')."`StartDate` >= '".$date."' ".
            "ORDER BY `StartDate` ASC";
   
   if (null === ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }
   
   $ids = array();
   
   while ($row = $odb->fetchAssoc($res))
   {
      $result['list'][$row['Project']] = array($row['Project'], $row['StartDate'], $row['DeliveryDate']);
      
      $ids[$row['Project']] = $row['Project'];
   }
   
   if (empty($ids)) return $result;
   
   // ProjectClosureRecords
   $query = "SELECT * FROM information_registry.ProjectClosureRecords ".
            "WHERE `ClosureDate` <= '".$date."' AND `Project` IN (".implode(',', $ids).") ";
   
   if (null === ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }
   
   while ($row = $odb->fetchAssoc($res))
   {
      unset(
         $result['list'][$row['Project']],
         $ids[$row['Project']]
      );
   }
   
   if (!empty($ids))
   {
      $result['links']['Project'] = $container->getCModel('catalogs', 'Projects')->retrieveLinkData($ids);
   }
   
   return $result;
}

/**
 * Web method WorkingOnProjectsInMyDepartment
 * 
 * @param array $attributes
 * @return array
 */
function getWorkingOnProjectsInMyDepartment(array $attributes)
{
   $container  = Container::getInstance();
   
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array(
         0 => 'Employee',
         1 => 'Position',
         2 => 'Project'
      )
   );
   
   // Check attributes
   if (!empty($attributes['Department']))
   {
      $model = $container->getModel('catalogs', 'OrganizationalUnits');

      if (!$model->loadByCode($attributes['Department']))
      {
         throw new Exception('Unknow department');
      }
      
      $department = $model->getId();
   }
   else
   {
      if (0 == ($department = MEmployees::retrieveCurrentDepartment()))
      {
         return $result;
      }
   }
   
   $date = empty($attributes['Date']) ? date('Y-m-d') : $attributes['Date'];
   
   // ProjectAssignmentPeriods
   $odb   = $container->getODBManager();
   $query = "SELECT `Employee`, `Project` ".
            "FROM information_registry.ProjectAssignmentPeriods ".
            "WHERE `ProjectDepartment` = ".$department." AND (`DateTo` > '".$date."' OR `DateFrom` <= '".$date."')";
      
   if (null === ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }
   
   $assign = array();
   $empIDS = array();
   $proIDS = array();
   
   while ($row = $odb->fetchAssoc($res))
   {
      if (isset($assign[$row['Employee']]))
      {
         $assign[$row['Employee']][] = $row['Project'];
      }
      else $assign[$row['Employee']][0] = $row['Project'];
      
      $empIDS[$row['Employee']] = $row['Employee'];
      $proIDS[$row['Project']]  = $row['Project'];
   }
   
   if (empty($empIDS)) return $result;
   
   // Position
   $hRows  = MEmployees::getLastNotFiringRecord($empIDS, $date, array('key' => 'Employee')); 
   $posIDS = array();
   
   foreach ($empIDS as $employee)
   {
      if (isset($hRows[$employee]))
      {
         $pos = $hRows[$employee]['OrganizationalPosition'];
         $posIDS[$pos] = $pos;
      }
      else $pos = '-';
      
      foreach ($assign[$employee] as $project)
      {
         $result['list'][] = array($employee, $pos, $project);
      }
   }
   
   $result['links']['Employee'] = $container->getCModel('catalogs', 'Employees')->retrieveLinkData($empIDS);
   $result['links']['Position'] = $container->getCModel('catalogs', 'OrganizationalPositions')->retrieveLinkData($posIDS);
   $result['links']['Project']  = $container->getCModel('catalogs', 'Projects')->retrieveLinkData($proIDS);
   
   return $result;
}

/**
 * Web method DepartmentHoursSpent
 * 
 * @param array $attributes
 * @return array
 */
function getDepartmentHoursSpent(array $attributes)
{
   $container  = Container::getInstance();
   
   $result = array(
      'list' => array(
         0 => array(0 => array('label' => 'Total Hours:',       'value' => 0)),
         1 => array(0 => array('label' => 'Includes Overtime:', 'value' => 0)),
         2 => array(0 => array('label' => 'Includes Extra:',    'value' => 0))
      )
   );
   
   // Check attributes
   if (empty($attributes['Period']))
   {
      $period = null;
   }
   elseif (null === ($period = MGlobal::parseDatePeriodString($attributes['Period'])))
   {
      throw new Exception('Invalid period');
   }
   
   if (!empty($attributes['Department']))
   {
      $model = $container->getModel('catalogs', 'OrganizationalUnits');

      if (!$model->loadByCode($attributes['Department']))
      {
         throw new Exception('Unknow department');
      }
      
      $department = $model->getId();
   }
   else
   {
      if (0 == ($department = MEmployees::retrieveCurrentDepartment()))
      {
         return $result;
      }
   }
   
   // Get total
   $model = $container->getCModel('AccumulationRegisters', 'EmployeeHoursReported');
   $total = $model->getTotals($period, array('criteria' => array('EmployeeDepartment' => $department)));
   
   $hours = 0;
   $overt = 0;
   $extra = 0;
   
   foreach ($total as $row)
   {
      $hours += $row['Hours'];
      $overt += $row['OvertimeHours'];
      $extra += $row['ExtraHours'];
   }
   
   $result['list'][0][0]['value'] = $hours;
   $result['list'][1][0]['value'] = $overt;
   $result['list'][2][0]['value'] = $extra;
   
   return $result;
}

/**
 * Web method ResourcesWorkload
 * 
 * @param array $attributes
 * @return array
 */
function getResourcesWorkload(array $attributes)
{
   $period = empty($attributes['Period']) ? 'This Week' : $attributes['Period'];
   
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array(
         0 => 'Employee',
         1 => 'Position',
         2 => 'Hours available'
      )
   );
   
   if (null === ($period = MGlobal::parseDatePeriodString($period)))
   {
      throw new Exception('Invalid period');
   }
   
   if (!empty($attributes['Department']))
   {
      $model = Container::getInstance()->getModel('catalogs', 'OrganizationalUnits');

      if (!$model->loadByCode($attributes['Department']))
      {
         throw new Exception('Unknow department');
      }
      
      $department = $model->getId();
   }
   else
   {
      if (0 == ($department = MEmployees::retrieveCurrentDepartment()))
      {
         return $result;
      }
   }
   
   return self::ResourcesAvailableHours($period, $department, array('allocated' => true));
} 















/**
 * Calculate total hours in period by schedule
 * 
 * @param array& $schedule
 * @param int $from - timestamp
 * @param int $to   - timestamp
 * @return int
 */
function calculateHoursByPeriod(array& $schedule, $from, $to)
{
   $current = $from;
   $result  = 0;
   
   while ($current < $to)
   {
      if (isset($schedule[$current]))
      {
         $result += $schedule[$current];
      }
      
      $current += 86400;
   }
   
   return $result;
}

/**
 * Get result for AvailableHours Grid widgets
 * 
 * @param array $period
 * @param int   $department
 * @param array $options
 * @return array
 */
function ResourcesAvailableHours(array $period, $department = 0, array $options = array())
{
   $container = Container::getInstance();
   
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array(
         0 => 'Employee',
         1 => 'Position',
         2 => 'Hours available'
      )
   );
   
   // Allocated Hours
   $odb   = $container->getODBManager();
   $query = "SELECT `Employee`, SUM(`Hours`) AS `HoursAllocated` ".
            "FROM information_registry.ProjectAssignmentRecords ".
            "WHERE `Date`>= '".$period['from']."' AND `Date` < '".$period['to']."' ".
            ($department > 0 ? 'AND `EmployeeDepartment` = '.$department.' ' : '').
            "GROUP BY `Employee`";
   
   if (null === ($aHours = $odb->loadAssocList($query, array('key' => 'Employee'))))
   {
      throw new Exception('Database error');
   }
   
   // Get working periods
   $query = "SELECT `Employee`, MAX(`Period`) AS `Period`, `OrganizationalUnit`, `Schedule`, `OrganizationalPosition`, `RegisteredEvent` ".
            "FROM information_registry.StaffHistoricalRecords ".
            "WHERE `Period` < '".$period['from']."' ".
            ($department > 0 ? 'AND `OrganizationalUnit` = '.$department.' ' : '').
            "GROUP BY `Employee` ASC";
   
   if (null === ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }
   
   $hRecs  = array();
   $inds   = array();
   $schedIDS = array();
   $emplIDS  = array();
   
   while ($row = $odb->fetchAssoc($res))
   {
      if ($row['RegisteredEvent'] == 'Firing') continue;
      
      $inds[$row['Employee']] = 0;
      $hRecs[$row['Employee']][0] = $row;
      $hRecs[$row['Employee']][0]['_from'] = $period['from'];
      
      $schedIDS[$row['Schedule']] = $row['Schedule'];
      $emplIDS[$row['Employee']]  = $row['Employee'];
   }
   
   $query = "SELECT `Employee`, `Period`, `OrganizationalUnit`, `Schedule`, `OrganizationalPosition`, `RegisteredEvent` ".
            "FROM information_registry.StaffHistoricalRecords ".
            "WHERE `Period` >= '".$period['from']."' AND `Period` < '".$period['to']."' ".
            ($department > 0 ? 'AND `OrganizationalUnit` = '.$department.' ' : '').
            "ORDER BY `Employee` ASC";
   
   if (null === ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }

   while ($row = $odb->fetchAssoc($res))
   {
      if (isset($hRecs[$row['Employee']]))
      {
         if ($row['RegisteredEvent'] == 'Firing')
         {
            $hRecs[$row['Employee']][$inds[$row['Employee']]]['_to'] = $row['Period'];
            $inds[$row['Employee']]++;
            
            continue;
         }
         else
         {
            if (isset($hRecs[$row['Employee']][$inds[$row['Employee']]]))
            {
               throw new Exception('Employee already Hiring');
            }
            
            $hRecs[$row['Employee']][$inds[$row['Employee']]] = $row;
            $hRecs[$row['Employee']][$inds[$row['Employee']]]['_from'] = $row['Period'];
         }
      }
      else
      {
         if ($row['RegisteredEvent'] == 'Firing') throw new Exception('Before Firing employee must be Hiring');
         
         $inds[$row['Employee']] = 0;
         $hRecs[$row['Employee']][0] = $row;
         $hRecs[$row['Employee']][0]['_from'] = $row['Period'];
         
         $emplIDS[$row['Employee']] = $row['Employee'];
      }
      
      $schedIDS[$row['Schedule']] = $row['Schedule'];
   }
   
   // Get SchedulesRecords
   $query = "SELECT * FROM information_registry.Schedules ".
            "WHERE `Schedule` IN (".implode(',', $schedIDS).") AND `Date` >= '".$period['from']."' AND `Date` < '".$period['to']."' ".
            "ORDER BY `Schedule` ASC, `Date` ASC";
   
   if (null === ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }
   
   unset($schedIDS);
   
   $scheds = array();
   
   while ($row = $odb->fetchAssoc($res))
   {
      $scheds[$row['Schedule']][strtotime($row['Date'])] = $row['Hours'];
   }
   
   // Get ScheduleVarianceRecords
   $query = "SELECT * FROM information_registry.ScheduleVarianceRecords ".
            "WHERE `Employee` IN (".implode(',', $emplIDS).") AND `DateFrom` < '".$period['to']."' AND `DateTo` >= '".$period['from']."' ".
            "ORDER BY `Employee` ASC, `DateFrom` ASC";
   
   if (null === ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }
   
   $variance = array();
   $inds     = array();
   $pfrom = strtotime($period['from']);
   $pto   = strtotime($period['to']);
   
   while ($row = $odb->fetchAssoc($res))
   {
      $row['_from'] = strtotime($row['DateFrom']);
      $row['_to']   = strtotime($row['DateTo']);
      
      if ($pfrom > $row['_from']) $row['_from'] = $pfrom;
      if ($pto   < $row['_to'])   $row['_to']   = $pto;
      
      if (isset($variance[$row['Employee']]))
      {
         $inds[$row['Employee']]++;
         $variance[$row['Employee']][$inds[$row['Employee']]] = $row;
      }
      else
      {
         $inds[$row['Employee']] = 0;
         $variance[$row['Employee']][0] = $row;
      }
   }
   
   // Calculate total hours
   $scache = array();
   $posIDS = array();
   
   $allocated = !empty($options['allocated']); 
   
   foreach ($hRecs as $employee => $hists)
   {
      $hours = 0;
      
      foreach ($hists as $i => $row)
      {
         // Hours by period
         if (!isset($row['_to'])) $row['_to'] = $period['to'];
         
         $_from = strtotime($row['_from']);
         $_to   = strtotime($row['_to']);
         
         $cacheKey = $row['_from'].$row['_to'];
         
         if (!isset($scache[$row['Schedule']][$cacheKey]))
         {
            $scache[$row['Schedule']][$cacheKey] = self::calculateHoursByPeriod($scheds[$row['Schedule']], $_from, $_to);
         }
          
         $hours += $scache[$row['Schedule']][$cacheKey];
         
         // Without variance
         if (empty($variance[$employee])) continue;
          
         $exec  = true;
         
         while ($exec)
         {
            $vi = key($variance[$employee]);

            if ($variance[$employee][$vi]['_from'] >= $_from)
            {
               if ($variance[$employee][$vi]['_to'] <= $_to)
               {
                  $cacheKey = $variance[$employee][$vi]['DateFrom'].$variance[$employee][$vi]['DateTo'];
                   
                  if (!isset($scache[$row['Schedule']][$cacheKey]))
                  {
                     $scache[$row['Schedule']][$cacheKey] = self::calculateHoursByPeriod(
                        $scheds[$row['Schedule']],
                        $variance[$employee][$vi]['_from'],
                        $variance[$employee][$vi]['_to']
                     );
                  }
                   
                  $hours -= $scache[$row['Schedule']][$cacheKey];
                   
                  unset($variance[$employee][$vi]);
                  
                  if (empty($variance[$employee]))
                  {
                     unset($variance[$employee]);
                     
                     $exec = false;
                  }
               }
               elseif ($variance[$employee][$vi]['_from'] <= $_to)
               {
                  throw new Exception('Invalid Schedule Variance Record');
               }
               else $exec = false;
            }
            else throw new Exception('Invalid Schedule Variance Record');
         }
      }
      
      $ah = isset($aHours[$employee]) ? $aHours[$employee]['HoursAllocated'] : 0;
      
      if (empty($options['filter_full_loaded']) && $ah >= $hours)
      {
         unset($emplIDS[$employee]);
         continue;
      }
      
      $result['list'][] = array(
         0 => $employee,
         1 => $hists[$i]['OrganizationalPosition'],
         2 => ($allocated ? $ah : $hours - $ah).'/'.$hours
      );
      
      $posIDS[$hists[$i]['OrganizationalPosition']] = $hists[$i]['OrganizationalPosition'];
   }
   
   unset($scache);
   unset($scheds);
   unset($variance);
   unset($hRecs);
   
   if (!empty($emplIDS))
   {
      $result['links']['Employee'] = $container->getCModel('catalogs', 'Employees')->retrieveLinkData($emplIDS);
      $result['links']['Position'] = $container->getCModel('catalogs', 'OrganizationalPositions')->retrieveLinkData($posIDS);
   }
   
   return $result;
}
?>