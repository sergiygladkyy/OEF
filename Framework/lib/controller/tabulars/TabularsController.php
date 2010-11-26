<?php

require_once('lib/controller/base/BaseController.php');

class TabularsController extends BaseController
{
   protected static $instance = array();

   /**
    * Get instance
    *
    * @return this
    */
   public static function getInstance($kind, $type, array $options = array())
   {
      if(empty(self::$instance[$kind.$type]))
      {
         self::$instance[$kind.$type] = new self($kind, $type, $options);
      }

      return self::$instance[$kind.$type];
   }
}
