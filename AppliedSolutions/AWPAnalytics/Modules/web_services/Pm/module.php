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
?>