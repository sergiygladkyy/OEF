<?php

require_once('lib/controller/base/ObjectsController.php');

class CatalogsController extends ObjectsController
{
   const kind = 'catalogs';
   
   protected static $instance = array();
   protected static $conf     = array();
   
   protected function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
      
      $CManager = $this->container->getConfigManager($options);
      
      self::$conf['hierarchy'] = $CManager->getInternalConfiguration($this->kind.'.hierarchy', $this->type);
      self::$conf['owners']    = $CManager->getInternalConfiguration($this->kind.'.owners', $this->type);
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
    * Get list of children
    * 
    * @param int   $nodeId
    * @param array $options
    * @return array
    */
   public function getChildren($nodeId = null, array $options = array())
   {
      if (empty(self::$conf['hierarchy']['type']))
      {
         return self::displayListForm(1, $options);
      }
      
      $status = true;
      $errors = array();
      
      $cmodel = $this->container->getCModel($this->kind, $this->type);
      $nodes  = $cmodel->getChildren($nodeId, array('with_link_desc' => true));
      
      if (is_null($nodes))
      {
         $status   = false;
         $errors[] = 'Internal model error';
      }
      elseif (!empty($nodes['errors']))
      {
         $status = false;
         $errors = $nodes['errors'];
      } 
      
      return array('status' => $status, 'result' => $nodes, 'errors' => $errors);
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/controller/base/ObjectsController#displayEditForm($id, $options)
    */
   public function displayEditForm($id = null, array $options = array())
   {
      $return = parent::displayEditForm($id, $options);
      
      if (!empty(self::$conf['hierarchy']['type']) && $return['status'] && $id)
      {
         unset($return['result']['select']['Parent'][$id]);
      }
      
      return $return;
   }
}
