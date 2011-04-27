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
    * Return date period by string
    * 
    * [
    *   DATETIME:
    *     - Now
    *   
    *   DATE:
    *     - Today
    *     - Tomorrow
    *     - Yesterday
    * 
    *     - This Week
    *     - Next Week
    *     - Last Week
    * 
    *     - This Month
    *     - Next Month
    *     - Last Month
    * 
    *     - This Quarter
    *     - Next Quarter
    *     - Last Quarter
    * 
    *     - 1Q, 2Q, 3Q, 4Q (current year)
    * 
    *     - This Year
    *     - Next Year
    *     - Last Year
    * ]
    * 
    * @param string $name - period name
    * @return array or null - 
    *    array(
    *       0 => '<from>',
    *       1 => '<to>',
    *       'from' => '<from>',
    *       'to'   => '<to>'
    *    )
    */
   public static function parseDatePeriodString($name)
   {
      if ($name{1} != 'Q')
      {
         $pname = explode(' ', $name);
         
         list($year, $month, $day) = explode('-', date('Y-m-d'));
         
         if (!isset($pname[1]))
         {
            switch ($name)
            {
               case 'Now':
                  $from = $to = date('Y-m-d H:i:s');
                  break;

               case 'Today':
                  $from = $to = date('Y-m-d');
                  break;

               case 'Tomorrow':
                  $from = $to = date('Y-m-d', mktime(0,0,0, $month, $day+1, $year));
                  break;

               case 'Yesterday':
                  $from = $to = date('Y-m-d', mktime(0,0,0, $month, $day-1, $year));
                  break;

               default:
                  return null;
            }
         }
         else
         {
            $shift = $pname[0] == 'Last' ? -1 : ($pname[0] == 'Next' ? 1 : 0);

            switch ($pname[1])
            {
               case 'Week':
                  $d = date('w');
                  $d = ($d == 0) ? 6 : $d - 1;
                  
                  $start = $day + $shift*7 + $d;
                  
                  $from = date('Y-m-d', mktime(0,0,0, $month, $start, $year));
                  $to   = date('Y-m-d', mktime(0,0,0, $month, $start+7, $year));
                  break;
                  
               case 'Month':
                  $from = date('Y-m-d', mktime(0,0,0, $month+$shift, 1, $year));
                  $to   = date('Y-m-d', mktime(0,0,0, $month+$shift+1, 1, $year));
                  break;
                  
              case 'Quarter':
                  $beg  = $month - (($month-1)%3) + $shift*3;
                  $from = date('Y-m-d', mktime(0,0,0, $beg, 1, $year));
                  $to   = date('Y-m-d', mktime(0,0,0, $beg+3, 1, $year));
                  break;
                  
               case 'Year':
                  $from = date('Y-m-d', mktime(0,0,0, 1,1, $year+$shift));
                  $to   = date('Y-m-d', mktime(0,0,0, 1,1, $year+$shift+1));
                  break;
            }
         }
      }
      else
      {
         $year = date('Y');
         $end  = 3 * ((int) $name{0});

         $from = date('Y-m-d', mktime(0,0,0, $end - 2, 1, $year));
         $to   = date('Y-m-d', mktime(0,0,0, $end + 1, 1, $year));
      }
      
      return array(0 => $from, 1 => $to, 'from' => $from, 'to' => $to);
   }
   
   /**
    * Get first day in week
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
    * Return week number
    * 
    * @param string $date
    * @return int
    */
   public static function getWeekNumber($date)
   {
      if (($ts = strtotime($date)) === -1)
      {
         throw new Exception('Invalid date format');
      }
      
      return date('W', $ts);
   }
   
   /**
    * Return day number
    * 
    * @param string $date
    * @return int
    */
   public static function getDayNumber($date)
   {
      if (($ts = strtotime($date)) === -1)
      {
         throw new Exception('Invalid date format');
      }

      $day = date('w', $ts);
      
      return ($day == 0) ? 6 : --$day;
   }
   
   /**
    * Get list of week's numbers by list of dates
    * 
    * @param array $dates
    * @param array $options
    * @return array
    */
   public static function getListWeeksByDates($dates, array& $options = array())
   {
      $res = array();
      
      foreach ($dates as $date)
      {
         $week = self::getWeekNumber($date);
         
         $res[$week] = $week;
      }
      
      sort($res);
      
      return $res;
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
    * Generate message by links and get or throw it
    * 
    * [ !!! Only documents !!! ]
    * 
    * @param array  $links
    * @param bool   $get
    * @param string $main_msg
    * @return void
    */
   public static function returnMessageByLinks($links, $get = false, $main_msg = 'You must unposted the following documents:')
   {
      $msg = $main_msg.'<ul style="margin: 0px 0px 0px 15px !important; padding: 0 !important;">';

      $prosessed = array();

      foreach ($links as $doc_type => $_links)
      {
         foreach ($_links as $link)
         {
            if (isset($prosessed[$doc_type][$link['value']])) continue;

            $prosessed[$doc_type][$link['value']] = true;

            $msg .= '<li style="font-weight: 400; list-style-type: disc !important;">';
            $msg .= '<a href="#" onclick="openPopup(this, \'documents\', \''.$doc_type.'\', \'EditForm\', {id: '.$link['value'].'}); return false;" target="_blank" class="oef_msg_link">'.$link['text'].'</a>';
            $msg .= '</li>';
         }
      }
      
      $msg .= '</ul>';
      
      if ($get) return $msg;
      
      throw new Exception($msg);
   }
   
   /**
    * Get list of customers for select box
    * 
    * @param array $options
    * @return array
    */
   public static function getCustomersForSelect(array $options = array())
   {
      $container = Container::getInstance();
      $select    = array();
       
      $cmodel = $container->getCModel('catalogs', 'Counteragents');
      $crit   = 'WHERE `Parent`=0 AND `_folder`=1 AND `_deleted`=0 ORDER BY `Description`';
       
      if (null === ($groups = $cmodel->getEntities(null, array('criterion' => $crit, 'key' => '_id'))) || isset($groups['errors']))
      {
         throw new Exception('Database error');
      }
       
      if (!empty($groups))
      {
         $crit  = 'WHERE `Parent` IN ('.implode(',', array_keys($groups)).') AND `_folder`=0 AND `_deleted`=0 ORDER BY `Parent`, `Description`';

         if (null === ($items = $cmodel->getEntities(null, array('criterion' => $crit, 'key' => '_id'))) || isset($items['errors']))
         {
            throw new Exception('Database error');
         }
      }
       
      foreach ($groups as $gid => $group)
      {
         $gname = $group['Description'];

         $select[$gname] = array();

         $exec = true;

         while ($exec && list($id, $item) = each($items))
         {
            if ($item['Parent'] != $gid)
            {
               reset($items);

               $exec = false;

               continue;
            }
             
            $select[$gname][] = array('text' => $item['Description'], 'value' => $id);
         }
      }
      
      return $select;
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
         
         while ($row = $odb->fetchAssoc($res))
         {
            $docs[$row['_rec_type']][] = $row['_rec_id'];
         }
         
         if (null === ($links = MGlobal::getDocumentLinks($docs)))
         {
            throw new Exception('Database error');
         }
      }
      
      // Check Variance Records
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
         
         while ($row = $odb->fetchAssoc($res))
         {
            $docs[$row['_rec_type']][] = $row['_rec_id'];
         }
         
         if (null === ($_links = MGlobal::getDocumentLinks($docs)))
         {
            throw new Exception('Database error');
         }
         
         $links = is_array($links) ? array_merge($links, $_links) : $_links;
      }
      
      if (!empty($links)) MGlobal::returnMessageByLinks($links);
      
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
         return array('EndDate' => 'End date cannot go before the start date. Check the period');
      }
      elseif ($end > $maxEnd)
      {
         return array('EndDate' => 'The employee has '.$total['VacationDays'].
            ' vacation days excluding weekends and holidays. End date should not exceed '.date('Y-m-d', $maxEnd)
         );
      }
      
      // Check Project Assignment Periods
      $query = "SELECT `DateFrom`, `DateTo`, `_rec_type`, `_rec_id` ".
               "FROM information_registry.ProjectAssignmentPeriods ".
               "WHERE `Employee` = ".$employee." AND `DateFrom` < '".date('Y-m-d', $end)."' AND `DateTo` > '".$start."' ".
               "ORDER BY `_rec_type` ASC";
      
      if (!($res = $odb->executeQuery($query)))
      {
         throw new Exception('DataBase error');
      }
      
      if ($odb->getNumRows($res))
      {
         $docs = array();
         
         while ($row = $odb->fetchAssoc($res))
         {
            $docs[$row['_rec_type']][] = $row['_rec_id'];
         }
         
         if (null === ($links = MGlobal::getDocumentLinks($docs)))
         {
            throw new Exception('Database error');
         }
         
         MGlobal::returnMessageByLinks($links, false, 'Vacation cannot be given because there are assignment for that period. See the following document:');
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
               "WHERE `Employee`=".(int) $employee." AND `DateFrom` <= '".$to."' AND `DateTo` >'".$from."'";
      
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
   
   /**
    * Get list of days
    * 
    * @param int $employee - employee id
    * @param string $from  - date from
    * @param string $to    - date to
    * @return array
    */
   public static function getScheduleVarianceDays($employee, $from, $to)
   {
      $odb = Container::getInstance()->getODBManager();
      
      $query = "SELECT * FROM information_registry.ScheduleVarianceRecords ".
               "WHERE `Employee`=".(int) $employee." AND `DateFrom` <= '".$to."' AND `DateTo` >'".$from."'";
      
      if (!($res = $odb->executeQuery($query)))
      {
         throw new Exception('Database error');
      }
      
      $result = array();
      $from = strtotime($from);
      $to   = strtotime($to);
      $day  = 24*60*60;
      
      while ($row = $odb->fetchAssoc($res))
      {
         $df = strtotime($row['DateFrom']);
         $dt = strtotime($row['DateTo']);
         
         if ($df < $from) $df = $from;
         if ($dt > $to)   $dt = $to;
         
         while ($df < $dt)
         {
            $result[date('Y-m-d', $df)] = $row['VarianceKind'];
            $df += $day;
         }
      }
      
      return $result;
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
         return array('Employee have been fired');
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
   
   /**
    * Return id (link) to current Employee
    * 
    * @return int
    */
   public static function retrieveCurrentEmployee()
   {
      $container = Container::getInstance();
      
      $user = $container->getUser();
      
      if (!$user->isAuthenticated()) return 0;
      
      $odb   = $container->getODBManager();
      $query = "SELECT `e`.`_id` ".
               "FROM catalogs.Employees as `e`, information_registry.LoginRecords AS `lr` ".
               "WHERE `lr`.`SystemUser` = ".$user->getId()." AND `lr`.`NaturalPerson` = `e`.`NaturalPerson`";
      
      if (null === ($res = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      return $res ? $res['_id'] : 0;
   }
   
   /**
    * Return id (link) to current Department
    * 
    * @return int - department id
    */
   public static function retrieveCurrentDepartment()
   {
      if (0 == ($employee = self::retrieveCurrentEmployee()))
      {
         return 0;
      }
      
      // Check current employee
      $odb   = Container::getInstance()->getODBManager();
      $query = "SELECT MAX(`Period`) AS `Period`, `OrganizationalUnit`, `OrganizationalPosition`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".$employee;
      
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      if ($row['RegisteredEvent'] == 'Firing') return 0;
      
      // Retrieve department
      $query = "SELECT `OrganizationalUnit` ".
               "FROM information_registry.DivisionalChiefs ".
               "WHERE `Employee`=".$employee;
      
      if (null === ($chief = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      return empty($chief) ? 0 : $chief['OrganizationalUnit'];
   }
   
   /**
    * Get list of Employee Schedules by period 
    * 
    * @param int $employee
    * @param string $from
    * @param string $to
    * @return array
    */
   public static function retrieveSchedulesByPeriod($employee, $from, $to)
   {
      $odb = Container::getInstance()->getODBManager();
      
      // Retrieve last record
      $query = "SELECT MAX(`Period`), `Schedule`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".(int) $employee." AND `Period` <= '".$from."'";
      
      if (null === ($first = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      // Retrieve records in period
      $query = "SELECT * FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".(int) $employee." AND `Period` > '".$from."' AND `Period` < '".$to."' ".
               "ORDER BY `Period` ASC";
      
      if (null === ($rows = $odb->loadAssocList($query)))
      {
         throw new Exception('Database error');
      }
      
      // Claculate
      $result = array();
      $prev = false;
      $ind  = array();
      
      if ($first && $first['RegisteredEvent'] != 'Firing')
      {
         $ind[$first['Schedule']] = 0;
         $result[$first['Schedule']][$ind[$first['Schedule']]] = array('from' => $from, 'to' => $to);
         $prev = $first['Schedule'];
      }
      
      if (empty($rows)) return $result;
      
      foreach($rows as $row)
      {
         if ($row['RegisteredEvent'] == 'Firing')
         {
            if ($prev)
            {
               $result[$prev][$ind[$prev]]['to'] = $row['Period'];
               $ind[$prev]++;
               $prev = false;
            }
            
            continue;
         }
         
         if ($prev == $row['Schedule']) continue;
         
         if (!isset($result[$row['Schedule']]))
         {
            if ($prev)
            {
               $result[$prev][$ind[$prev]]['to'] = $row['Period'];
               $ind[$prev]++;
            }
            
            $ind[$row['Schedule']] = 0;
            $result[$row['Schedule']][$ind[$row['Schedule']]] = array('from' => $row['Period'], 'to' => $to);
            $prev = $row['Schedule'];
         }
         else
         {
            if ($prev)
            { 
               $result[$prev][$ind[$prev]]['to'] = $row['Period'];
               $ind[$prev]++;
            }
            
            $ind[$row['Schedule']]++;
            $result[$row['Schedule']][$ind[$row['Schedule']]] = array('from' => $row['Period'], 'to' => $to);
            $prev = $row['Schedule'];
         }
      }
      
      return $result;
   }
   
   /**
    * Get working hours in period
    * 
    * @param int $employee - employee id
    * @param string $from  - date from
    * @param string $to    - date to
    * @return array ('date_1' => hours, .., 'date_N' => hours) 
    */
   public static function retrieveParametersInPeriod($employee, $from, $to)
   {
      if ((($start = strtotime($from)) === -1) || (($end = strtotime($to)) === -1))
      {
         throw new Exception('Invalid date format');
      }
      
      $odb = Container::getInstance()->getODBManager();
      
      // Retrieve last record
      $query = "SELECT MAX(`Period`), `OrganizationalUnit`, `Schedule`, `OrganizationalPosition`, `InternalHourlyRate`, `YearlyVacationDays`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".(int) $employee." AND `Period` <= '".$from."'";
      
      if (null === ($first = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      // Retrieve records in period
      $query = "SELECT * FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".(int) $employee." AND `Period` > '".$from."' AND `Period` < '".$to."' ".
               "ORDER BY `Period` ASC";
      
      if (null === ($rows = $odb->loadAssocList($query)))
      {
         throw new Exception('Database error');
      }
      
      // Claculate
      $result = array();
      $prev   = array('Date' => $from);
      
      if ($first && $first['RegisteredEvent'] != 'Firing')
      {
         $result[$from] = array(
            'OrganizationalUnit'     => $first['OrganizationalUnit'],
            'Schedule'               => $first['Schedule'],
            'OrganizationalPosition' => $first['OrganizationalPosition'],
            'InternalHourlyRate'     => $first['InternalHourlyRate'],
            'YearlyVacationDays'     => $first['YearlyVacationDays'],
            'WorkingHours'           => 0
         );
         
         $prev['Schedule'] = $first['Schedule'];
      }
      
      $day = 86400;
      
      if (!empty($rows))
      {
         foreach($rows as $row)
         {
            $tts = strtotime($row['Period']) - $day;
            $cts = strtotime($prev['Date']);
            
            // Hiring
            if (empty($prev['Schedule']))
            {
               while ($tts >= $cts)
               {
                  $cind = date('Y-m-d', $cts);
                  $cts += $day;
                  
                  $result[$cind] = array();
               }
            }
            else
            {
               $hours = MSchedules::getSchedule($prev['Schedule'], $prev['Date'], $row['Period']);
               
               while ($tts > $cts)
               {
                  $pind = date('Y-m-d', $cts);
                  $cts += $day;
                  $cind = date('Y-m-d', $cts);
                  
                  $result[$cind] = $result[$pind];
                  $result[$pind]['WorkingHours'] = isset($hours[$pind]) ? $hours[$pind] : 0;
               }
               
               $result[$cind]['WorkingHours'] = isset($hours[$cind]) ? $hours[$cind] : 0;
               
               $cind = date('Y-m-d', $cts + $day);
            }
            
            $prev['Date'] = $cind;
            $prev['Schedule'] = $row['Schedule'];
            
            if ($row['RegisteredEvent'] != 'Firing')
            {
               $result[$cind] = array(
                  'OrganizationalUnit'     => $row['OrganizationalUnit'],
                  'Schedule'               => $row['Schedule'],
                  'OrganizationalPosition' => $row['OrganizationalPosition'],
                  'InternalHourlyRate'     => $row['InternalHourlyRate'],
                  'YearlyVacationDays'     => $row['YearlyVacationDays'],
                  'WorkingHours'           => 0
               );
            }
         }
      }
      elseif (empty($prev['Schedule']))
      {
         return array();
      }
      
      $tts = $end - $day;
      $cts = strtotime($prev['Date']);
      
      if (empty($prev['Schedule']))
      {
         while ($tts >= $cts)
         {
            $cind = date('Y-m-d', $cts);
            $cts += $day;
            
            $result[$cind] = array();
         }
      }
      else
      {
         $hours = MSchedules::getSchedule($prev['Schedule'], $prev['Date'], $to);
         
         while ($tts > $cts)
         {
            $pind = date('Y-m-d', $cts);
            $cts += $day;
            $cind = date('Y-m-d', $cts);

            $result[$cind] = $result[$pind];
            $result[$pind]['WorkingHours'] = isset($hours[$pind]) ? $hours[$pind] : 0;
         }
          
         $result[$cind]['WorkingHours'] = isset($hours[$cind]) ? $hours[$cind] : 0;
      }
      
      return $result;
   }
   
   /**
    * Get last Historical record 
    * 
    * @param int $employee
    * @param string $date
    * @return array
    */
   public static function getLastHistoricalRecord($employee, $date = null)
   {
      if (!$date) $date = date('Y-m-d H:i:s');
      
      $odb = Container::getInstance()->getODBManager();
      
      // Retrieve last record
      $query = "SELECT MAX(`Period`) AS `Period`, `OrganizationalUnit`, `Schedule`, `OrganizationalPosition`, `InternalHourlyRate`, `YearlyVacationDays`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".(int) $employee." AND `Period` <= '".$date."'";
      
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      return $row ? $row : array();
   }
   
   /**
    * Get last Hiring record 
    * 
    * @param int $employee
    * @param string $date
    * @return array
    */
   public static function getLastHiringRecord($employee, $date)
   {
      $odb = Container::getInstance()->getODBManager();
      
      // Retrieve last record
      $query = "SELECT MAX(`Period`) AS `Period`, `OrganizationalUnit`, `Schedule`, `OrganizationalPosition`, `InternalHourlyRate`, `YearlyVacationDays`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".(int) $employee." AND `RegisteredEvent` = 'Hiring' AND `Period` <= '".$date."'";
      
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      return $row['Period'] ? $row : array();
   }
   
   /**
    * Get last Not Firing record 
    * 
    * @param mixed $employees
    * @param string $date
    * @return array
    */
   public static function getLastNotFiringRecord($employees, $date, array $options = array())
   {
      $model = Container::getInstance()->getCModel('information_registry', 'StaffHistoricalRecords');
      
      if (is_array($employees))
      {
         $options['criterion'] = "WHERE `Employee` IN (".implode(',', $employees).")";
      }
      else
      {
         $options['criterion'] = "WHERE `Employee` = ".$employees;
      }
      
      $options['criterion'] .= " AND `Period` <= '".$date."' AND `RegisteredEvent` <> 'Firing' ";
      $options['criterion'] .= "GROUP BY `Employee`, `Period`";
      
      if (null === ($hRows = $model->getEntities(null, $options)))
      {
         throw new Exception('Database error');
      }
      
      return $hRows;
   }
   
   /**
    * Get Employee HoursAllocated
    * 
    * @param int   $employee
    * @param array $projects
    * @return array
    */
   public static function getHoursAllocated($employee, array $projects = array())
   {
      $odb = Container::getInstance()->getODBManager();
      
      if (empty($projects))
      {
         $query = "SELECT SUM(`Hours`) AS `HoursAllocated` ".
                  "FROM information_registry.ProjectAssignmentRecords ".
                  "WHERE `Employee` = ".$employee;
         
         if (null === ($result = $odb->loadAssoc($query)))
         {
            throw new Exception('Database error');
         }
      }
      else
      {
         $query = "SELECT `Project`, SUM(`Hours`) AS `HoursAllocated` ".
                  "FROM information_registry.ProjectAssignmentRecords ".
                  "WHERE `Employee` = ".$employee." AND `Project` IN(".implode(',', $projects).") ".
                  "GROUP BY `Project`";
         
         if (null === ($result = $odb->loadAssocList($query, array('key' => 'Project'))))
         {
            throw new Exception('Database error');
         }
      }
      
      return $result;
   }
   
   /**
    * Get employees with ProjectManager position
    * 
    * @param string $date
    * @return array
    */
   static public function getListOfPMForSelect($date = null)
   {
      $PMPos = Constants::get('ProjectManagerPosition');
      
      if (empty($PMPos)) return array();
      
      if (!$date) $date = date('Y-m-d');
      
      $odb = Container::getInstance()->getODBManager();
      
      $query = "SELECT c.`Description`, ir.`Employee`, MAX(ir.`Period`) AS `Period` ".
               "FROM information_registry.StaffHistoricalRecords AS ir, catalogs.Employees AS c ".
               "WHERE ir.`OrganizationalPosition`=".(int) $PMPos." AND ir.`RegisteredEvent` <> 'Firing' AND ".
               "ir.`Period` <= '".$date."' AND ir.`Employee` = c.`_id` ".
               "GROUP BY `Employee` ORDER BY c.`Description`";
      
      if (null === ($res = $odb->executeQuery($query)))
      {
         throw new Exception('Database error');
      }
      
      $select = array();
      
      while ($row = $odb->fetchAssoc($res))
      {
         $select[$row['Employee']] = array('text' => $row['Description'], 'value' => $row['Employee']);
      }
      
      return $select;
   }
   
   /**
    * Get employees with DivisionalChief position
    * 
    * @param string $date
    * @return array
    */
   public static function getListOfDivisionalChiefsForSelect($date)
   {
      $pos = Constants::get('DivisionalChiefPosition');
      
      if (empty($pos)) return array();
      
      if (!$date) $date = date('Y-m-d');
      
      $odb = Container::getInstance()->getODBManager();
      
      $query = "SELECT c.`Description`, ir.`Employee`, MAX(ir.`Period`) AS `Period` ".
               "FROM information_registry.StaffHistoricalRecords AS ir, catalogs.Employees AS c ".
               "WHERE ir.`OrganizationalPosition`=".(int) $pos." AND ir.`RegisteredEvent` <> 'Firing' AND ".
               "ir.`Period` <= '".$date."' AND ir.`Employee` = c.`_id` ".
               "GROUP BY `Employee` ORDER BY c.`Description`";
      
      if (null === ($res = $odb->executeQuery($query)))
      {
         throw new Exception('Database error');
      }
      
      $select = array();
      
      while ($row = $odb->fetchAssoc($res))
      {
         $select[$row['Employee']] = array('text' => $row['Description'], 'value' => $row['Employee']);
      }
      
      return $select;
   }
   
   /**
    * Return true if employee is Project Manager
    * 
    * @param int $employee
    * @param string $date
    * @return boolean
    */
   static public function isPM($employee, $date = null)
   {
      if (!$date) $date = date('Y-m-d H:i:s');
      
      $odb = Container::getInstance()->getODBManager();
      
      // Retrieve last record
      $query = "SELECT MAX(`Period`) AS `Period`, `OrganizationalUnit`, `Schedule`, `OrganizationalPosition`, `InternalHourlyRate`, `YearlyVacationDays`, `RegisteredEvent` ".
               "FROM information_registry.StaffHistoricalRecords ".
               "WHERE `Employee`=".(int) $employee." AND `Period` <= '".$date."'";
      
      if (null === ($row = $odb->loadAssoc($query)))
      {
         throw new Exception('Database error');
      }
      
      if ($row['RegisteredEvent'] != 'Firing' && $row['OrganizationalPosition'] == Constants::get('ProjectManagerPosition'))
      {
         return true;
      }
      
      return false;
   }
   
   /**
    * Return true if current user is Project Manager
    * 
    * @param string $date
    * @return boolean
    */
   static public function currentIsPM($date = null)
   {
      if (!($employee = self::retrieveCurrentEmployee()))
      {
         return false;
      }
      
      return self::isPM($employee, $date);
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
      
      if ($date) $criterion .= " AND `ClosureDate` <= '".$date."'";
      
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
   public static function getEmployeeProjects($employee, $from, $to, $notClosed = true, array $options = array())
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
      
      $query = "SELECT `Project`, `DateFrom`, `DateTo`, `SubProject`, `ProjectDepartment`, `EmployeeDepartment`, `Comment` ".
               "FROM information_registry.ProjectAssignmentPeriods ".
               "WHERE `Employee` = ".(int) $employee.$proj." AND `DateFrom` <= '".$to."' AND `DateTo` >= '".$from."'";
      
      if (null === ($projects = $odb->loadAssocList($query, $options)))
      {
         throw new Exception('Database error');
      }
      
      return $projects;
   }
   
   /**
    * Get projects assignment info by employee
    * 
    * @param int    $employee
    * @param string $from
    * @param string $to
    * @param bool   $notClosed
    * @return array
    */
   public static function getEmployeeAssignmentInfo($employee, $from, $to, $notClosed = true, array $options = array())
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
      
      $query = "SELECT * FROM information_registry.ProjectAssignmentRecords ".
               "WHERE `Employee` = ".(int) $employee.$proj." AND `Date` < '".$to."' AND `Date` >= '".$from."' ".
               "ORDER BY `Project`, `Date`";
      
      if (null === ($projects = $odb->loadAssocList($query, $options)))
      {
         throw new Exception('Database error');
      }
      
      return $projects;
   }
   
   /**
    * Get list of employees assignment to project
    * 
    * @param mixed $project
    * @param string $date
    * @return array
    */
   public static function getAssignmentEmployees($projects, $date, array $options = array())
   {
      if (empty($projects)) return array();
      
      if (!is_array($projects)) $projects = array($projects);
      
      $container = Container::getInstance();
      
      $odb   = $container->getODBManager();
      $query = "SELECT `Employee`, `DateFrom`, `DateTo`, `SubProject`, `ProjectDepartment`, `EmployeeDepartment`, `Comment` ".
               "FROM information_registry.ProjectAssignmentPeriods ".
               "WHERE `Project` IN (".implode(',', $projects).") AND (`DateTo` > '".$date."' OR `DateFrom` <= '".$date."')";
      
      if (null === ($employees = $odb->loadAssocList($query, $options)))
      {
         throw new Exception('Database error');
      }
      
      return $employees;
   }
   
   /**
    * Get list of project milestones
    * 
    * @param int $project
    * @return array
    */
   public static function getMilestones($project)
   {
      $odb   = Container::getInstance()->getODBManager();
      $query = "SELECT MileStoneName, MileStoneDeadline FROM information_registry.MilestoneRecords ".
               "WHERE `Project` = ".$project;
       
      if (null === ($result = $odb->loadRowList($query)))
      {
         throw new Exception('Database error');
      }
      
      return $result;
   }
   
   /**
    * Get Employee HoursAllocated
    * 
    * @param int $project
    * @param array $employees
    * @return array
    */
   public static function getHoursAllocated($project, array $employees = array())
   {
      $odb = Container::getInstance()->getODBManager();
      
      if (empty($employees))
      {
         $query = "SELECT SUM(`Hours`) AS `HoursAllocated` ".
                  "FROM information_registry.ProjectAssignmentRecords ".
                  "WHERE `Project` = ".$project;
         
         if (null === ($result = $odb->loadAssoc($query)))
         {
            throw new Exception('Database error');
         }
      }
      else
      {
         $query = "SELECT `Employee`, SUM(`Hours`) AS `HoursAllocated` ".
                  "FROM information_registry.ProjectAssignmentRecords ".
                  "WHERE `Project` = ".$project." AND `Employee` IN(".implode(',', $employees).") ".
                  "GROUP BY `Employee`";
         
         if (null === ($result = $odb->loadAssocList($query, array('key' => 'Employee'))))
         {
            throw new Exception('Database error');
         }
      }
      
      return $result;
   }
}



/**
 * Schedule function
 * 
 * @author alexander.yemelianov
 */
class MSchedules
{
   /**
    * Get Schedule hours by date
    * 
    * @param int $id - schedule id
    * @param string $from - date from
    * @param string $to   - date to
    * @return array
    */
   public static function getSchedule($id, $from, $to)
   {
      $odb = Container::getInstance()->getODBManager();
      
      $query = "SELECT * FROM information_registry.Schedules ".
               "WHERE `Schedule` = ".(int) $id." AND `Date` >='".$from."' AND `Date` <'".$to."'".
               "ORDER BY `Date`";
      
      if (!($res = $odb->executeQuery($query)))
      {
         throw new Exception('Database error');
      }
      
      $result = array();
      
      while ($row = $odb->fetchAssoc($res))
      {
         $result[$row['Date']] = $row['Hours'];
      }
      
      return $result;
   }
}