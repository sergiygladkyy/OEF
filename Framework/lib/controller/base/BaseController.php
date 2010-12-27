<?php

abstract class BaseController
{
   protected $kind;
   protected $type;
   protected $container = null;
   
   protected function __construct($kind, $type, array& $options = array())
   {
      $this->kind = $kind;
      $this->type = $type;
      
      $this->container = Container::getInstance();
   }
   
   /**
    * Return params for ListForm
    * 
    * @param int $page
    * @param array& $options
    * [
    *    array(
    *      'with_link_desc' => [boolean]
    *      'config' => array(
    *        'max_per_page'       => [int] // Max item in page
    *        'max_item_in_scroll' => [int] // Max item in scroll line
    *      ),
    *      'criteria' => array(
    *        'attributes' => [array] // List of attributes for the current entity 
    *                                // belonging to a selection criterion (See 
    *                                // BaseEntitiesModel::generateWhere and 
    *                                // BaseEntitiesModel::generateWhereByCriteria).
    *        'values'    => [mixed]  // Values of attributes
    *        'criterion'  => [string] // Template for WHERE sentence
    *      )
    *    )
    * ]
    * @return array
    */
   public function displayListForm($page = 1, array $options = array())
   {
      $status = true;
      $errors = array();

      $pager = $this->container->createPager($this->kind, $this->type, $options);
      $list  = $pager->retrievePage($page, $options);
      
      if (is_null($list))
      {
         $status   = false;
         $errors[] = 'Internal model error';
      }
      elseif (!empty($list['errors']))
      {
         $status = false;
         $errors = $list['errors'];
      } 
      
      return array('status' => $status, 'result' => $list, 'errors' => $errors);
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
      
      if (!empty($id) && !$item->load($id, $options))
      {
         $errors[] = 'Can\'t load "'.ucfirst($this->kind).'.'.$this->type.'" with id '.(int) $id;
         
         return array('status' => false, 'result' => null, 'errors' => $errors);
      } 
      
      $model  = $this->container->getCModel($this->kind, $this->type, $options);
      $select = $model->retrieveSelectDataForRelated(array(), $options);
      
      return array('status' => true, 
                   'result' => array(
                      'item'   => $item->toArray($options),
                      'select' => $select
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
      
      return array('status' => true, 'result' => array('item' => $item->toArray($options)), 'errors' => $errors);
   }
   
   
   
   /**
    * Delete entities
    * 
    * @param mixed $ids
    * @param array $options
    * @return array
    */
   public function delete($ids, array $options = array())
   {
      $status = true;
      
      $cmodel = $this->container->getCModel($this->kind, $this->type, $options);
      $errors = $cmodel->delete($ids, $options);
      
      if ($errors)
      { 
         $status = false;
         $result['msg'] = '"'.ucfirst($this->kind).'.'.$this->type.'" not deleted';
      }
      else $result['msg'] = '"'.ucfirst($this->kind).'.'.$this->type.'" deleted succesfully';
      
      return array('status' => $status, 'result' => $result, 'errors' => $errors);
   }
   
   /**
    * Create new entity
    * 
    * @param array $values
    * @param array $options
    * @return array
    */
   public function create(array $values, array $options = array())
   {
      $status = true;
      $return = $this->processFrom($values, $options);

      if ($return['status'])
      {
         $return['result']['msg'] = '"'.ucfirst($this->kind).'.'.$this->type.'" created succesfully';
      }
      else
      {
         $return['result']['msg'] = '"'.ucfirst($this->kind).'.'.$this->type.'" not created';
      }
      
      return $return;
   }
   
   /**
    * Update entity
    * 
    * @param array $values
    * @param array $options
    * @return array
    */
   public function update(array $values, array $options = array())
   {
      $status = true;
      $return = $this->processFrom($values, $options);
      
      if ($return['status'])
      { 
         $return['result']['msg'] = '"'.ucfirst($this->kind).'.'.$this->type.'" updated succesfully';
      }
      else
      {
         $return['result']['msg'] = '"'.ucfirst($this->kind).'.'.$this->type.'" not updated';
      }
      
      return $return;
   }
   
   
   /**
    * Process entity HTML-form
    * 
    * @param $values
    * @param $options
    * @return array
    */
   protected function processFrom($values, array $options = array())
   {
      $item   = $this->container->getModel($this->kind, $this->type, $options);
      $errors = $item->fromArray($values);
      
      if ($errors) return array('status' => false, 'errors' => $errors);
      
      $res = $item->save($options);
      
      if (empty($res))
      {
         return array('status' => true, 'result' => array('_id' => $item->getId()));
      }
      else return array('status' => false, 'errors' => $res);
   }

   /**
    * Generate custom form
    * 
    * @param string $name - form name
    * @param array $options
    * @return array
    */
   public function generateCustomForm($name, array $options = array())
   {
      // Check form name
      $forms = $this->container->getConfigManager($options)->getInternalConfiguration($this->kind.'.forms', $this->type);
      
      if (!in_array($name, $forms))
      {
         return array(
            'status' => false,
            'result' => array(),
            'errors' => array('Unknow form '.$name)
         );
      }
      
      // Generate form
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.forms.'.$name.'.onGenerate');
      $event['name']    = $name;
      $event['options'] = $options;

      try
      {
         ob_start();
         
         $this->container->getEventDispatcher()->notify($event);
         
         $output = ob_get_clean();
      }
      catch(Exception $e)
      {
         return array(
            'status' => false,
            'result' => array(),
            'errors' => array($e->getMessage())
         );
      }
      
      return array(
            'status' => true,
            'result' => array('form' => $output), 
            'errors' => array()
      );
   }
   
   /**
    * Process custom form
    * 
    * @param string $name - form name
    * @param mixed $values - values
    * @param array $options
    * @return array
    */
   public function processCustomForm($name, $values, array $options = array())
   {
      // Check form name
      $forms = $this->container->getConfigManager($options)->getInternalConfiguration($this->kind.'.forms', $this->type);
      
      if (!in_array($name, $forms))
      {
         return array(
            'status' => false,
            'result' => array(),
            'errors' => array('Unknow form '.$name)
         );
      }
      
      // Process form
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.forms.'.$name.'.onProcess');
      $event->setReturnValue(null);
      $event['name']    = $name;
      $event['values']  = $values;
      $event['options'] = $options;
      
      try
      {
         $this->container->getEventDispatcher()->notify($event);
      }
      catch(Exception $e)
      {
         return array(
            'status' => false,
            'result' => array(),
            'errors' => array($e->getMessage())
         );
      }
      
      $result = $event->getReturnValue();
      
      if (is_null($result))
      {
         $status = false;
         $errors = array('Form not processed. Module error');
      }
      elseif (!is_array($result))
      {
         $status = false;
         $errors = array('Module error');
      }
      elseif (!(isset($result['status']) && (isset($result['result']) || isset($result['errors']))))
      {
         $status = false;
         $errors = array('Module error');
      }
         
      return !isset($status) ? $result : array(
         'status' => $status,
         'result' => array(), 
         'errors' => $errors
      );
   }
}
