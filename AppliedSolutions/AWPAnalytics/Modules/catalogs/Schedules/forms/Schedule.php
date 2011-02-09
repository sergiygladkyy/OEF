<?php
/**
 * Generate form
 * 
 * @param object $event
 * @return void
 */
function onGenerate($event)
{
   $subject = $event->getSubject();
   $name    = $event['name'];
   $params  = $event['parameters'];
   
   $event['append_to_head']  = '<link rel="stylesheet" type="text/css" href="/ext/OEF/Framework/MindTouch/Js/oe_schedule/oe_schedule.css" />';
   $event['append_to_head'] .= '<script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_schedule/oe_schedule.js"></script>';
   
   $kind = $subject->getKind();
   $type = $subject->getType();
   $year_start  = 1990;
   $year_end    = 2040;
   $year_cur    = isset($params['year']) ? $params['year'] : date('Y');
   $attr_prefix = "aeform[".$kind."][".$type."]";
   
   $container = Container::getInstance();
   
   // Calendar
   $cmodel   = $container->getCModel('information_registry', 'BaseCalendar');
   $calendar = $cmodel->getEntities($year_cur, array('attributes' => array('Year'), 'key' => 'Date'));
   $calendar = Utility::convertArrayToJSONString($calendar);
   
   // Schedule
   if (!empty($params['schedule']))
   {
      $schedule = $params['schedule'];
      
      $cmodel = $container->getCModel('information_registry', 'Schedules');
      $values = array(
         'Schedule' => $schedule,
         'Year'     => $year_cur
      );
      $options = array(
         'attributes' => array('Schedule', 'Year'),
         'criterion'  => "`Schedule` = %%Schedule%% AND `Year` = %%Year%%",
         'key' => 'Date'
      );
      $data = $cmodel->getEntities($values, $options);
   }
   else $schedule = 0;
   
   if (empty($data))
   {
      $data  = '{}';
      $isNew = true;
   }
   else $data = Utility::convertArrayToJSONString($data);
   
   include(self::$templates_dir.$name.'.php');
}

/**
 * Process form
 * 
 * @param object $event
 * @return void
 */
function onProcess($event)
{
   $container = Container::getInstance();
   
   $errors = array();
   $values = $event['values'];
   unset($values['Schedule']);
   
   $model = $container->getModel('catalogs', 'Schedules');
   
   if (!($ret = $model->fromArray($values)))
   {
      if ($ret = $model->save()) $errors = $ret;
   }
   else $errors = $ret;
   
   if ($errors)
   {
      $event->setReturnValue(array(
         'status' => false,
         'result' => array(
            'msg' => 'Schedule not '.(isset($values['_id']) ? 'updated' : 'created')
         ),
         'errors' => $errors
      ));
      
      return;
   }
   
   $schedule = $model->getId(); 
   $result   = array();
   
   if (!isset($values['_id']))
   {
      $result['_id'] = $schedule;
   }
   
   $model = $container->getModel('information_registry', 'Schedules');
   
   $dates = $event['values']['Schedule'];
   
   ksort($dates);
   reset($dates);
   
   foreach ($dates as $date => $hours)
   {
      $ret = $model->fromArray(array(
         'Schedule' => $schedule,
         'Year'     => $date,
         'Date'     => $date,
         'Hours'    => ($hours ? (float) $hours : 0),
      ), array('replace' => true));
      
      if ($ret)
      {
         $errors = array_merge($errors, $ret);
         continue;
      }
      
      $ret = $model->save();
      
      if ($ret) $errors = array_merge($errors, $ret);
   }
   
   if (!$errors)
   {
      $status = true;
      $result['msg'] = 'Schedule '.(isset($values['_id']) ? 'updated' : 'created').' sucessfuly';
   }
   else $status = false;
   
   $event->setReturnValue(array(
      'status' => $status,
      'result' => $result,
      'errors' => $errors
   ));
}
