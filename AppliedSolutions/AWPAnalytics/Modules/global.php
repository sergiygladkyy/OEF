<?php

/**
 * Utility global functions
 * 
 * @author alexander.yemelianov
 */
class MGlobal
{
   public static function dateToTimeStamp($date, $day = 0)
   {
      $dt = explode(' ', $date);
      $vals = explode('-', $dt[0]);
      $vals[0] = (int) $vals[0];
      $vals[1] = isset($vals[1]) ? (int) $vals[1] : 1;
      $vals[2] = isset($vals[2]) ? $vals[2] + $day : 1;
       
      if (empty($vals[0])) return null;
       
      if (!empty($dt[1])) $time = explode(':', $dt[1]);

      $vals[3] = /*isset($time[0]) ? (int) $time[0] : */0;
      $vals[4] = /*isset($time[1]) ? (int) $time[1] : */0;
      $vals[5] = /*isset($time[2]) ? (int) $time[2] : */0;
       
      $mt = mktime($vals[3], $vals[4], $vals[5], $vals[1], $vals[2], $vals[0]);
       
      return $mt;
   }
   
   /**
    * Generates date string for display in templates
    *
    * @param string $date - "Y-m-d H:i:s"
    * @return string or null
    */
   public static function getFormattedDate($date, $format = null)
   {
      $dt = explode(' ', $date);
      $vals = explode('-', $dt[0]);
      $vals[0] = (int) $vals[0];
      $vals[1] = (int) $vals[1];
      $vals[2] = (int) $vals[2];
       
      if (empty($vals[0])) return null;
       
      if (!empty($dt[1])) $time = explode(':', $dt[1]);

      $vals[3] = isset($time[0]) ? (int) $time[0] : 0;
      $vals[4] = isset($time[1]) ? (int) $time[1] : 0;
      $vals[5] = isset($time[2]) ? (int) $time[2] : 0;
       
      $mt = mktime($vals[3], $vals[4], $vals[5], $vals[1] ? $vals[1] : 1, $vals[2] ? $vals[2] : 1, $vals[0]);

      if (empty($format))
      {
         if (empty($vals[1]))     $format = '%Y';
         elseif (empty($vals[2])) $format = '%b %Y';
         elseif (empty($dt[1]))   $format = '%d.%m.%y';
         else                     $format = '%d.%m.%y %H:%M:%S';
      }

      return strftime($format, $mt);
   }
   
   /**
    * 
    * @param string $date
    * @return int
    */
   public static function getFirstWeekDay($date)
   {
      if (($ts = strtotime($date)) === -1)
      {
         throw new Exception('Invalid date format');
      }

      $ts  = mktime(0,0,0,date('m', $ts), date('d', $ts), date('Y', $ts));
      $day = date('w', $ts);
      
      if ($day != 1)
      {
         if ($day == 0)
         {
            $day = 6;
         }
         else $day--;

         $ts -= $day*24*60*60;
      }
      
      return $ts;
   }
   
   /**
    * Get document links
    * 
    * [
    *   $docs = array(
    *      <type> => array(id_1, id_2, .., id_N)
    *   )
    * ]
    * 
    * @param array $docs
    * @return array or null
    */
   public static function getDocumentLinks(array $docs)
   {
      $links = array();
      
      $container  = Container::getInstance();
      
      foreach ($docs as $type => $ids)
      {
         $ids = array_unique($ids);

         if (null === ($res = $container->getCModel('documents', $type)->retrieveLinkData($ids)))
         {
            return null;
         }
         
         $links[$type] = empty($links[$type]) ? $res : array_merge($links[$type], $res);
      }
      
      return $links;
   }
   
   /**
    *
    * @param array& $links
    * @return void
    */
   public static function returnMessageByLinks($links)
   {
      $msg = 'You must unposted the following documents:<ul style="margin: 0px 0px 0px 15px !important; padding: 0 !important;">';

      $prosessed = array();

      foreach ($links as $doc_type => $_links)
      {
         foreach ($_links as $link)
         {
            if (isset($prosessed[$doc_type][$link['value']])) continue;

            $prosessed[$doc_type][$link['value']] = true;

            $msg .= '<li style="font-weight: 400; list-style-type: disc !important;">'.$link['text'].'</li>';
         }
      }

      throw new Exception($msg.'</ul>');
   }
}




/**
 * Vacation global functions
 * 
 * @author alexander.yemelianov
 */
class MVacation
{
   /**
    * Check vacation item
    * 
    * @param array& $attrs
    * @return array - errors
    */
   public static function checkVacationItem(array& $attrs)
   {
      $container = Container::getInstance();
      
      $cmodel = $container->getCModel('catalogs', 'Employees');
      $odb    = $container->getODBManager();
   
      // Check attributes
      if (empty($attrs['Employee']))
      {
         return array('Employee' => 'Required');
      }
      elseif (null === ($res = $cmodel->countEntities($attrs['Employee'])))
      {
         throw new Exception('DataBase error');
      }
      elseif ($res == 0)
      {
         return array('Employee' => 'Unknow employee');
      }
      
      $employee = $attrs['Employee'];
      
      if (empty($attrs['StartDate']))
      {
         $start = time();
      }
      else
      {
         if (($start = strtotime($attrs['StartDate'])) === -1)
         {
            return array('StartDate' => 'Date must be in the format YYYY-MM-DD');
         }
      }
      
      $attrs['StartDate'] = date('Y-m-d', $start);
      
      // Check previous event
      $query = "SELECT MAX(`Period`), `Schedule`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee` = ".$employee." AND `Period` <= '".date('Y-m-d', $start)."'";
      
      if (null === ($res = $odb->loadAssoc($query)))
      {
         throw new Exception('DataBase error');
      }
      elseif (empty($res))
      {
         return array('StartDate' => 'Employee did not worked in this period');
      }
      elseif ($res['RegisteredEvent'] == 'Firing')
      {
         return array('StartDate' => 'Employee was firing in this period');
      }
      
      $schedule = $res['Schedule']; 
      
      // Check next events
      $query = "SELECT `Period`, `RegisteredEvent`, `_rec_type`, `_rec_id` ".
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
      }
      
      // Cneck Variance Records
      $query = "SELECT `DateFrom`, `DateTo`, `VarianceKind`, `_rec_type`, `_rec_id` ".
               "FROM information_registry.ScheduleVarianceRecords ".
               "WHERE `Employee` = ".$employee." AND (`DateFrom` >= '".date('Y-m-d', $start)."' OR `DateTo` > '".date('Y-m-d', $start)."') ".
               "ORDER BY `_rec_type` ASC";
      
      if (!($res = $odb->executeQuery($query)))
      {
         throw new Exception('DataBase error');
      }
      
      if ($odb->getNumRows($res))
      {
         $docs = array();
         
         if (!isset($msg)) $msg = 'You must unposted the following documents:<br/>';
         
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
      }
      
      if (isset($msg)) return array('StartDate' => $msg);
      
      // Retrieve total vacation days for current Employee
      $cmodel = $container->getCModel('AccumulationRegisters', 'EmployeeVacationDays');
      $total  = $cmodel->getTotals(date('Y-m-d', $start), array('criteria' => array('Employee' => $employee)));
      
      // Calculate
      if (!isset($total[0]))
      {
         return array('StartDate' => 'Vacation days are not charged. Perhaps you didn\'t posted a document PeriodicClosing');
      }
      
      $total = $total[0];
      
      if ($total['VacationDays'] <= 0)
      {
         return array('StartDate' => 'Has no vacation days');
      }

      $maxEnd = self::getEndDate($schedule, date('Y-m-d', $start), $total['VacationDays']);
      
      $vacatDays = 60*60*24*$total['VacationDays'];

      if (empty($attrs['EndDate']))
      {
         $attrs['EndDate'] = date('Y-m-d', $maxEnd);
         
         return array();
      }

      if (($end = strtotime($attrs['EndDate'])) === -1)
      {
         return array('EndDate' => 'Date must be in the format YYYY-MM-DD');
      }
      elseif ($end <= $start)
      {
         return array('EndDate' => 'EndDate must be larger the StartDate');
      }
      elseif ($end > $maxEnd)
      {
         return array('EndDate' => 'The employee has '.$total['VacationDays'].
            ' vacation days excluding weekends and holidays. EndDate should not exceed '.date('Y-m-d', $maxEnd)
         );
      }
   }
   
   /**
    * Get end date
    * 
    * @param int $schedule - id
    * @param string $start - start date
    * @param int $days     - number of days
    * @return int - timestamp
    */
   public static function getEndDate($schedule, $start, $days)
   {
      $container = Container::getInstance();
      
      if (($start = strtotime($start)) === -1)
      {
         throw new Exception('Date must be in the format YYYY-MM-DD');
      }
      
      $cmodel = $container->getCModel('information_registry', 'BaseCalendar');
      $smodel = $container->getCModel('information_registry', 'Schedules');
      
      $copt = array('attributes' => array('Date'));
      
      while ($days > 0)
      {
         $date = date('Y-m-d', $start);
         
         // Check calendar
         if (null === ($res = $cmodel->getEntities($date, $copt)) || isset($res['errors']))
         {
            throw new Exception('Database error');
         }
         
         if (empty($res))
         {
            throw new Exception('Missing calendar on the '.$date);
         }
         
         if ($res[0]['Working'] != 0)
         {
            // Check Schedule
            $sopt = array('criterion' => "WHERE `Schedule`={$schedule} AND `Date`='".$date."'");
      
            if (null === ($res = $smodel->getEntities(null, $sopt)) || isset($res['errors']))
            {
               throw new Exception('Database error');
            }
            
            if (empty($res))
            {
               throw new Exception('Missing schedule on the '.$date);
            }
             
            if ($res[0]['Hours'] != 0)
            {
               $days--;
            }
         }
         
         $start += 24*60*60;
      }
      
      return $start;
   }

   /**
    * Get number of days
    * 
    * @param int $schedule - id
    * @param string $start - start date
    * @param string $end   - end date
    * @return int
    */
   public static function getDays($schedule, $start, $end)
   {
      $days = 0;
      
      $container = Container::getInstance();
      
      if (($start = strtotime($start)) === -1)
      {
         throw new Exception('Date must be in the format YYYY-MM-DD');
      }
      
      if (($end = strtotime($end)) === -1)
      {
         throw new Exception('Date must be in the format YYYY-MM-DD');
      }
      
      if ($start >= $end)
      {
         throw new Exception('EndDate must be larger the StartDate');
      }
      
      $cmodel = $container->getCModel('information_registry', 'BaseCalendar');
      $smodel = $container->getCModel('information_registry', 'Schedules');
      
      $copt = array('attributes' => array('Date'));
      
      while ($start < $end)
      {
         $date = date('Y-m-d', $start);
         
         // Check calendar
         if (null === ($res = $cmodel->getEntities($date, $copt)) || isset($res['errors']))
         {
            throw new Exception('Database error');
         }
         
         if (empty($res))
         {
            throw new Exception('Missing calendar on the '.$date);
         }
         
         if ($res[0]['Working'] != 0)
         {
            // Check Schedule
            $sopt = array('criterion' => "WHERE `Schedule`={$schedule} AND `Date`='".$date."'");
      
            if (null === ($res = $smodel->getEntities(null, $sopt)) || isset($res['errors']))
            {
               throw new Exception('Database error');
            }
            
            if (empty($res))
            {
               throw new Exception('Missing schedule on the '.$date);
            }
             
            if ($res[0]['Hours'] != 0)
            {
               $days++;
            }
         }
         
         $start += 24*60*60;
      }
      
      return $days;
   }
   
   /**
    * Get number of days
    * 
    * @param int $employee - id
    * @param string $start - start date
    * @param string $end   - end date
    * @return int
    */
   public static function getDaysByEmployee($employee, $start, $end)
   {
      $odb = Container::getInstance()->getODBManager();
      
      // Get previous event
      $query = "SELECT MAX(`Period`), `Schedule`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee` = ".$employee." AND `Period` <= '".$start."'";
      
      if (null === ($res = $odb->loadAssoc($query)))
      {
         throw new Exception('DataBase error');
      }
      elseif (empty($res))
      {
         throw new Exception('Employee did not worked in this period');
      }
      elseif ($res['RegisteredEvent'] == 'Firing')
      {
         throw new Exception('Employee was firing in this period');
      }
      
      $schedule = $res['Schedule'];

      return self::getDays($schedule, $start, $end);
   }
   
   /**
    *  
    * @param string $from
    * @return array 
    */
   public static function getListVacationOrder($from, $employees = array())
   {
      $links = array();
      
      $container  = Container::getInstance();
      $attributes = array();
      $criterion  = array();
      
      if (!empty($employees))
      {
         if (!is_array($employees)) $employees = array($employees);
         
         $values['Employee'] = implode(',', $employees);
         
         $attributes[] = "Employee";
         $criterion[]  = "`Employee` IN (%%Employee%%)";
      }
      
      $values['DateTo'] = $from;
      
      $attributes[] = "DateTo";
      $criterion[]  = "`DateTo` > %%DateTo%% GROUP BY `_rec_type`, `_rec_id`";
      
      $options = array(
         'attributes' => $attributes,
         'criterion'  => implode(' AND ', $criterion),
      );
      
      $cmodel = $container->getCModel('information_registry', 'ScheduleVarianceRecords');
      $result = $cmodel->getEntities($values, $options);
      
      if ($result === null)
      {
         throw new Exception('Database error');
      }
      
      if (isset($result['errors']))
      {
         throw new Exception(implode('<br>', $result['errors']));
      }
      
      if (!empty($result))
      {
         $docs = array();
         
         foreach ($result as $row)
         {
            $docs[$row['_rec_type']][] = $row['_rec_id'];
         }
          
         if (null === ($links = MGlobal::getDocumentLinks($docs)))
         {
            throw new Exception('Database error');
         }
      }
      
      return $links;
   }
   
   /**
    * Has Vacation in this period ?
    * 
    * @param int $employee
    * @param date $from
    * @param date $to
    * @return array - errors
    */
   public static function checkByPeriod($employee, $from, $to)
   {
      $odb = Container::getInstance()->getODBManager();
      
      $query = "SELECT * FROM information_registry.ScheduleVarianceRecords ".
               "WHERE `Employee`=".(int) $employee." AND (`DateFrom` < '".$to."' OR `DateTo` >'".$from."')";
      
      if (null === ($row = $odb->loadAssocList($query)))
      {
         throw new Exception('Database error');
      }
      
      if (!empty($row))
      {
         return array('Employee has vacation days in this period');
      }
      
      return array();
   }
}


/**
 * Employees global functions
 * 
 * @author alexander.yemelianov
 */
class MEmployees
{
   /**
    *  
    * @param string $from
    * @param mixed  $employees
    * @return array 
    */
   public static function getListMovements($from, $employees = array())
   {
      $links = array();
      
      $container  = Container::getInstance();
      $attributes = array();
      $criterion  = array();
      
      if (!empty($employees))
      {
         if (!is_array($employees)) $employees = array($employees);
         
         $values['Employee'] = implode(',', $employees);
         
         $attributes[] = "Employee";
         $criterion[]  = "`Employee` IN (%%Employee%%)";
      }
      
      $values['Period'] = $from;
      
      $attributes[] = "Period";
      $criterion[]  = "`Period` > %%Period%% GROUP BY `_rec_type`, `_rec_id`";
      
      $options = array(
         'attributes' => $attributes,
         'criterion'  => implode(' AND ', $criterion),
      );
      
      $cmodel = $container->getCModel('information_registry', 'StaffHistoricalRecords');
      $result = $cmodel->getEntities($values, $options);
      
      if ($result === null)
      {
         throw new Exception('Database error');
      }
      
      if (isset($result['errors']))
      {
         throw new Exception(implode('<br>', $result['errors']));
      }
      
      if (!empty($result))
      {
         $docs = array();
         
         foreach ($result as $row)
         {
            $docs[$row['_rec_type']][] = $row['_rec_id'];
         }
         
         if (null === ($links = MGlobal::getDocumentLinks($docs)))
         {
            throw new Exception('Database error');
         }
      }
      
      return $links;
   }
   
   /**
    * Return list of now works employees for select box
    * 
    * @return array
    */
   public static function getNowWorksForSelect()
   {
      $container = Container::getInstance();
       
      $odb   = $container->getODBManager();
       
      $query = "SELECT empl._id AS `value`, empl.Description AS `text` ".
               "FROM catalogs.Employees AS `empl` ".
               "WHERE empl.NowEmployed = 1 ".
               "ORDER BY empl.Description";
       
      if (null === ($empls = $odb->loadAssocList($query, array('key' => 'value'))))
      {
         throw new Exception($odb->getError(). ' Database error');
      }
      
      return $empls;
   }
   
   /**
    * Worked in this period ?
    * 
    * @param int $employee
    * @param string $from
    * @param string $to
    * @return array - errors
    */
   public static function checkByPeriod($employee, $from, $to)
   {
      $odb = Container::getInstance()->getODBManager();
      
      $query = "SELECT `Period`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".(int) $employee." AND `Period` <= '".$from."' ".
               "GROUP BY `Period` ASC";
      
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      if (empty($row))
      {
         return array('Unknow Employee');
      }
      
      if ($row['RegisteredEvent'] == 'Firing')
      {
         return array('Employee fired');
      }
      
      $query = "SELECT `Period`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".(int) $employee." AND `Period` > '".$from."' AND `Period` <= '".$to."' AND `RegisteredEvent` = 'Firing' ".
               "GROUP BY `Period` DESC";
      
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      if (!empty($row))
      {
         return array('Employee have been fired '.print_r($row, true));
      }
      
      return array();
   }
   
   /**
    * Get department
    * 
    * @param int $employee
    * @param string $date
    * @return int or null
    */
   public static function getDepartment($employee, $date)
   {
      $odb = Container::getInstance()->getODBManager();
      
      $query = "SELECT MAX(`Period`), `OrganizationalUnit` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".(int) $employee." AND `Period` <= '".$date."'";
      
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      return empty($row) ? null : $row['OrganizationalUnit'];
   }
}




/**
 * PeriodicClosing global functions
 * 
 * @author alexander.yemelianov
 */
class MPeriodicClosing
{
   /**
    *  
    * @param string $from
    * @param mixed  $employees
    * @return array 
    */
   public static function getListMovements($from, $employees = array())
   {
      $links = array();
      
      $container  = Container::getInstance();
      $values     = array('Period' => $from);
      $attributes = array('%recorder_type', 'Period');
      $criterion  = array("%recorder_type = 'PeriodicClosing' AND `Period` >= %%Period%%");
      
      if (!empty($employees))
      {
         if (!is_array($employees)) $employees = array($employees);
         
         $values['Employee'] = implode(',', $employees);
         
         $attributes[] = "Employee";
         $criterion[]  = "`Employee` IN (%%Employee%%)";
      }
      
      $options = array(
         'attributes' => $attributes,
         'criterion'  => implode(' AND ', $criterion).' GROUP BY `_rec_type`, `_rec_id`',
      );
      
      $cmodel = $container->getCModel('AccumulationRegisters', 'EmployeeVacationDays');
      $result = $cmodel->getEntities($values, $options);
      
      if ($result === null)
      {
         throw new Exception('Database error');
      }
      
      if (isset($result['errors']))
      {
         throw new Exception(implode('<br>', $result['errors']));
      }
      
      if (!empty($result))
      {
         $docs = array();
         
         foreach ($result as $row)
         {
            $docs[$row['_rec_type']][] = $row['_rec_id'];
         }
         
         if (null === ($links = MGlobal::getDocumentLinks($docs)))
         {
            throw new Exception('Database error');
         }
      }
      
      return $links;
   }
}



/**
 * Projects functions
 * 
 * @author alexander.yemelianov
 */
class MProjects
{
   /**
    * Return list of registered projects for select box
    * 
    * @return array
    */
   public static function getRegisteredProjectsForSelect()
   {
      $container = Container::getInstance();
      
      $odb   = $container->getODBManager();
      $query = "SELECT ir.Project AS `value`, pr.Description AS `text` ".
               "FROM catalogs.Projects AS `pr`, information_registry.ProjectRegistrationRecords AS `ir` ".
               "WHERE ir.Project = pr._id ".
               "ORDER BY pr.Description";
      
      if (null === ($projects = $odb->loadAssocList($query, array('key' => 'value'))))
      {
         throw new Exception('Database error');
      }
      
      return $projects;
   }
   
   /**
    * Return list of registered subprojects for select box
    * 
    * @param int $project
    * @return array
    */
   public static function getRegisteredSubProjectForSelect($project)
   {
      $container = Container::getInstance();
      
      $odb   = $container->getODBManager();
      $query = "SELECT ir.SubProject AS `value`, sub.Description AS `text` ".
               "FROM catalogs.SubProjects AS `sub`, information_registry.SubprojectRegistrationRecords AS `ir` ".
               "WHERE sub.Project = ".(int) $project." AND sub._id = ir.SubProject ".
               "ORDER BY sub.Description";
      
      if (null === ($subprojects = $odb->loadAssocList($query, array('key' => 'value'))))
      {
         throw new Exception('Database error');
      }
      
      return $subprojects;
   }
   
   /**
    * 
    * @param $project
    * @param $date
    * @return unknown_type
    */
   public static function isClose($project, $date = null)
   {
      $cmodel = Container::getInstance()->getCModel('information_registry', 'ProjectClosureRecords');
      
      $criterion = "WHERE `Project`=".(int) $project;
      
      if ($date) $critrion .= " AND `ClosureDate` <= '".$date."'";
      
      if (null === ($result = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($result['errors']))
      {
         throw new Exception('Database error');
      }
      
      if (empty($result)) return array();
      
      $docs = array();

      foreach ($result as $row)
      {
         $docs[$row['_rec_type']][] = $row['_rec_id'];
      }
       
      if (null === ($links = MGlobal::getDocumentLinks($docs)))
      {
         throw new Exception('Database error');
      }
      
      return $links;
   }
   
   /**
    * Get project by employee
    * 
    * @param int    $employee
    * @param string $from
    * @param string $to
    * @param bool   $notClosed
    * @return array
    */
   public static function getEmployeeProjects($employee, $from, $to, $notClosed = true)
   {
      $container = Container::getInstance();
      
      $proj = '';
      $odb  = $container->getODBManager();
      
      if ($notClosed)
      {
         $query = "SELECT `Project` FROM information_registry.ProjectClosureRecords ".
                  "WHERE `ClosureDate` <= '".date('Y-m-d')."'";
         
         if (null === ($closed = $odb->loadAssocList($query, array('key' => 'Project'))))
         {
            throw new Exception('Database error');
         }
         elseif (!empty($closed))
         {
            $proj = " AND `Project` NOT IN (".implode(',', array_keys($closed)).") ";
         }
      }
      
      $query = "SELECT `Project`, `Date`, `Hours`, `SubProject`, `ProjectDepartment`, `EmployeeDepartment`, `Comment` ".
               "FROM information_registry.ProjectAssignmentRecords ".
               "WHERE `Employee` = ".(int) $employee.$proj." AND `Date` >= '".$from."' AND `Date` <= '".$to."'";
      
      if (null === ($projects = $odb->loadAssocList($query)))
      {
         throw new Exception('Database error');
      }
      
      return $projects;
   }
}