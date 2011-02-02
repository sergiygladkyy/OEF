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
   
   $card = array();
   $kind = $subject->getKind();
   $type = $subject->getType();
   $attr_prefix = "aeform[".$kind."][".$type."]";
   
   $container = Container::getInstance();
   $document  = $container->getModel('documents', 'TimeCard');
   
   // Get values for User
   $employees = MEmployees::getNowWorksForSelect();
   
   // Get values for Period
   $periods = array();
   $ts      = time();
   $ctime   = mktime(0,0,0,1,1,date('Y', $ts));
   $day     = date('w', $ctime);
   $msday   = 24*60*60;
   $week    = 1;
   
   if ($day != 1)
   {
      if ($day == 0) $day = 7;
      
      $ctime += (8 - $day)*$msday;
   }
   
   while ($ctime < $ts)
   {
      $from   = $ctime;
      $ctime += 6 * $msday;
      $to     = $ctime;
      
      $periods[] = array(
         'value' => date('Y-m-d', $from).'|'.date('Y-m-d', $to),
         'text'  => sprintf("Week %02d (%s - %s)", $week, date('d.m.Y', $from), date('d.m.Y', $to)) 
      );
      
      $week++;
      $ctime += $msday;
   }
   
   // Retrieve attributes
   if (!empty($params['document']) && is_numeric($params['document']) && (int) $params['document'] > 0)
   {
      $id = (int) $params['document'];
      
      if (!$document->load($id))
      {
         throw new Exception('Document not exists');
      }
   }
   
   $attrs = $document->toArray();
   
   if (!empty($params['Period']))
   {
      list($start) = explode('|', $params['Period']);
      $cleared     = $id ? true : false;
   }
   elseif (!empty($attrs['StartDate']))
   {
      $start = $attrs['StartDate'];
   }
   else $start  = date('Y-m-d');
   
   $start  = MGlobal::getFirstWeekDay($start);
   $end    = $start + 6*24*60*60;
   $period = date('Y-m-d', $start).'|'.date('Y-m-d', $end);
   
   if (!empty($params['Employee']))
   {
      $employee = $params['Employee'];
      $cleared  = $id || $cleared ? true : false;
   }
   elseif (!empty($attrs['Employee']))
   {
      $employee = $attrs['Employee'];
   }
   else $employee = 0;
   
   if ($employee)
   {
      $assign = MProjects::getEmployeeProjects($employee, date('Y-m-d', $start), date('Y-m-d', $end));
   }
   else $assign = array();
   
   if ($id)
   {
      $records = $container->getCModel('documents.TimeCard.tabulars', 'TimeRecords');
      
      if ($cleared)
      {
         if ($records->delete($id, array('attributes' => 'Owner')))
         {
            throw new Exception('DataBaseError');
         }
         
         $tabulars = array();
      }
      else
      {
         $criterion = "WHERE `Owner` = ".$id." AND `Date` >= '".date('Y-m-d', $start)."' AND `Date` <= '".date('Y-m-d', $end)."' ".
                      "ORDER BY `Project` ASC, `SubProject` ASC, `Date` ASC";
         $tabulars  = $records->getEntities(null, array('criterion' => $criterion/*, 'with_link_desc' => true*/));
      }
   }
   else $tabulars = array();
   
   $ids = array();
   
   foreach ($assign as $row)
   {
      if (isset($card[$row['Project']][$item['SubProject']])) continue;
      
      if (isset($card[$row['Project']]))
      {
         $ids['SubProject'][$row['SubProject']] = $row['SubProject'];
      }
      else
      {
         $ids['Project'][$row['Project']]       = $row['Project'];
         $ids['SubProject'][$row['SubProject']] = $row['SubProject'];
      }
      
      $card[$row['Project']][$row['SubProject']] = array(
         0 => array('Hours' => 0),
         1 => array('Hours' => 0),
         2 => array('Hours' => 0),
         3 => array('Hours' => 0),
         4 => array('Hours' => 0),
         5 => array('Hours' => 0),
         6 => array('Hours' => 0)
      );
   }
   
   foreach ($tabulars/*['list']*/ as $item)
   {
      $time = strtotime($item['Date']);
       
      if ($time < $start || $time > $end) continue;
      
      if (!isset($card[$item['Project']][$item['SubProject']]))
      {
         $card[$item['Project']][$item['SubProject']] = array(
            0 => array('Hours' => 0),
            1 => array('Hours' => 0),
            2 => array('Hours' => 0),
            3 => array('Hours' => 0),
            4 => array('Hours' => 0),
            5 => array('Hours' => 0),
            6 => array('Hours' => 0)
         );
         
         $ids['Project'][$item['Project']]       = $item['Project'];
         $ids['SubProject'][$item['SubProject']] = $item['SubProject'];
      }
       
      if (!($day = date('w', $time)))
      {
         $day = 6;
      }
      else $day--;
       
      $card[$item['Project']][$item['SubProject']][$day]['_id']     = $item['_id'];
      $card[$item['Project']][$item['SubProject']][$day]['Hours']   = $item['Hours'];
      $card[$item['Project']][$item['SubProject']][$day]['Comment'] = $item['Comment'];
   }
   
   $links = array();
   
   $links['Project']    = $container->getCModel('catalogs', 'Projects')->retrieveLinkData($ids['Project']);
   $links['SubProject'] = $container->getCModel('catalogs', 'SubProjects')->retrieveLinkData($ids['SubProject']);
   
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
   
   $attrs   =& $values['attributes'];
   $tabular =& $values['tabulars']['TimeRecords'];
    
   // Check Period
   if (empty($values['week']))
   {
      $errors['Period'] = 'Required';
   }
   else
   {
      $period = explode('|', $values['week']);
      
      if (!isset($period[1]))
      {
         $errors['Period'] = 'Invalid value';
      }
      else
      {
         $attrs['StartDate'] = $period[0];
         $attrs['EndDate']   = $period[1];
      }
   }
   
   $attrs['Date'] = date('Y-m-d H:i:s');
   
   // Save document
   if (!$errors)
   {
      $model = $container->getModel('documents', 'TimeCard');
       
      if (!($ret = $model->fromArray($attrs)))
      {
         if ($ret = $model->save()) $errors = $ret;
      }
      else $errors = $ret;
   }
   
   if ($errors)
   {
      $event->setReturnValue(array(
         'status' => false,
         'result' => array(
            'msg' => 'Document not '.(isset($attrs['_id']) ? 'updated' : 'created')
         ),
         'errors' => $errors
      ));
      
      return;
   }
   
   $result = array();
   
   $id = $model->getId();
   
   if (!isset($attrs['_id']))
   {
      $result['_id'] = $id;
   }
   
   // Add TimeRecords
   $start  = strtotime($attrs['StartDate']);
   $hours  = 24*60*60;
   $status = true;
   
   $tabresult  = array();
   $controller = $container->getController('documents.TimeCard.tabulars', 'TimeRecords');
   
   foreach ($tabular as $i => $row)
   {
      $vals = array(
            'Owner'      => $id,
            'Project'    => $row['Project'],
            'SubProject' => $row['SubProject'],
            'Date'       => date('Y-m-d', $start + ($i%7)*$hours),
            'Hours'      => $row['Hours'],
            'Comment'    => isset($row['Comment']) ? $row['Comment'] : $row['Comment']
      );
       
      if (isset($row['_id']))
      {
         $vals['_id'] = $row['_id'];

         $method = 'update';
      }
      else $method = 'create';
      
      $tabresult[$i] = $controller->$method($vals);
       
      $status = $status && $tabresult[$i]['status'];
   }
   
   if ($status)
   {
      $result['msg'] = 'Document '.(isset($attrs['_id']) ? 'updated' : 'created').' sucessfully';
   }
   else
   {
      $result['msg'] = 'Time Records not updated<pre>'.print_r($tabular, true).'</pre>';
   }
   
   $event->setReturnValue(array(
      'status'   => $status,
      'result'   => $result,
      'errors'   => $errors,
      'tabulars' => array('TimeRecords' => $tabresult)
   ));
}
