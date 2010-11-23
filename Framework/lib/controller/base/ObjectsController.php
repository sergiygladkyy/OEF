<?php

require_once('lib/controller/base/BaseController.php');

abstract class ObjectsController extends BaseController
{
   protected function __construct($kind, $type, array& $options = array())
   {
      parent::__construct($kind, $type, $options);
   }
   
   /**
    * Return params for EditForm
    * 
    * @param int $id
    * @param array& $options
    * @return array
    */
   public function displayEditForm($id = null, array $options = array())
   {
      $errors = array();
      
      $item = $this->container->getModel($this->kind, $this->type, $options);
      
      if (!is_null($id) && !$item->load($id, $options))
      {
         $errors[] = 'Can\'t load "'.ucfirst($this->kind).'.'.$this->type.'" with id '.(int) $id;
         
         return array('status' => false, 'result' => null, 'errors' => $errors);
      } 
      
      $model  = $this->container->getCModel($this->kind, $this->type, $options);
      $select = $model->retrieveSelectDataForRelated(array(), $options);
      $types  = $model->getTabularsList();
      
      $tabulars = array();
      
      foreach ($types as $type)
      {
         $controller = $this->container->getController($this->kind.'.'.$this->type.'.tabulars', $type, $options);
         $taboptions = (!empty($options[$type]) && is_array($options[$type])) ? $options[$type] : array();
         
         if (empty($taboptions['criteria']))
         {
            // This is owner
            $taboptions['criteria'] = array(
               'attributes' => 'owner',
               'criterion'  => '`owner`=%%owner%%',
               'values'     => array('owner' => $id)
            );
         }
         
         $tabpage = isset($taboptions['page']) ? $taboptions['page'] : 1;
         $tabulars[$type] = $controller->displayListForm($tabpage, $taboptions);
         
         if (!$tabulars[$type]['status']) continue;
         
         $tmodel = $this->container->getCModel($this->kind.'.'.$this->type.'.tabulars', $type, $options);
         $tabulars[$type]['result']['select'] = $tmodel->retrieveSelectDataForRelated(array(), $taboptions);
         
         /* BEGIN - FOR MT */
         if (!empty($tabulars[$type]['result']['pagination']))
         {
            $pagin =& $tabulars[$type]['result']['pagination'];

            for ($i = $pagin['first']; $i <= $pagin['last']; $i++) $pagin['FOR_MT'][] = $i;
         }
         /* END - FOR MT */
      }
      
      return array('status' => true, 
                   'result' => array(
                      'item'     => $item->toArray($options),
                      'select'   => $select,
                      'tabulars' => $tabulars
                   ),
                   'errors' => $errors
      );
   }
   
   /**
    * Return params for ItemForm
    * 
    * @param int $id
    * @param array $options
    * @return array
    */
   public function displayItemForm($id, array $options = array())
   {
      $errors = array();
      
      $item = $this->container->getModel($this->kind, $this->type, $options);
      
      if (!$item->load($id, $options))
      {
         $errors[] = 'Can\'t load "'.ucfirst($this->kind).'.'.$this->type.'" with id '.(int) $id;
         
         return array('status' => false, 'result' => null, 'errors' => $errors);
      } 
      
      $model = $this->container->getCModel($this->kind, $this->type, $options);
      $types = $model->getTabularsList();
      
      $tabulars = array();
      
      foreach ($types as $type)
      {
         $controller = $this->container->getController($this->kind.'.'.$this->type.'.tabulars', $type, $options);
         $taboptions = (!empty($options[$type]) && is_array($options[$type])) ? $options[$type] : array();
         $taboptions['with_link_desc'] = true;
          
         if (empty($taboptions['criteria']))
         {
            // This is owner
            $taboptions['criteria'] = array(
               'attributes' => 'owner',
               'criterion'  => '`owner`=%%owner%%',
               'values'     => array('owner' => $id)
            );
         }
          
         $tabpage = isset($taboptions['page']) ? $taboptions['page'] : 1;
         $tabulars[$type] = $controller->displayListForm($tabpage, $taboptions);
         
         /* BEGIN - FOR MT */
         if (!empty($tabulars[$type]['result']['pagination']))
         {
            $pagin =& $tabulars[$type]['result']['pagination'];

            for ($i = $pagin['first']; $i <= $pagin['last']; $i++) $pagin['FOR_MT'][] = $i;
         }
         /* END - FOR MT */
      }
      
      return array('status' => true, 
                   'result' => array(
                      'item'     => $item->toArray($options),
                      'tabulars' => $tabulars
                   ),
                   'errors' => $errors
      );
   }
   
   /**
    * Restore entities
    * 
    * @param mixed $ids
    * @param array $options
    * @return array
    */
   public function restore($ids, array $options = array())
   {
      $status = true;
      
      $cmodel = $this->container->getCModel($this->kind, $this->type, $options);
      $errors = $cmodel->restore($ids, $options);
      
      if ($errors)
      { 
         $status = false;
         $result['msg'] = '"'.ucfirst($this->kind).'.'.$this->type.'" not restored';
      }
      else $result['msg'] = '"'.ucfirst($this->kind).'.'.$this->type.'" restored succesfully';
      
      return array('status' => $status, 'result' => $result, 'errors' => $errors);
   }
}