<?php

interface DBManager
{
   /**
    * Reconnection to other database
    *
    * @param string $dbserver
    * @param string $dbusername
    * @param string $dbpass
    * @param string $dbname
    * @return void
    */
   public function reconnection($dbserver, $dbusername, $dbpass, $dbname);
    
   /**
    * Select database
    *
    * @param $dbname
    * @return void
    */
   public function selectDB($dbname);
    
    
   /**
    * Return one field with result as numeric array
    *
    * @param $res
    * @return array
    */
   public function fetchRow($res);
    
   /**
    * Return one field with result as assoc array
    *
    * @param $res
    * @return array
    */
   public function fetchAssoc($res);
    
   /**
    * Return one field with result as numeric and assoc array
    *
    * @param $res
    * @return array
    */
   public function fetchArray($res);
    
   /**
    * Return one field with result as object
    *
    * @param $res
    * @return object
    */
   public function fetchObject($res);
    
    

   
   /**
    * Return last insert id
    *
    * @return int
    */
   public function getInsertId();
    
   /**
    * Return last error text
    *
    * @return string
    */
   public function getError();
    
   /**
    * Return last error number
    *
    * @return int
    */
   public function getErrno();
   
   /**
    * 
    * @return int
    */
   public function getNumRows();
   
   /**
    * 
    * @return int
    */
   public function getAffectedRows();
   
   /**
    * Escape string to safe use in query
    *
    * @param string $str
    * @return string
    */
   public function realEscapeString($str);
   
   
   
   /**
    * Execute query
    *
    * @param string $query
    * @return mixed
    */
   public function executeQuery($query);

   /**
    * 
    * @param resurce $linkRes
    * @return bool
    */
   public function freeResult($linkRes);
   
   /**
    * Get first row from result as object
    * 
    * @param string $query
    * @param array $options
    * @return object
    */
   public function loadObject($query, array $options = array());
   
   /**
    * Get result as list object
    * 
    * @param string $query
    * @param array $options
    * @return array
    */
   public function loadObjectList($query, array $options = array());
   
   /**
    * Get first row from result as numeric array
    * 
    * @param string $query
    * @param array $options
    * @return object
    */
   public function loadRow($query, array $options = array());
   
   /**
    * Get result as list numeric arrays
    *  
    * @param string $query
    * @param array $options
    * @return array
    */
   public function loadRowList($query, array $options = array());
   
   /**
    * Get first row from result as assoc array
    * 
    * @param string $query
    * @param array $options
    * @return object
    */
   public function loadAssoc($query, array $options = array());
   
   /**
    * Get result as list assoc arrays
    *  
    * @param string $query
    * @param array $options
    * @return array
    */
   public function loadAssocList($query, array $options = array());
   
   /**
    * Get first row from result as numeric and assoc array
    * 
    * @param string $query
    * @param array $options
    * @return object
    */
   public function loadArray($query, array $options = array());
   
   /**
    * Get result as list numeric and assoc arrays
    * 
    * @param string $query
    * @param array $options
    * @return array
    */
   public function loadArrayList($query, array $options = array());
   
   /**
    * Return next value for AUTO_INCREMENT field
    * 
    * @param string $table - table name
    * @param array $options
    * @return int or null
    */
   public function getAutoIncrementValue($table, array $options = array());
}
