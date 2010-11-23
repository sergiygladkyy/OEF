<?php

import('lib/db/DBManager');

/**
 * Управление соединением с БД
 */
class DBMysql implements DBManager
{
   protected static $instance = null;

   protected $conn = null;

   protected $prefix = '';

   protected $charset = 'utf8';
   
   protected $dbname = null;


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
         self::$instance = new DBMysql($options);
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
      if (!function_exists('mysql_connect')) throw new Exception(__METHOD__.': The MySQL adapter is not available');

      if (empty($options['dbserver']) ||
          empty($options['dbusername']) ||
         !isset($options['dbpass']) ||
          empty($options['dbname'])
      )
      {
         throw new Exception(__METHOD__.': db configuration is wrong');
      }
      
      extract($options, EXTR_PREFIX_ALL, "");

      if (!empty($_dbprefix))  $this->prefix  = $_dbprefix;
      if (!empty($_dbcharset)) $this->charset = $_dbcharset;

      $this->connect($_dbserver, $_dbusername, $_dbpass);

      $this->selectDB($_dbname);
   }

   /**
    * Close connection
    *
    * @return void
    */
   public function __destruct()
   {
      if (!is_null($this->conn))
      {
         mysql_close($this->conn);
      }
   }



   /**
    * Connect to database
    *
    * @throws Exception
    * @param string $dbserver
    * @param string $dbusername
    * @param string $dbpass
    * @return void
    */
   protected function connect($dbserver, $dbusername, $dbpass)
   {
      $this->conn = mysql_connect($dbserver, $dbusername, $dbpass);
       
      if (!$this->conn)
      {
         throw new Exception(__METHOD__.": Can't connect to mysql database on $dbserver thru $dbusername with password $dbpass");
      }
       
      if (!mysql_query("SET CHARACTER SET ".$this->charset)) throw new Exception(__METHOD__.": Can't set encoding");
      if (!mysql_query("SET character_set_client = ".$this->charset)) throw new Exception(__METHOD__.": Can't set encoding");
      if (!mysql_query("SET character_set_results = ".$this->charset)) throw new Exception(__METHOD__.": Can't set encoding");
      if (!mysql_query("SET character_set_connection = ".$this->charset)) throw new Exception(__METHOD__.": Can't set encoding");
   }

   /**
    * Select database
    *
    * @throws Exception
    * @param string $dbname
    * @return void
    */
   public function selectDB($dbname)
   {
      if (!mysql_select_db("$dbname", $this->conn))
      {
         throw new Exception(__METHOD__.": Can't select mysql database $dbname");
      }
      
      $this->dbname = $dbname;
   }

   /**
    * Reconnection to other database
    *
    * @param string $dbserver
    * @param string $dbusername
    * @param string $dbpass
    * @param string $dbname
    * @return void
    */
   public function reconnection($dbserver, $dbusername, $dbpass, $dbname)
   {
      mysql_close($this->conn);

      $this->connect($dbserver, $dbusername, $dbpass);

      $this->selectDB($dbname);
   }



   
   /**
    * (non-PHPdoc)
    * @see lib/db/mDBManager#fetchRow($res)
    */
   public function fetchRow($res)
   {
      return mysql_fetch_row($res);
   }

   /**
    * (non-PHPdoc)
    * @see lib/db/mDBManager#fetchAssoc($res)
    */
   public function fetchAssoc($res)
   {
      return mysql_fetch_assoc($res);
   }

   /**
    * (non-PHPdoc)
    * @see lib/db/mDBManager#fetchArray($res)
    */
   public function fetchArray($res)
   {
      return mysql_fetch_array($res);
   }
    
   /**
    * (non-PHPdoc)
    * @see lib/db/DBManager#fetchObject($res)
    */
   public function fetchObject($res)
   {
      return mysql_fetch_object($res);
   }

   /**
    * (non-PHPdoc)
    * @see lib/db/mDBManager#getNumRows()
    */
   public function getNumRows()
   {
      return mysql_num_rows($this->conn);
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/db/DBManager#getAffectedRows()
    */
   public function getAffectedRows()
   {
      return mysql_affected_rows($this->conn);
   }

   /**
    * (non-PHPdoc)
    * @see lib/db/mDBManager#getInsertId()
    */
   public function getInsertId()
   {
      return mysql_insert_id($this->conn);
   }

   /**
    * (non-PHPdoc)
    * @see lib/db/DBManager#getErrno()
    */
   public function getErrno()
   {
      return mysql_errno($this->conn);
   }

   /**
    * (non-PHPdoc)
    * @see lib/db/mDBManager#getError()
    */
   public function getError()
   {
      return mysql_error($this->conn);
   }

   /**
    * (non-PHPdoc)
    * @see lib/db/DBManager#realEscapeString($str)
    */
   public function realEscapeString($str)
   {
      return mysql_real_escape_string($str, $this->conn);
   }
   
   
   
   /**
    * (non-PHPdoc)
    * @see lib/db/DBManager#executeQuery($query)
    */
   public function executeQuery($query)
   {
      return mysql_query($query, $this->conn);
   }

   /**
    * (non-PHPdoc)
    * @see lib/db/DBManager#freeResult($linkRes)
    */
   public function freeResult($linkRes)
   {
      return mysql_free_result($linkRes);
   }
   
   /**
    * Get first row from result as object
    * 
    * @param string $query
    * @param array $options - not supported
    * @return object or null
    */
   public function loadObject($query, array $options = array())
   {
      if (!($res = $this->executeQuery($query))) return null;
      
      if ($object = $this->fetchObject($res))
      {
         return $object;
      }
      
      return null;
   }
   
   /**
    * Get result as list object
    * 
    * @param string $query
    * @param array $options
    *    array(
    *       'key'  => <primary key fild name>, // return array with row index == key
    *    )
    * @return array or null
    */
   public function loadObjectList($query, array $options = array())
   {
      if (!($res = $this->executeQuery($query))) return null;
      
      $list = array();
      
      if (!empty($options['key']))
      {
         while ($row = $this->fetchObject($res)) $list[$row->$options['key']] = $row;
      }
      else
      {
         while ($row = $this->fetchObject($res)) $list[] = $row;
      }
      
      return $list;
   }
   
   /**
    * Get first row from result as numeric array
    * 
    * @param string $query
    * @param array $options - not supported
    * @return array or null
    */
   public function loadRow($query, array $options = array())
   {
      if (!($res = $this->executeQuery($query))) return null;
      
      if ($row = $this->fetchRow($res))
      {
         return $row;
      }
      
      return null;
   }
   
   /**
    * Get result as list numeric arrays
    *  
    * @param string $query
    * @param array $options
    *    array(
    *       'key'   => <primary key index>, // return array with row index == key
    *       'field' => <field index>        // return numeric array with value == field value
    *    )
    *    'key' XOR 'field'
    * @return array or null
    */
   public function loadRowList($query, array $options = array())
   {
      if (!($res = $this->executeQuery($query))) return null;
      
      $list = array();
      
      if (!empty($options['key']))
      {
         while ($row = $this->fetchRow($res)) $list[$row[$options['key']]] = $row;
      }
      elseif (!empty($options['field']))
      {
         while ($row = $this->fetchRow($res)) $list[] = $row[$options['field']];
      }
      else
      {
         while ($row = $this->fetchRow($res)) $list[] = $row;
      }
      
      return $list;
   }
   
   /**
    * Get first row from result as assoc array
    * 
    * @param string $query
    * @param array $options - not supported
    * @return array or null
    */
   public function loadAssoc($query, array $options = array())
   {
      if (!($res = $this->executeQuery($query))) return null;
      
      if ($row = $this->fetchAssoc($res))
      {
         return $row;
      }
      
      return null;
   }
   
   /**
    * Get result as list assoc arrays
    *  
    * @param string $query
    * @param array $options
    *    array(
    *       'key'   => <primary key name>, // return array with row index == key
    *       'field' => <field name>        // return numeric array with value == field value
    *    )
    *    'key' XOR 'field'
    * @return array or null
    */
   public function loadAssocList($query, array $options = array())
   {
      if (!($res = $this->executeQuery($query))) return null;
      
      $list = array();
      
      if (!empty($options['key']))
      {
         while ($row = $this->fetchAssoc($res)) $list[$row[$options['key']]] = $row;
      }
      elseif (!empty($options['field']))
      {
         while ($row = $this->fetchAssoc($res)) $list[] = $row[$options['field']];
      }
      else
      {
         while ($row = $this->fetchAssoc($res)) $list[] = $row;
      }
      
      return $list;
   }
   
   /**
    * Get first row from result as numeric and assoc array
    * 
    * @param string $query
    * @param array $options - not supported
    * @return object or null
    */
   public function loadArray($query, array $options = array())
   {
      if (!($res = $this->executeQuery($query))) return null;
      
      if ($row = $this->fetchArray($res))
      {
         return $row;
      }
      
      return null;
   }
   
   /**
    * Get result as list numeric and assoc arrays
    * 
    * @param string $query
    * @param array $options
    *    array(
    *       'key'   => <primary key name or index>, // return array with row index == key
    *       'field' => <field name or index>        // return numeric array with value == field value
    *    )
    *    'key' XOR 'field'
    * @return array or null
    */
   public function loadArrayList($query, array $options = array())
   {
      if (!($res = $this->executeQuery($query))) return null;
      
      $list = array();
      
      if (!empty($options['key']))
      {
         while ($row = $this->fetchArray($res)) $list[$row[$options['key']]] = $row;
      }
      elseif (!empty($options['field']))
      {
         while ($row = $this->fetchArray($res)) $list[] = $row[$options['field']];
      }
      else
      {
         while ($row = $this->fetchArray($res)) $list[] = $row;
      }
      
      return $list;
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/db/DBManager#getAutoIncrementValue($table, $options)
    */
   public function getAutoIncrementValue($table, array $options = array())
   {
      $query = "SELECT `AUTO_INCREMENT` FROM information_schema.TABLES ".
               "WHERE TABLE_SCHEMA='".$this->dbname."' AND TABLE_NAME='".$table."'"; 
      
      if (!($res = $this->executeQuery($query))) return null;
      
      if ($row = $this->fetchArray($res))
      {
         return $row[0];
      }
      
      return null;
   }
 
}
