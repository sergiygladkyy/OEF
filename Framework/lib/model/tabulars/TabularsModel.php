<?php

require_once('lib/model/base/BaseEntitiesModel.php');

class TabularsModel extends BaseEntitiesModel
{
   protected static $instance = array();
   
   protected
      $owner_kind = null,
      $owner_type = null;

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
   
   /**
    * (non-PHPdoc)
    * @see BaseModel#initialize($kind, $type)
    */
   protected function initialize($kind, $type)
   {
      if (!parent::initialize($kind, $type)) return false;
      
      $pKind = Utility::parseKindString($this->kind);
      
      if (!isset($pKind['main_kind'])) return false;
      
      $this->owner_kind = $pKind['main_kind'];
      $this->owner_type = $pKind['main_type'];
      
      return true;
   }
   
   
   
   
   /************************** For control access rights **************************************/
   
   
   
   /**
    * (non-PHPdoc)
    * @see BaseEntitiesModel#delete($values, $options)
    */
   public function delete($values, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->owner_kind.'.'.$this->owner_type.'.Read'))
      {
         return array('Access denied');
      }
      
      // Execute method
      return parent::delete($values, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntitiesModel#getEntities($values, $options)
    */
   public function getEntities($values = null, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->owner_kind.'.'.$this->owner_type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      return parent::getEntities($values, $options);
   }

   /**
    * (non-PHPdoc)
    * @see BaseEntitiesModel#hasEntities($values, $options)
    */
   public function hasEntities($values, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->owner_kind.'.'.$this->owner_type.'.Read'))
      {
         return false;
      }
      
      // Execute method
      return parent::hasEntities($values, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntitiesModel#countEntities($values, $options)
    */
   public function countEntities($values = null, array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->owner_kind.'.'.$this->owner_type.'.Read'))
      {
         return 0;
      }
      
      // Execute method
      return parent::countEntities($values, $options);
   }

   
   /**
    * (non-PHPdoc)
    * @see BaseEntitiesModel#retrieveSelectDataForRelated($fields, $options)
    */
   public function retrieveSelectDataForRelated($fields = array(), array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->owner_kind.'.'.$this->owner_type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      return parent::retrieveSelectDataForRelated($fields, $options);
   }
}
