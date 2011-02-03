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
   $model = $container->getCModel('information_registry', 'ProjectAssignmentPeriods');
   $crit  = "WHERE `Project` = ".$project." AND (`DateTo` > '".$date."' OR `DateFrom` <= '".$date."')";
   
   if (null === ($aRows = $model->getEntities(null, array('criterion' => $crit, 'key' => 'Employee'))))
   {
      throw new Exception('Database error');
   }
   
   if (empty($aRows)) return $result;
   
   $empIDS = array_keys($aRows);
   
   // InternalRate
   $model = $container->getCModel('information_registry', 'StaffHistoricalRecords');
   $crit  = "WHERE `Employee` IN (".implode(',', $empIDS).") AND `Period` <= '".$date."' AND `RegisteredEvent` <> 'Firing'";
   $crit .= "GROUP BY `Employee`, `Period`";
   
   if (null === ($hRows = $model->getEntities(null, array('criterion' => $crit, 'key' => 'Employee'))))
   {
      throw new Exception('Database error');
   }
   
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
   $container  = Container::getInstance();
   $period     = empty($attributes['Period']) ? 'Next Month' : $attributes['Period'];
   $department = 0;
   
   $result = array(
      'list'   => array(),
      'links'  => array(),
      'fields' => array(
         0 => 'Employee',
         1 => 'Position',
         2 => 'Hours available'
      )
   );
   
   // Check attributes
   if (!empty($attributes['Department']))
   {
      $model = $container->getModel('catalogs', 'OrganizationalUnits');

      if (!$model->loadByCode($attributes['Department']))
      {
         throw new Exception('Unknow project');
      }
      
      $department = $model->getId();
   }
   
   if (null === ($period = MGlobal::parseDatePeriodString($period)))
   {
      throw new Exception('Invalid period');
   }
   
   // Allocated Hours
   $odb   = Container::getInstance()->getODBManager();
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
      
      $av = isset($aHours[$employee]) ? $hours - $aHours[$employee]['HoursAllocated'] : $hours;
      
      if ($av <= 0)
      {
         unset($emplIDS[$employee]);
         continue;
      }
      
      $result['list'][] = array(
         0 => $employee,
         1 => $hists[$i]['OrganizationalPosition'],
         2 => $av.'/'.$hours
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
      if ($row['HoursAllocated'] === null) $row['HoursAllocated'] = '-';
      if ($row['HoursSpent'] === null)     $row['HoursSpent'] = '-';
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
?>