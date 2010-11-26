<?php

require_once('lib/controller/base/ObjectsController.php');

class CatalogsController extends ObjectsController
{
   const kind = 'catalogs';
   
   protected static $instance = array();
   
   protected function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
   }
   
   /**
    * Get instance
    *
    * @return this
    */
   public static function getInstance($type, array $options = array())
   {
      if(empty(self::$instance[$type]))
      {
         self::$instance[$type] = new self($type, $options);
      }

      return self::$instance[$type];
   }
}
