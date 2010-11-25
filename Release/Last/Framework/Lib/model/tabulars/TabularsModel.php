<?php

require_once('lib/model/base/BaseEntitiesModel.php');

class TabularsModel extends BaseEntitiesModel
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
