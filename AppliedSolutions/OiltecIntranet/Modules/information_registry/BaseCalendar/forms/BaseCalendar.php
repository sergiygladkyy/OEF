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
   
   $event['append_to_head']  = '<link rel="stylesheet" type="text/css" href="/ext/OEF/Framework/MindTouch/Js/oe_calendar/oe_calendar.css" />';
   $event['append_to_head'] .= '<script type="text/javascript" src="/ext/OEF/Framework/MindTouch/Js/oe_calendar/oe_calendar.js"></script>';
   
   $kind = $subject->getKind();
   $type = $subject->getType();
   $year_start  = 1990;
   $year_end    = 2040;
   $year_cur    = isset($params['year']) ? $params['year'] : date('Y');
   $attr_prefix = "aeform[".$kind."][".$type."]";
   
   $container = Container::getInstance();
   $cmodel = $container->getCModel('information_registry', 'BaseCalendar');
   $data   = $cmodel->getEntities($year_cur, array('attributes' => array('Year'), 'key' => 'Date'));
   $isNew  = empty($data);
   $data   = Utility::convertArrayToJSONString($data);
   
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
   $model = $container->getModel('information_registry', 'BaseCalendar');
   
   $dates = $event['values'];
   
   ksort($dates);
   reset($dates);
   
   $cnt = 0;
   $errors = array();
   
   foreach ($dates as $date => $working)
   {
      $ret = $model->fromArray(array(
         'Year'    => $date,
         'Date'    => $date,
         'Working' => ($working ? 1 : 0),
         'WorkingDayNumber' => ($working ? ++$cnt : 0)
      ), array('replace' => true));
      
      if ($ret)
      {
         $errors = array_merge($errors, $ret);
         continue;
      }
      
      $ret = $model->save();
      
      if ($ret) $errors = array_merge($errors, $ret);
   }
   
   if ($errors)
   {
      $event->setReturnValue(array(
         'status' => false,
         'errors' => $errors
      ));
   }
   else
   {
      $event->setReturnValue(array(
         'status' => true,
         'result' => array('msg' => 'Updated successfully'),
         'errors' => array()
      ));
   }
}
