<?php

require_once('lib/model/base/BaseEntityModel.php');

class BaseObjectModel extends BaseEntityModel
{
   /**
    * (non-PHPdoc)
    * @see lib/model/base/BaseModel#setup($kind, $type)
    */
   protected function initialize($kind, $type)
   {
      if (!parent::initialize($kind, $type)) return false;
      
      $confname = self::getConfigurationName($kind, $type);

      if (isset(self::$config[$confname]['relations'])) return true;
      
      $relations = $this->container->getConfigManager()->getInternalConfiguration('relations', $kind);

      self::$config[$confname]['relations'] = isset($relations[$type]) ? $relations[$type] : array();
      
      return true;
   }
   
   /**
    * (non-PHPdoc)
    * @see lib/model/BaseEntityModel#delete($options)
    */
   public function delete(array& $options = array())
   {
      if ($this->isNew) return array();
      
      // Remove this
      $model  = $this->container->getCModel($this->kind, $this->type, $options);
      $errors = $model->delete($this->id);
      
      if (!empty($errors)) return $errors;
      
      $this->isDeleted = true;
      
      return array();
   }

}