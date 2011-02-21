<?php

class ObjectDeletion
{
   protected static $instance = null;
   
   private static $kinds = array(
      'catalogs',
      'documents'
   );
   
   protected
      $container = null,
      $cmanager  = null;
   
   /**
    * Get instance
    *
    * @return this
    */
   public static function getInstance(array $options = array())
   {
      if (empty(self::$instance))
      {
         self::$instance = new self($options);
      }

      return self::$instance;
   }
   
   /**
    * Construct
    * 
    * @param array& $options
    * @return void
    */
   protected function __construct(array& $options = array())
   {
      $this->container = Container::getInstance();
      $this->cmanager  = $this->container->getConfigManager($options);
   }
   
   /**
    * Return all marked for delition objects
    * 
    * @param array& $options
    * @return array or null
    */
   public function getMarked(array $options = array())
   {
      $ret   = array();
      $kinds = self::$kinds;
      
      foreach ($kinds as $kind)
      {
         $types = $this->cmanager->getInternalConfiguration($kind.'.'.$kind);
         
         foreach ($types as $type)
         {
            if (null === ($res = $this->container->getCModel($kind, $type)->getMarkedForDeletion()))
            {
               return null;
            }
            
            if (!empty($res)) $ret[$kind][$type] = $res;
         }
      }
      
      return $ret;
   }
   
   /**
    * Return list of entities related with specified
    * 
    * [
    *   $params = array(
    *      <kind_1> => array(
    *         <type_1> => array(<id_1>,.., <id_N>),
    *         ....................................
    *      ),
    *      ..................
    *   )
    * ] 
    * 
    * @param array $params
    * @param array $options
    * @return array or null
    */
   public function getListOfRelated($params, array $options = array())
   {
      $ret   = array();
      $kinds = self::$kinds;
      
      foreach ($params as $kind => $types)
      {
         if (!in_array($kind, $kinds)) return null;
         
         if (!is_array($types)) return null;
         
         foreach ($types as $type => $ids)
         {
            if (null === ($res = $this->container->getCModel($kind, $type)->getRelatedEntities($ids)))
            {
               return null;
            }
            
            if (!empty($res)) $ret[$kind][$type] = $res;
         }
      }
      
      return $ret;
   }
   
  /**
   * Retrieve data for DeleteMarkedForDeletion form
   * 
   * @param array $options
   * @return array
   */
   public function displayDeletionForm(array $options = array())
   {
      $result = $this->getMarked();
      $status = ($result === null) ? false : true;
      
      return array(
         'status' => $status,
         'result' => $result,
         'errors' => $status ? array() : array('Database error')
      );
   }
   
   /**
    * Delete marked for delition
    * 
    * @param string $kind
    * @param string $type
    * @param array  $ids
    * @param array& $options
    * @return array - errors
    */
   public function deleteMarkedForDeletion($kind, $type, $ids, array $options = array())
   {
      $errors = $this->container->getCModel($kind, $type)->deleteMarkedForDeletion($ids, $options);
      
      if ($errors)
      {
         $status = false;
         $msg = 'Not deleted';
      }
      else
      {
         $status = true;
         $msg = 'Deleted successfully';
      }
      
      return array(
         'status' => $status,
         'result' => array('msg' => $msg),
         'errors' => $errors
      );
   }
}
