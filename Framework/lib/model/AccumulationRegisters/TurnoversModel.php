<?php 

class TurnoversModel
{
   protected
      $conf      = null,
      $resources = null;
      
   private static $numeric_types = array(
      'bool'      => 'bool',
      'int'       => 'int',
      'float'     => 'float',
      'reference' => 'reference'
   );
   
   public function __construct(array $configuration, array $options = array())
   {
      $this->conf = $configuration;
      $this->resources = array_diff($this->conf['attributes'], $this->conf['dimensions'], array($this->conf['periodical']['field']));
   }
   
   /**
    * Get total
    * 
    * [
    *    $date = 'Y-m-d H:i:s', // period Y-m-01 .. Y-(m+1)-01
    *    
    *    $date = array(
    *       'from' => 'Y-m-d H:i:s',
    *       'to'   => 'Y-m-d H:i:s'
    *    ),
    *    
    *    $date = array(
    *       0 => 'Y-m-d H:i:s', // from
    *       1 => 'Y-m-d H:i:s'  // to
    *    ),
    *    
    *    $options = array(
    *       'criterion' => array(
    *          '<dimension_name>' => <value>,
    *          .............................
    *       )
    *    )
    *    
    *    Embed by AND
    * ]
    * 
    * @param mixed $date
    * @param array $options
    * @return unknown_type
    */
   public function getTotals($date = null, array $options = array())
   {
      if (empty($date)) return array();
      
      $pfield = $this->conf['periodical']['field'];
      
      if (!empty($options['criteria']) && is_array($options['criteria']))
      {
         $criterion = $this->retrieveCriteriaQuery($options['criteria']);
      }
      else $criterion = '';
      
      $db = Container::getInstance()->getDBManager();
      

      // Get records for a certain period
      if (is_string($date))
      {
         $period = date('Y-m', strtotime($date)).'-01 00:00:00';
         
         if (!empty($this->conf['dimensions']))
         {
            $select = implode(',', $this->conf['dimensions']).',';
         }
         else $select = '';

         $select .= implode(',', $this->resources);
         
         $query = 'SELECT '.$select.' FROM `'.$this->conf['db_map']['total']['table'].'` '.
                  'WHERE `'.$pfield."`='".$period."'".($criterion ? ' AND '.$criterion : '');
      
         if (null === ($total = $db->loadAssocList($query)))
         {
            return array();
         }
      
         return $total;
      }
      
      
      // Get custom period
      if (!is_array($date)) throw new Exception('Invalid date');
      
      if (!empty($date[0]))
      {
         if (-1 === ($from = strtotime($date[0])))
         {
            return array();
         }
      }
      elseif (!empty($date['from']))
      {
         if (-1 === ($from = strtotime($date['from'])))
         {
            return array();
         }
      }
       
      if (!empty($date[1]))
      {
         if (-1 === ($to = strtotime($date[1])))
         {
            return array();
         }
      }
      elseif (!empty($date['to']))
      {
         if (-1 === ($to = strtotime($date['to'])))
         {
            return array();
         }
      }

      if (!isset($from))
      {
         $_date = explode('-', date('Y-m', $to));
         $from  = $_date[0].'-'.$_date[1].'-01 00:00:00';
         
         $firstPeriod = $from;
         
         if (date('d H:i:s', $to) == '01 00:00:00')
         {
            $to = date('Y-m-d H:i:s', $to);
            $lastPeriod = $to;
         }
         else
         {
            $to = date('Y-m-d H:i:s', $to);
            $lastPeriod = date('Y-m-d H:i:s', mktime(0,0,0, $_date[1]+1, 1, $_date[0]));
         }
      }
      elseif (!isset($to))
      {
         $_date = explode('-', date('Y-m', $from));
         $from  = date('Y-m-d H:i:s', $from);
         $to    = date('Y-m-d H:i:s', mktime(0,0,0, $_date[1]+1, 1, $_date[0]));
         
         $firstPeriod = $_date[0].'-'.$_date[1].'-01 00:00:00'; 
         $lastPeriod  = $to;
      }
      else
      {
         $firstPeriod = date('Y-m', $from).'-01 00:00:00';
         $from = date('Y-m-d H:i:s', $from);
         
         if (date('d H:i:s', $to) == '01 00:00:00')
         {
            $to = date('Y-m-d H:i:s', $to);
            $lastPeriod = $to;
         }
         else
         {
            $_date = explode('-', date('Y-m', $to));
            $to    = date('Y-m-d H:i:s', $to);
            $lastPeriod = date('Y-m-d H:i:s', mktime(0,0,0, $_date[1]+1, 1, $_date[0]));
         }
      }
      
      // Count total
      $total  = array();
      $dimstr = '';
      $dimsql = array();
      
      foreach ($this->conf['dimensions'] as $field)
      {
         $dimstr  .= '$row[\''.$field.'\'].';
         $dimsql[] = '`'.$field.'`';
      }
      $dimstr = $dimstr."' '";
      
      $resources = array();
      foreach ($this->resources as $field)
      {
         $resources[] = 'SUM(`'.$field.'`) AS `'.$field.'`';
      }
      
      $query = 'SELECT '.implode(',', $resources).($dimsql ? ','.implode(',', $dimsql) : '').' '.
               'FROM `'.$this->conf['db_map']['total']['table'].'` '.
               'WHERE `'.$pfield."`>='".$firstPeriod."' AND `".$pfield."`<'".$lastPeriod."'".
               ($criterion ? ' AND '.$criterion : '').' '.
               ($dimsql ? 'GROUP BY '.implode(',', $dimsql) : '');
      
      if (null === ($total = $db->loadAssocList($query)))
      {
         return array();
      }
      
      $minus = array();
      
      if ($from != $firstPeriod)
      {
         $query = 'SELECT '.implode(',', $resources).($dimsql ? ','.implode(',', $dimsql) : '').' '.
                  'FROM `'.$this->conf['db_map']['table'].'` '.
                  'WHERE `'.$pfield."`>='".$firstPeriod."' AND `".$pfield."`<'".$from."' AND `".$this->conf['db_map']['active']."`=1".
                  ($criterion ? ' AND '.$criterion : '').' '.
                  ($dimsql ? 'GROUP BY '.implode(',', $dimsql) : '');
         
         if (null === ($minus = $db->loadAssocList($query)))
         {
            return array();
         }
      }
      
      if ($to != $lastPeriod)
      {
         $query = 'SELECT '.implode(',', $resources).($dimsql ? ','.implode(',', $dimsql) : '').' '.
                  'FROM `'.$this->conf['db_map']['table'].'` '.
                  'WHERE `'.$pfield."`>='".$to."' AND `".$pfield."`<='".$lastPeriod."' AND `".$this->conf['db_map']['active']."`=1".
                  ($criterion ? ' AND '.$criterion : '').' '.
                  ($dimsql ? 'GROUP BY '.implode(',', $dimsql) : '');
         
         if (null === ($res = $db->loadAssocList($query)))
         {
            return array();
         }
         
         if (!empty($res)) $minus = array_merge($minus, $res);
      }
      
      if (empty($minus)) return $total;
      
      $map = array();
            
      foreach ($total as $key => $row)
      {
         eval('$map['.$dimstr.'] = '.$key.';');
      }
      
      $cnt = count($total);
      
      foreach ($minus as $row)
      {
         eval('$key = isset($map['.$dimstr.']) ? $map['.$dimstr.'] : null;');
         
         if (!is_null($key))
         {
            $exec = '$total["'.$key.'"][$field]-=$row[$field];';
            
            foreach ($this->resources as $field)
            {
               eval($exec);
            }
         }
         else
         {
            eval('$map['.$dimstr.'] = '.$cnt.';');
            
            $total[$cnt] = $row;
            
            foreach ($this->resources as $field)
            {
               $total[$cnt][$field] = -$total[$cnt][$field];
            }
            
            $cnt++;
         }
      }
      
      return $total;
   }
   
   
   /**
    * Count total
    * 
    * @param mixed $dates - string or array dates
    * @return array - errors
    */
   public function countTotals($dates = null)
   {
      // Check dates
      if (!is_array($dates))
      {
         if (!is_string($dates))
         {
            throw new Exception('Invalid date');
         }
         
         $dates = array($dates);
      }
      
      if (empty($dates)) return array();
      
      // Prepare dates
      foreach ($dates as &$date)
      {
         $date = date('Y-m', strtotime($date)).'-01 00:00:00';
      }
      
      $db     = Container::getInstance()->getDBManager();
      $total  = array();
      $pfield = $this->conf['periodical']['field'];
      
      // Clear totals
      $query = 'DELETE  FROM `'.$this->conf['db_map']['total']['table'].'` '.
               'WHERE `'.$pfield."` IN ('".implode("','", $dates)."')";
      
      if (!($res = $db->executeQuery($query)))
      {
         return array($db->getError());
      }
      
      
      // Generate demension and fields string for SQL query (REPLACE)
      $dimstr = '';
      $fields = '('.$pfield;
      
      $numeric_types =& self::$numeric_types;
      
      foreach ($this->conf['dimensions'] as $field)
      {
         $fields .= ','.$field;
         
         if (!isset($numeric_types[$this->conf['types'][$field]]))
         {
            $dimstr .= '",\'".$row[\''.$field.'\']."\'".';
         }
         else $dimstr .= '",".$row[\''.$field.'\'].';
      }
      
      $dimstr  = '['.$dimstr."' ']";
      $fields .= ','.implode(',', $this->resources).')';
      
      
      // Count totals
      foreach ($dates as $date)
      {
         $_date = explode(' ', $date);
         $_date = explode('-', $_date[0]);
         $end   = date('Y-m-d H:i:s', mktime(0,0,0, $_date[1]+1, 1, $_date[0])); 
         
         $query = 'SELECT * FROM `'.$this->conf['db_map']['table'].'` '.
                  'WHERE `'.$pfield."`>='".$date."' AND `".$pfield."`<'".$end."' AND `".$this->conf['db_map']['active']."` = 1 ".
                  'ORDER BY `'.$pfield.'` ASC';
         
         if (!($res = $db->executeQuery($query)))
         {
            return array($db->getError());
         }
         
         while ($row = $db->fetchAssoc($res))
         {
            foreach ($this->resources as $field)
            {
               $exec = '$total[$date]'.$dimstr.'[\''.$field.'\']';

               eval('$isset = isset('.$exec.');');

               $exec .= $isset ? ('+='.$row[$field].';') : ('='.$row[$field].';');

               eval($exec);
            }
         }
      }
      
      
      // Update total table
      $values = '';
      
      foreach ($total as $date => $data)
      {
         foreach ($data as $dim => $resources)
         {
            $tmp  = ",('".$date."'".$dim;
            $exec = true;
            
            while ($exec)
            {
               if (false === ($cuurent = each($resources)))
               {
                  $values .= $tmp.")";
                  $exec    = false;
               }
               
               if ($cuurent['value'] != 0) 
               {
                  $tmp .= ",".$cuurent['value'];
               }
               else $exec = false;
            }
         }
      }
      
      if (empty($values)) return array();

      $values{0} = ' ';
      
      $query = "REPLACE INTO `".$this->conf['db_map']['total']['table']."`".$fields." VALUES".$values;
   
      if (!($res = $db->executeQuery($query)))
      {
         return array($db->getError());
      }
      
      return array();
   }

   /**
    * 
    * @return array
    */
   protected function retrieveCriteriaQuery(array& $criteria)
   {
      $criterion = array();
      
      $dimensions =& $this->conf['dimensions'];
      $types      =& $this->conf['types'];
      
      foreach ($dimensions as $field)
      {
         if (!isset($criteria[$field])) continue;
         
         if (isset(self::$numeric_types[$types[$field]]))
         {
            $criterion[] = '`'.$field.'`='.$criteria[$field];
         }
         else $criterion[] = '`'.$field."`='".$criteria[$field]."'";
      }
      
      return implode(' AND ', $criterion);
   }
}
