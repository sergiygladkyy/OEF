<?php

class Validator
{
   protected static $instance = array();
   
   protected function __construct(array& $options = array())
   {
      ;
   }
   
   /**
    * Get instance
    *
    * @return this
    */
   public static function getInstance(array $options = array())
   {
      if(empty(self::$instance))
      {
         self::$instance = new self($options);
      }

      return self::$instance;
   }
   
   /**
    * In array
    * 
    * @param string $type
    * @param mixed& $value
    * @param mixed $params
    * @return array - errors
    */
   public function in($type, & $value , $params)
   {
      if ($type == 'enum')
      {
         if (in_array($value, $params))
         {
            $value = (string) $value;
            return array();
         }
         elseif (array_key_exists($value, $params))
         {
            $value = (int) $value;
            return array();
         }
      }
      elseif (in_array($value, $params)) return array();
      
      return array('Invalid value');
   }
   
   /**
    * Limit min for numeric types
    * 
    * @param string $type
    * @param mixed& $value
    * @param mixed $params
    * @return array - errors
    */
   public function min($type, & $value , $params)
   {
      if ($value >= $params) return array();
      
      return array('Invalid value');
   }
   
   /**
    * Limit max for numeric types
    * 
    * @param string $type
    * @param mixed& $value
    * @param mixed $params
    * @return array - errors
    */
   public function max($type, & $value , $params)
   {
      if ($value <= $params) return array();
      
      return array('Invalid value');
   }
   
   /**
    * Limit min string length
    * 
    * @param string $type
    * @param mixed& $value
    * @param mixed $params
    * @return array - errors
    */
   public function min_length($type, & $value , $params)
   {
      $value = trim($value);
      
      if (strlen($value) >= $params) return array(); 
      
      return array('Invalid value');
   }
   
   /**
    * Limit max string length
    * 
    * @param string $type
    * @param mixed& $value
    * @param mixed $params
    * @return array - errors
    */
   public function max_length($type, & $value , $params)
   {
      $value = trim($value);
      
      if (strlen($value) <= $params) return array(); 
      
      return array('Invalid value');
   }
   
   /**
    * Check string by regexp
    * 
    * @param string $type
    * @param mixed& $value
    * @param mixed $params
    * @return array - errors
    */
   public function regexp($type, & $value , $params)
   {
      $value = trim($value);
      
      if (preg_match($params, $value)) return array();
      
      return array('Invalid value');
   }
   
   
   /**
    * Return "Not supported" error
    * 
    * @param string $method
    * @param array $args
    * @return array
    */
   public function __call($method, $args)
   {
      return array('Not supported restriction "'.$method.'"');
   }
}
