<?php

require_once('lib/controller/base/BaseController.php');

class AccumulationRegistersController extends BaseController
{
   const kind = 'AccumulationRegisters';
   
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
   
   /**
    * Get totals
    * 
    * @param mixed $date
    * @param array $options
    * @return array
    */
   public function getTotals($date = null, array $options = array())
   {
      $errors = array();
      
      $cmodel = $this->container->getCModel($this->kind, $this->type, $options);
      
      return array(
         'status' => true,
         'result' => array('total' => $cmodel->getTotals($date, $options)),
         'errors' => $errors
      );
   }
}
