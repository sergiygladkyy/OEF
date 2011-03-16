<?php

require_once('lib/controller/base/EntityController.php');

abstract class ObjectsController extends EntityController
{
   protected $conf = array();
   
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
      $result = array();
      $tabdef = array();
      
      // Default values
      $default = $this->getDefaultValuesForEditForm('Default', array('id' => $id));
      
      if ($default['status'])
      {
         $def =& $default['result'];
         
         $result['item'] = $def['attributes'];
         
         if (isset($def['select']) && is_array($def['select']))
         {
            $result['select'] = $def['select'];
         }
         
         if (isset($def['tabulars']) && is_array($def['tabulars']))
         {
            $tabdef =& $def['tabulars'];
         }
      }
      else $result = array('item' => array());
      
      // Get current item
      $item = $this->container->getModel($this->kind, $this->type, $options);
      
      if (!empty($id))
      {
         if (!$item->load($id, $options))
         {
            $errors[] = 'Can\'t load '.ucfirst($this->kind).'.'.$this->type.' with id '.(int) $id;
            
            return array('status' => false, 'result' => null, 'errors' => $errors);
         }
         
         $result['item'] = $item->toArray($options);
      }
      else $result['item'] = array_merge($result['item'], $item->toArray($options));
      
      $item_opt = $options;
      
      if (!empty($this->conf['hierarchy']['type']) && $id)
      {
         $item_opt['pkey'] = $id;
      }
      
      if (!empty($this->conf['owners']) && $id)
      {
         $item_opt['otype'] = $result['item']['OwnerType'];
         $item_opt['oid']   = $result['item']['OwnerId'];
      }
      
      $model  = $this->container->getCModel($this->kind, $this->type, $options);
      $select = $model->retrieveSelectDataForRelated(array(), $item_opt);
      $types  = $model->getTabularsList();
      
      $result['select'] = isset($result['select']) ? array_merge($select, $result['select']) : $select;
      
      // Get current tabular sections
      $tabulars = array();
      
      foreach ($types as $type)
      {
         $taboptions = (!empty($options[$type]) && is_array($options[$type])) ? $options[$type] : array();
         
         if (!empty($id))
         {
            $controller = $this->container->getController($this->kind.'.'.$this->type.'.tabulars', $type, $options);
            
            if (empty($taboptions['criteria']))
            {
               // This is owner
               $taboptions['criteria'] = array(
                  'attributes' => 'Owner',
                  'criterion'  => '`Owner`=%%Owner%%',
                  'values'     => array('Owner' => $id)
               );
            }
             
            $tabpage = isset($taboptions['page']) ? $taboptions['page'] : 1;
            $tabulars[$type] = $controller->displayListForm($tabpage, $taboptions);
             
            if (!$tabulars[$type]['status']) continue;
         }
         else
         {
            $tabulars[$type] = array(
               'status' => true,
               'result' => array(),
               'errors' => array()
            );
            
            if (!empty($tabdef[$type]))
            {
               $tabulars[$type]['result']['list'] = (isset($tabdef[$type]['list']) && is_array($tabdef[$type]['list'])) ? $tabdef[$type]['list'] : array();
            }
            else
            {
               $tabulars[$type]['result']['list'] = array();
            }
         }
         
         if (!empty($tabdef[$type]) && isset($tabdef[$type]['select']) && is_array($tabdef[$type]['select']))
         {
            $tabulars[$type]['result']['select'] = $tabdef[$type]['select'];
         }
         
         $tmodel = $this->container->getCModel($this->kind.'.'.$this->type.'.tabulars', $type, $options);
         $select = $tmodel->retrieveSelectDataForRelated(array(), $taboptions);
         
         $tabulars[$type]['result']['select'] = isset($tabulars[$type]['result']['select']) ? array_merge($select, $tabulars[$type]['result']['select']) : $select;
         
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
                      'item'     => $result['item'],
                      'select'   => $result['select'],
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
               'attributes' => 'Owner',
               'criterion'  => '`Owner`=%%Owner%%',
               'values'     => array('Owner' => $id)
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
    * (non-PHPdoc)
    * @see ext/OEF/Framework/lib/controller/base/EntityController#delete($ids, $options)
    */
   public function delete($ids, array $options = array())
   {
      return array(
         'status' => false,
         'result' => array(
            'msg' => 'Not supported operation'
         ),
         'errors' => array()
      );
   }
   
   /**
    * Mark for deletion
    *  
    * @param mixed $ids
    * @param array $options
    * @return array
    */
   public function markForDeletion($ids, array $options = array())
   {
      $status = true;
      
      $cmodel = $this->container->getCModel($this->kind, $this->type, $options);
      $errors = $cmodel->markForDeletion($ids, $options);
      
      if ($errors)
      { 
         $status = false;
         $result['msg'] = 'Not marked for deletion';
      }
      else $result['msg'] = 'Marked for deletion succesfully';
      
      return array('status' => $status, 'result' => $result, 'errors' => $errors);
   }
   
   /**
    * Unmark for deletion
    *  
    * @param mixed $ids
    * @param array $options
    * @return array
    */
   public function unmarkForDeletion($ids, array $options = array())
   {
      $status = true;
      
      $cmodel = $this->container->getCModel($this->kind, $this->type, $options);
      $errors = $cmodel->unmarkForDeletion($ids, $options);
      
      if ($errors)
      { 
         $status = false;
         $result['msg'] = 'Not unmarked for deletion';
      }
      else $result['msg'] = 'Unmarked for deletion succesfully';
      
      return array('status' => $status, 'result' => $result, 'errors' => $errors);
   }
   
   /**
    * Return select data
    * 
    * @param mixed $fields
    * @param array $options
    * @return array
    */
   public function getSelectData($fields = array(), array $options = array())
   {
      $cmodel = $this->container->getCModel($this->kind, $this->type, $options);
      
      return array(
         'status' => true,
         'result' => $cmodel->retrieveSelectDataForRelated($fields, $options),
         'errors' => array()
      );
   }
   
}