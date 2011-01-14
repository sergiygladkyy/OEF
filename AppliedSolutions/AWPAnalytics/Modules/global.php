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
      $query = "SELECT MAX(`Period`), `RegisteredEvent` ".
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

      $vacatDays = 60*60*24*$total['VacationDays'];

      if (empty($attrs['EndDate']))
      {
         $attrs['EndDate'] = date('Y-m-d', $start + $vacatDays);
         
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
      elseif ($end - $start > $vacatDays)
      {
         return array('EndDate' => 'The employee has '.$total['VacationDays'].
            ' vacation days. EndDate should not exceed '.date('Y-m-d', $start + $vacatDays)
         );
      }
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