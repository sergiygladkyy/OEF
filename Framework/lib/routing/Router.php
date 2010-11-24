<?php

abstract class Router
{
   protected $parameters = array();
   
   /**
    * Constructor
    * 
    * @param array $options
    * @return void
    */
   public function __construct(array $options = array())
   {
      ;
   }
   
   /**
    * Parse uri
    * 
    * @param string $uri
    * @return array - GET parameters
    */
   public function parseURI($uri)
   {
      $params = $this->processParse($uri);
      
      $this->parameters = array_merge($this->parameters, $params);
      
      return $this->parameters;
   }
   
   /**
    * Generate external URI by internal URL
    * 
    * @param string $url - internal URL
    * @return string external URI
    */
   public function generateURI($url)
   {
      return $this->processGenerate($url);
   }
   
   /**
    * Process parse
    * 
    * @param string $uri
    * @return array
    */
   abstract protected function processParse($uri);
   
   /**
    * Process geneate
    * 
    * @param string $url - internal URL
    * @return string external URI
    */
   abstract protected function processGenerate($url);
   
   /**
    * Get all parameters
    * 
    * @return array
    */
   public function getParameters()
   {
      return $this->parameters;
   }
   
   /**
    * Add parameter
    * 
    * @param mixed $name
    * @param mixed $value
    * @return void
    */
   public function addParameter($name, $value)
   {
      $this->parameters[$name] = $value;
   }
   
   /**
    * Get parameter
    * 
    * @param mixed $name
    * @param mixed $default
    * @return mixed
    */
   public function getParameter($name, $default = null)
   {
      if (isset($this->parameters[$name]))
      {
         return $this->parameters[$name];
      }
      
      return Utility::getArrayValueByPath($this->parameters, $name, $default);
   }
   
   /**
    * Set parameter
    * 
    * @param mixed $name
    * @param mixed $value
    * @param bool $by_path
    * @return boolean
    */
   public function setParameter($name, $value, $by_path = false)
   {
      if ($by_path)
      {
         return Utility::setArrayValueByPath($this->parameters, $name, $value);
      }
      
      $this->parameters[$name] = $value;
      
      return true;
   }
   
   /**
    * Remove parameter
    * 
    * @param mixed $name
    * @param mixed $value
    * @param bool $by_path
    * @return boolean
    */
   public function removeParameter($name, $by_path = false)
   {
      if ($by_path)
      {
         return Utility::removeArrayValueByPath($this->parameters, $name);
      }
      
      unset($this->parameters[$name]);
      
      return true;
   }
}