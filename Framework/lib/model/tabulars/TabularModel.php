<?php

require_once('lib/model/base/BaseEntityModel.php');

class TabularModel extends BaseEntityModel
{
   protected
      $owner_kind = null,
      $owner_type = null;
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#initialize($kind, $type)
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
    * @see BaseEntityModel#delete($options)
    */
   public function delete(array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->owner_kind.'.'.$this->owner_type.'.Read'))
      {
         return array('Access denied');
      }
      
      // Execute method
      return parent::delete($options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#load($id, $options)
    */
   public function load($id, array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->owner_kind.'.'.$this->owner_type.'.Read'))
      {
         return false;
      }
      
      // Execute method
      return parent::load($id, $options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#toArray($options)
    */
   public function toArray(array $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->isNew && !$this->container->getUser()->hasPermission($this->owner_kind.'.'.$this->owner_type.'.Read'))
      {
         return array();
      }
      
      // Execute method
      return parent::toArray($options);
   }
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#save($options)
    */
   public function save(array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE'))
      { 
         $access = $this->container->getUser()->hasPermission($this->owner_kind.'.'.$this->owner_type.'.Insert');
         $access = $access || $this->container->getUser()->hasPermission($this->owner_kind.'.'.$this->owner_type.'.Update');
         
         if (!$access)
         {
            return array('Access denied');
         }
      }
      
      // Execute method
      return parent::save($options);
   }
}
