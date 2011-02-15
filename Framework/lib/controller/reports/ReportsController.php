<?php

require_once('lib/controller/base/BaseController.php');

class ReportsController extends BaseController
{
   const kind = 'reports';
   
   protected static $instance = array();
   
   protected function __construct($type, array& $options = array())
   {
      parent::__construct(self::kind, $type, $options);
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
    * Return params for ReportForm
    * 
    * @param array $headline
    * @param array $options
    * @return array
    */
   public function displayReportForm(array $headline = array(), array $options = array())
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
      else {$result = array('item' => array()); throw new Exception(print_r($default, true)); }
      
      // Get current item
      $model = $this->container->getModel($this->kind, $this->type, $options);
      
      $select = $model->retrieveSelectDataForRelated(array(), $options);
      
      $result['select'] = isset($result['select']) ? array_merge($select, $result['select']) : $select;
      
      $ret = array('status' => true,
                   'result' => $result,
                   'errors' => $errors
      );
      
      if (!empty($options['with_report']))
      {
         $res = $this->generate($headline, $options);
         
         $ret['status'] = $ret['status'] && $res['status'];
         $ret['result']['report'] = $res['result'];
         $ret['errors'] = array_merge($ret['errors'], $res['errors']);
      }
      
      return $ret;
   }
   
   /**
    * Generate report content
    * 
    * @param array $headline
    * @param array $options
    * @return array
    */
   public function generate(array $headline = array(), array $options = array())
   {
      $errors = array();
      
      $model = $this->container->getModel($this->kind, $this->type, $options);
      
      if (!empty($headline))
      {
         $errors = $model->fromArray($headline);

         if (!empty($errors))
         {
            return array('status' => false, 'result' => array(), 'errors' => $errors);
         }
      }
      
      $output = '';
      $errors = $model->generate($output);
      
      if (empty($errors))
      {
         $msg = 'Generated succesfully';
         $status = true;
      }
      else
      {
         $msg = 'Report not generated';
         $status = false;
      }
      
      return array('status' => $status,
                   'result' => array(
                      'output' => $output,
                      'msg'    => $msg
                   ),
                   'errors' => $errors
      );
   }
   
   /**
    * Decode report item
    * 
    * @param mixed $parameters
    * @return array
    */
   public function decode($parameters, array $options = array())
   {
      $model  = $this->container->getModel($this->kind, $this->type, $options);
      $result = $model->decode($parameters);
      
      if (isset($result['errors']))
      {
         return array('status' => false,
                      'result' => array('msg' => implode('; ', $result['errors']).'.'),
                      'errors' => $result['errors']
         );
      }
      
      return array('status' => true,
                   'result' => array(
                      'data' => $model->decode($parameters),
                   ),
                   'errors' => array()
      );
   }
}