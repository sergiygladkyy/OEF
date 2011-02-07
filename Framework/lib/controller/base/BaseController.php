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
   
   public function getKind()
   {
      return $this->kind;
   }
   
   public function getType()
   {
      return $this->type;
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
      $result = array();
      
      // Default values
      $default = $this->getDefaultValuesForEditForm();
      
      if ($default['status'])
      {
         $default =& $default['result'];
         
         $result['item'] = $default['attributes'];
         
         if (isset($default['select']) && is_array($default['select']))
         {
            $result['select'] = $default['select'];
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
      
      $model  = $this->container->getCModel($this->kind, $this->type, $options);
      $select = $model->retrieveSelectDataForRelated(array(), $options);
      
      $result['select'] = isset($result['select']) ? array_merge($select, $result['select']) : $select;
      
      return array('status' => true,
                   'result' => $result,
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
         $result['msg'] = 'Not deleted';
      }
      else $result['msg'] = 'Deleted succesfully';
      
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
         $return['result']['msg'] = 'Created succesfully';
      }
      else
      {
         $return['result']['msg'] = 'Not created';
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
         $return['result']['msg'] = 'Updated succesfully';
      }
      else
      {
         $return['result']['msg'] = 'Not updated';
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
      $event['name']       = $name;
      $event['parameters'] = $options;

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
            'result' => array(
               'form'    => $output,
               'scripts' => (!empty($event['append_to_head']) && is_string($event['append_to_head']) ? $event['append_to_head'] : '')
            ), 
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
   
   /**
    * Notify form event
    * 
    * @param string $formName
    * @param string $eventName
    * @param array& $formData
    * @param array& $parameters
    * @return array
    */
   public function notifyFormEvent($formName, $eventName, array& $formData = array(), array& $parameters = array())
   {
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.forms.'.$formName.'.'.$eventName);
      $event->setReturnValue(null);
      $event['formName'] = $formName;
      $event['formData'] = $formData;
      $event['parameters'] = $parameters;
      
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
      
      $errors = array();
      $result = $event->getReturnValue();
      
      if (is_null($result))
      {
         $status = false;
         $errors = array('Event not processed. Module error');
      }
      elseif (!is_array($result))
      {
         $status = false;
         $errors = array('Module error');
      }
      elseif (!isset($result['type']) || !isset($result['data']))
      {
         $status = false;
         $errors = array('Module error');
      }
      else $status = true;
      
      return array(
         'status' => $status,
         'result' => $status ? $result : array(), 
         'errors' => $errors
      );
   }
   
   /**
    * Get default values for edit form
    * 
    * @param array& $options
    * @return array
    */
   protected function getDefaultValuesForEditForm($formName = 'Default', array $options = array())
   {
      $event = $this->container->getEvent($this, $this->kind.'.'.$this->type.'.forms.'.$formName.'.onBeforeOpening');
      $event->setReturnValue(null);
      $event['formName'] = $formName;
      $event['options']  = $options;
      
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
      
      $errors = array();
      $result = $event->getReturnValue();
      
      if (is_null($result))
      {
         $status = false;
         $errors = array('Event not processed. Module error');
      }
      elseif (!is_array($result) || !(isset($result['attributes']) || isset($result['select']) || isset($result['tabulars'])))
      {
         $status = false;
         $errors = array('Module error');
      }
      else $status = true;
      
      return array(
         'status' => $status,
         'result' => $status ? $result : array(), 
         'errors' => $errors
      );
   }
}
