<?php

require_once('lib/db/DBMysql.php');

class ODBMysql extends DBMysql
{
   protected static $instance = null;
   
   protected static $dbmap = array();

   /**
    * Create new instance
    *
    * @param array $options
    * @return this
    */
   public static function createInstance(array $options)
   {
      if(is_null(self::$instance))
      {
         self::$instance = new ODBMysql($options);
      }

      return self::$instance;
   }

   /**
    * Get this instance
    *
    * @throws Exception
    * @return this
    */
   public static function getInstance()
   {
      if(is_null(self::$instance))
      {
         throw new Exception(__METHOD__.": Instance is not exists");
      }

      return self::$instance;
   }

   /**
    * Construct
    *
    * @throws Exception
    * @param array& $options
    * @return this
    */
   protected function __construct(array& $options)
   {
      if (!isset($options['dbmap']) || !is_array($options['dbmap']))
      {
         throw new Exception(__METHOD__.': DB map is wrong');
      } 
      
      self::$dbmap = $options['dbmap'];
      
      unset($options['dbmap']);
      
      parent::__construct($options);
   }
   
   /**
    * Convert object query to sql
    * 
    * @param string $query - object query
    * @return string (sql query) or null
    */
   public static function convertObjectQueryToSQL($query)
   {
      $dbmap =& self::$dbmap;
      $alias =  array();
       
      // Retrieve alias (.. AS ..)
      if (preg_match_all('/(?<=\s)((?>[^\s`]+?[\s]*\((?:(?>[^()]+)|(?R))*\))|(?:[^\s]+?)){1}[\s]+AS[\s]+(?:`|)([^\s]+?)(?:`|)(?=\s|,|\z)/i', $query, $matches))
      {
         $alias = array_combine($matches[1], $matches[2]);
      }
      
      // Retrieve objects and attributes definition
      if (!preg_match_all('/(?<=\s|,|\()(?:[^\s.,()]+(?:\.[^\s.,()]+)+)(?=\s|,|\)|\z)/i', $query, $matches))
      {
         return null;
      }
      
      $search  = array();
      $replace = array();
      
      foreach ($matches[0] as $reference)
      {
         // Parse reference
         $length = strlen($reference);
         $param = array(0 => '');
         $quot = 0;
         $n = 0;
         for ($i = 0; $i < $length; $i++)
         {
            
            switch ($reference{$i})
            {
               case '`':
                  if ($quot > 0)
                  {
                     if ($i+1 < $length && $reference{$i+1} != '.')
                     {
                        return null;
                     }
                     $quot--;
                  }
                  else
                  {
                     if ($i+1 == $length || ($i > 0 && $reference{$i-1} != '.'))
                     {
                        return null;
                     }
                     $quot++;
                  }
                  break;
                  
               case '.':
                  if ($quot == 0)
                  {
                      $n++;
                      $param[$n] = '';
                  }
                  else $param[$n] .= '.';
                  
                  break;
                  
               default:
                  $param[$n] .= $reference{$i};
            }
         }
         
         // Prepare replace
         switch (count($param))
         {
            // alias.attribute | simple_kind.type (table or attribute reference)
            case 2:
               if (in_array($param[0], $alias)) break;
               if (!isset($dbmap[$param[0]][$param[1]]['table'])) return null;

               $search[]  = $reference;
               $replace[] = '`'.$dbmap[$param[0]][$param[1]]['table'].'`';

               break;

               // simple_kind.type.attribute (attribute reference)
            case 3:
               if (!isset($dbmap[$param[0]][$param[1]]['table'])) return null;

               $search[]  = $reference;
               $replace[] = '`'.$dbmap[$param[0]][$param[1]]['table'].'`.`'.$param[2].'`';

               break;
                
               // kind.type (table reference)
            case 4:
               if (!isset($dbmap[$param[0]][$param[1]][$param[2]][$param[3]]['table'])) return null;

               $search[]  = $reference;
               $replace[] = '`'.$dbmap[$param[0]][$param[1]][$param[2]][$param[3]]['table'].'`';

               break;
                
               // kind.type.attribute (attribute reference)
            case 5:
               if (!isset($dbmap[$param[0]][$param[1]][$param[2]][$param[3]]['table'])) return null;

               $search[]  = $reference;
               $replace[] = '`'.$dbmap[$param[0]][$param[1]][$param[2]][$param[3]]['table'].'`.`'.$param[4].'`';

               break;
                
            default:
               return null;
         }
      }
       
      return str_replace($search, $replace, $query);
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/db/DBMysql#executeQuery($query)
    */
   public function executeQuery($query)
   {
      if (!$query = self::convertObjectQueryToSQL($query)) return null;
      
      return mysql_query($query, $this->conn);
   }
}
