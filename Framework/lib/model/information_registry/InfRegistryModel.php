<?php

require_once('lib/model/base/BaseRegisterModel.php');

class InfRegistryModel extends BaseRegisterModel
{
   const kind = 'information_registry';
   
   public function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
   }
   
   
   
   
   /************************** For control access rights **************************************/
   
   
   
   /**
    * (non-PHPdoc)
    * @see BaseEntityModel#delete($options)
    */
   public function delete(array& $options = array())
   {
      // Check permissions
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Update'))
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
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
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
      if (defined('IS_SECURE') && !$this->isNew && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Read'))
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
      if (defined('IS_SECURE') && !$this->container->getUser()->hasPermission($this->kind.'.'.$this->type.'.Update'))
      { 
         return array('Access denied');
      }
      
      // Execute method
      return parent::save($options);
   }
}
