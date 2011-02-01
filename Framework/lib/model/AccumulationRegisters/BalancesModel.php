<?php 

class BalancesModel
{
   protected
      $conf      = null,
      $resources = null;

   private static
      $numeric_types = array(
         'bool'      => 'bool',
         'int'       => 'int',
         'float'     => 'float',
         'reference' => 'reference'
      ),
      $totalActual = '2999-01-01 00:00:00';
   
   public function __construct(array $configuration, array $options = array())
   {
      $this->conf = $configuration;
      $this->resources = array_diff($this->conf['attributes'], $this->conf['dimensions'], array($this->conf['periodical']['field']));
   }
   
   
   /**
    * Get total
    * 
    * [
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
    * @param string $date
    * @param array  $options
    * @return array
    */
   public function getTotals($date = null, array $options = array())
   {
      if (empty($date))
      {
         return $this->getActualTotal($options);
      }
      elseif (!is_string($date))
      {
         return $this->getTotalsByPeriod($date, $options);
      }
      
      $pfield = $this->conf['periodical']['field'];
      $_date  = explode('-', date('Y-m', strtotime($date)));
      $period = date('Y-m-d H:i:s', mktime(0,0,0, $_date[1]+1, 1, $_date[0]));
      
      if (!empty($options['criteria']) && is_array($options['criteria']))
      {
         $criterion = $this->retrieveCriteriaQuery($options['criteria']);
      }
      else $criterion = '';
      
      $db = Container::getInstance()->getDBManager();
      
      if (!empty($this->conf['dimensions']))
      {
         $select = implode(',', $this->conf['dimensions']).',';
      }
      else $select = '';
      
      $select .= implode(',', $this->resources);
      
      // Retrieve current period
      $query = 'SELECT `'.$pfield.'` FROM `'.$this->conf['db_map']['total']['table'].'` '.
               'WHERE `'.$pfield."`='".$period."'";
      
      if (null === ($cur = $db->loadRow($query)))
      {
         return array($db->getError());
      }
      
      if (empty($cur))
      {
         // Retrieve previous period
         $query = 'SELECT `'.$pfield.'` FROM `'.$this->conf['db_map']['total']['table'].'` '.
                  'WHERE `'.$pfield."`<'".$period."' ".
                  'GROUP BY `'.$pfield.'` ORDER BY `'.$pfield.'` DESC LIMIT 1';

         if (null === ($prev = $db->loadRow($query)))
         {
            return array($db->getError());
         }

         if (empty($prev)) return array();
         
         $query = 'SELECT '.$select.' FROM `'.$this->conf['db_map']['total']['table'].'` '.
                  'WHERE `'.$pfield."`='".$prev[0]."'".($criterion ? ' AND '.$criterion : '');
      
         if (null === ($total = $db->loadAssocList($query)))
         {
            return array();
         }
      
         return $total;
      }
      
      // Count total
      $query = 'SELECT '.$select.' FROM `'.$this->conf['db_map']['total']['table'].'` '.
               'WHERE `'.$pfield."`='".$period."'".($criterion ? ' AND '.$criterion : '');
      
      if (null === ($total = $db->loadAssocList($query)))
      {
         return array();
      }
      
      $op_field = $this->conf['db_map']['operation'];
      
      $query = 'SELECT '.$select.','.$op_field.' FROM `'.$this->conf['db_map']['table'].'` '.
               'WHERE `'.$pfield."`>'".$date."' AND `".$pfield."`<'".$period."' AND `".$this->conf['db_map']['active']."` = 1 ".
               ($criterion ? 'AND '.$criterion.' ' : '').
               'ORDER BY `'.$pfield.'` ASC';
      
      if (!($res = $db->executeQuery($query)))
      {
         return array();
      }
      
      if ($db->getNumRows($res) == 0) return $total;
      
      $map    = array();
      $dimstr = '';
      
      foreach ($this->conf['dimensions'] as $field)
      {
         $dimstr .= '$row[\''.$field.'\'].';
      }
      $dimstr = $dimstr."' '";
      
      foreach ($total as $key => $row)
      {
         eval('$map['.$dimstr.'] = '.$key.';');
      }
      
      $cnt = count($total);
      
      while ($row = $db->fetchAssoc($res))
      {
         eval('$key = isset($map['.$dimstr.']) ? $map['.$dimstr.'] : null;');
         
         if (!is_null($key))
         {
            $op = ($row[$op_field] == 1) ? '-' : '+';
            
            $exec = '$total["'.$key.'"][$field]'.$op.'=$row[$field];';
            
            foreach ($this->resources as $field)
            {
               eval($exec);
            }
         }
         else
         {
            eval('$map['.$dimstr.'] = '.$cnt.';');
            
            $total[$cnt] = $row;
            
            unset($total[$cnt][$op_field]);
            
            if ($row[$op_field] == 1)
            {
               foreach ($this->resources as $field)
               {
                  $total[$cnt][$field] = -$total[$cnt][$field];
               }
            }
            
            $cnt++;
         }
      }
      
      return $total;
   }
   
   
   /**
    * Count total
    * 
    * @param mixed $from - string or array dates
    * @return array - errors
    */
   public function countTotals($from = null)
   {
      $db = Container::getInstance()->getDBManager();
      
      // Get first not actual period
      if (is_array($from))
      {
         sort($from);
         reset($from);
         $from = current($from);
      }
      
      $total          = array();
      $pfield         = $this->conf['periodical']['field'];
      $_date          = explode('-', date('Y-m', strtotime($from)));
      $acregPeriod    = $_date[0].'-'.$_date[1].'-01 00:00:00';
      $firstNotActual = date('Y-m-d H:i:s', mktime(0,0,0, $_date[1]+1, 1, $_date[0]));
      $totalActual    =& self::$totalActual;
   
      
      // Delete not actual records
      $query = 'DELETE  FROM `'.$this->conf['db_map']['total']['table'].'` '.
               'WHERE `'.$pfield."`>='".$firstNotActual."'";
      
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
      
      
      // Retrieve last actual periord
      $query = 'SELECT max(`'.$pfield.'`) FROM `'.$this->conf['db_map']['total']['table'].'`';
      /*$query = 'SELECT `'.$pfield.'` FROM `'.$this->conf['db_map']['total']['table'].'` '.
               'WHERE `'.$pfield."`<'".$firstNotActual."' ".
               'GROUP BY `'.$pfield.'` ORDER BY `'.$pfield.'` DESC LIMIT 1';*/
      
      if (null === ($res = $db->loadRow($query)))
      {
         return array($db->getError());
      }
      else $lastActual = empty($res[0]) ? null : $res[0];
      
      
      // Retrieve last actual total
      if ($lastActual)
      {
         $query = 'SELECT * FROM `'.$this->conf['db_map']['total']['table'].'` '.
                  'WHERE `'.$pfield."`='".$lastActual."' ";

         if (!($res = $db->executeQuery($query)))
         {
            return array($db->getError());
         }

         // Specifying initial values
         while ($row = $db->fetchAssoc($res))
         {
            foreach ($this->resources as $field)
            {
               $exec  = '$total[$firstNotActual]'.$dimstr.'[\''.$field.'\']='.$row[$field].';';
               $exec .= '$total[$totalActual]'.$dimstr.'[\''.$field.'\']='.$row[$field].';';

               eval($exec);
            }
         }
      }
      
      
      // Retrieve records from Accumulation Register
      $query = 'SELECT * FROM `'.$this->conf['db_map']['table'].'` '.
               'WHERE `'.$pfield."`>='".$acregPeriod."' AND `".$this->conf['db_map']['active']."` = 1 ".
               'ORDER BY `'.$pfield.'` ASC';
      
      if (!($res = $db->executeQuery($query)))
      {
         return array($db->getError());
      }
      
      
      // Count totals
      $pdate = $lastActual;
      $prev  = false;
      
      while ($row = $db->fetchAssoc($res))
      {
         $_date = explode('-', date('Y-m', strtotime($row[$pfield])));
         $cdate = date('Y-m-d H:i:s', mktime(0,0,0, $_date[1]+1, 1, $_date[0]));
         
         if ($prev && $prev != $cdate)
         {
            $pdate = $prev;
            
            $total[$cdate] = $total[$pdate];
         }
         
         foreach ($this->resources as $field)
         {
            $op = ($row[$this->conf['db_map']['operation']] == 1) ? '+' : '-';
            
            // Current
            $exec = '$total[$cdate]'.$dimstr.'[\''.$field.'\']';
            
            eval('$isset = isset('.$exec.');');
            
            $exec .= $isset ? ($op.'='.$row[$field].';') : ('='.$op.$row[$field].';');
            
            eval($exec);
            
            // Actual
            $exec = '$total[$totalActual]'.$dimstr.'[\''.$field.'\']';
            
            eval('$isset = isset('.$exec.');');
            
            $exec .= $isset ? ($op.'='.$row[$field].';') : ('='.$op.$row[$field].';');
            
            eval($exec);
         }

         $prev = $cdate;
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
         
         if (!is_array($criteria[$field]))
         {
            $criteria[$field] = array($criteria[$field]);
         }
         
         if (isset(self::$numeric_types[$types[$field]]))
         {
            $criterion[] = '`'.$field.'` IN ('.implode(',', $criteria[$field]).')';
         }
         else $criterion[] = '`'.$field."` IN ('".implode("','", $criteria[$field])."')";
      }
      
      return implode(' AND ', $criterion);
   }
   
   /**
    * Get actual total
    * 
    * @param array& $options
    * @return array()
    */
   protected function getActualTotal(array& $options = array())
   {
      $pfield = $this->conf['periodical']['field'];
      
      if (!empty($options['criteria']) && is_array($options['criteria']))
      {
         $criterion = $this->retrieveCriteriaQuery($options['criteria']);
      }
      else $criterion = '';
      
      $db = Container::getInstance()->getDBManager();
      
      if (!empty($this->conf['dimensions']))
      {
         $select = implode(',', $this->conf['dimensions']).',';
      }
      else $select = '';
      
      $select .= implode(',', $this->resources);
      
      // Retrieve total
      $query = 'SELECT '.$select.' FROM `'.$this->conf['db_map']['total']['table'].'` '.
               'WHERE `'.$pfield."`='".self::$totalActual."'".($criterion ? ' AND '.$criterion : '');

      if (null === ($total = $db->loadAssocList($query)))
      {
         return array();
      }

      return $total;
   }
   
   /**
    * Get totals by period
    * 
    * @param mixed $date
    * @param array $options
    * @return array
    */
   public function getTotalsByPeriod($date, array $options = array())
   {
      if (empty($date))
      {
         return array();
      }
      
      $pfield = $this->conf['periodical']['field'];
      
      // Get custom period
      if (is_string($date))
      {
         $from = $date;
         $to   = false;        
      }
      elseif (!is_array($date))
      {
         throw new Exception('Invalid date');
      }
      else
      {
         $from = empty($date[0]) ? (empty($date['from']) ? false : $date['from']) : $date[0];
         $to   = empty($date[1]) ? (empty($date['to'])   ? false : $date['to'])   : $date[1];
      }
      
      // Get criterion
      if (!empty($options['criteria']) && is_array($options['criteria']))
      {
         $criterion = $this->retrieveCriteriaQuery($options['criteria']);
      }
      else $criterion = '';
      
      if ($from) $criterion .= ($criterion ? ' AND ' : '')."`Period` >= '".$from."'";
      if ($to)   $criterion .= ($criterion ? ' AND ' : '')."`Period` < '".$to."'";
      
      $op = (isset($options['operation']) && !($options['operation'] == true || $options['operation'] == '+')) ? 0 : 1;
      
      // Comming
      $db = Container::getInstance()->getDBManager();
      
      if (!empty($this->conf['dimensions']))
      {
         $select = '`'.implode('`,`', $this->conf['dimensions']).'`';
         $group  = ' GROUP BY `'.implode('`,`', $this->conf['dimensions']).'`';
      }
      else
      {
         $select = '';
         $group  = '';
      }

      foreach ($this->resources as $attribute)
      {
         $select .= ',SUM(`'.$attribute.'`) AS `'.$attribute.'`';
      }
      
      if ($select{0} == ',') $select{0} = ' ';
      
      $query = 'SELECT '.$select.' FROM `'.$this->conf['db_map']['table'].'` '.
               'WHERE `'.$this->conf['db_map']['active'].'`= 1 AND '.($criterion ? $criterion.' AND ' : '').$this->conf['db_map']['operation'].'='.$op;
               $group;
      
      if (null === ($comming = $db->loadAssocList($query)))
      {
         return array();
      }
      
      if (!$comming[0]['Employee']) $comming = array();
      
      if (isset($options['operation'])) return $comming;
      
      // Expense
      $query = 'SELECT '.$select.',`'.$this->conf['db_map']['operation'].'` FROM `'.$this->conf['db_map']['table'].'` '.
               'WHERE `'.$this->conf['db_map']['active'].'`= 1 AND '.($criterion ? $criterion.' AND ' : '').$this->conf['db_map']['operation'].'= 0';
               $group;
      
      if (null === ($expense = $db->loadAssocList($query)))
      {
         return array();
      }
      
      if (!$expense[0]['Employee']) $expense = array();
      
      // Calculate totals
      $total  = array();
      $dimstr = '';
            
      foreach ($this->conf['dimensions'] as $field)
      {
         $dimstr .= '",".$row[\''.$field.'\'].';
      }
      
      $dimstr = '['.$dimstr."' ']";
      $rows   = array_merge($comming, $expense);
      
      foreach ($rows as $row)
      {
         foreach ($this->resources as $field)
         {
            $op = (isset($row[$this->conf['db_map']['operation']])) ? '-' : '+';
            
            // Current
            $exec = '$total'.$dimstr.'[\''.$field.'\']';
            
            eval('$isset = isset('.$exec.');');
            
            $exec .= $isset ? ($op.'='.$row[$field].';') : ('='.$op.$row[$field].';');
            
            eval($exec);
         }
      }
      
      return $total;   
   }
}
