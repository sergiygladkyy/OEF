<?php

abstract class Storage
{
   protected $options = array();

   /**
    * Constructor
    *
    */
   public function __construct(array $options = array())
   {
      $this->initialize($options);

      if ($this->options['auto_shutdown'])
      {
         register_shutdown_function(array($this, 'shutdown'));
      }
   }

   /**
    * Initialize storage object
    * 
    * @param array& $options
    * @return boolean
    */
   protected function initialize(array& $options = array())
   {
      $this->options['auto_shutdown'] = false;
      
      if ($options) $this->options = array_merge($this->options, $options);
      
      return true;
   }

   /**
    * Returns options
    *
    * @return array
    */
   public function getOptions()
   {
      return $this->options;
   }

   /**
    * Read data
    *
    * @param string $key
    * @param mixed  $default
    * @return mixed
    */
   abstract public function read($key, $default = null);

   /**
    * Write data
    *
    * @param string $key
    * @param mixed  $data
    * @return void
    */
   abstract public function write($key, $data);
   
   /**
    * Remove data
    *
    * @param string $key
    * @return void
    */
   abstract public function remove($key);
   
   /**
    * Regenerate storage id
    *
    * @param boolean $destroy Whether to delete the old associated storage file or not
    * @return boolean
    */
   abstract public function regenerate($destroy = true);

   /**
    * Execute shutdown
    */
   abstract public function shutdown();
}
