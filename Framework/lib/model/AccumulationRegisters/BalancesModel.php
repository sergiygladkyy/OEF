<?php 

class BalancesModel
{
   protected $conf = null;
   
   public function __construct(array $configuration, array $options = array())
   {
      $this->conf = $configuration;
      $this->resources = array_diff($this->conf['attributes'], $this->conf['dimensions'], array($this->conf['periodical']['field']));
   }
   
   public function getTotals($from = null, $to = null)
   {
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
      $firstNotActual = date('Y-m', strtotime($from)).'-01 00:00:00';
      $totalActual    = '2999-01-01 00:00:00';
   
      
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
      
      $numeric_types = array(
         'bool'      => 'bool',
         'int'       => 'int',
         'float'     => 'float',
         'reference' => 'reference'
      );
      
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
               'WHERE `'.$pfield."`>='".$firstNotActual."' AND `".$this->conf['db_map']['active']."` = 1 ".
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
         $cdate = date('Y-m', strtotime($row[$pfield])).'-01 00:00:00';
         
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
            $tmp = ",('".$date."'".$dim;
            
            foreach ($resources as $field => $val)
            {
               if ($val == 0) continue;
               
               $tmp .= ",".$val;
            }
            
            $values .= $tmp.")";
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
}
