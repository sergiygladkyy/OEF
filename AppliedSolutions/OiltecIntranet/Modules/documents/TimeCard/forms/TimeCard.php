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
   
   // Check parameters
   if (!empty($params['Employee']))
   {
      $employee = (int) $params['Employee'];
   }
   elseif (!empty($attrs['Employee']))
   {
      $employee = $attrs['Employee'];
   }
   else
   {
      $employee = MEmployees::retrieveCurrentEmployee();
   }
   
   if (!empty($params['Period']))
   {
      list($start) = explode('|', $params['Period']);
   }
   elseif (!empty($attrs['StartDate']))
   {
      $start = $attrs['StartDate'];
   }
   else $start = date('Y-m-d');
   
   $start = MGlobal::getFirstWeekDay($start);

   
   // Get values for User
   $employees = MEmployees::getNowWorksForSelect();
   
   if ($employee > 0 && !isset($employees[$employee]))
   {
      $model = $container->getModel('catalogs', 'Employees');
      
      if (!$model->load($employee))
      {
         throw new Exception('Employee not exists');
      }
      
      $employees[$employee] = array('text' => $model->getAttribute('Description'), 'value' => $employee);
   }
   
   // Get values for Period
   $periods = array();
   $ts      = time();
   $ctime   = mktime(0,0,0,1,1,date('Y', $ts));
   $day     = date('w', $ctime);
   $msday   = 24*60*60;
   $week    = 1;
   $exists  = array();
   $wassign = array();
   
   if ($day != 1)
   {
      if ($day == 0) $day = 7;
      
      $ctime += (8 - $day)*$msday;
   }
   
   if ($employee > 0)
   {
      // Retrieve list of existing documents
      $cmodel = $container->getCModel($kind, $type);
      $criter = "WHERE `Employee` = ".$employee." AND `StartDate` >= '".date('Y-m-d', $ctime)."' AND `EndDate` <= '".date('Y-m-d', $ts)."'";
      
      if ($id) $criter .= ' AND `_id`<>'.$id; 
      
      if (null !== ($docs = $cmodel->getEntities(null, array('criterion' => $criter))) && !isset($docs['errors']))
      {
         foreach ($docs as $doc)
         {
            $exists[$doc['StartDate'].'|'.$doc['EndDate']] = true;
         }
      }
      
      // Retrieve assignment for current employee
      $assign_p = MProjects::getEmployeeProjects($employee, date('Y-m-d', $ctime), date('Y-m-d', $ts), false);
      
      foreach ($assign_p as $key => $row)
      {
         $_fweek = (int) MGlobal::getWeekNumber($row['DateFrom']);
         $_tweek = (int) MGlobal::getWeekNumber($row['DateTo']);
         
         for ($i = $_fweek; $i <= $_tweek; $i++)
         {
            $wassign[$i] = true;
         }
      }
   }
   
   while ($ctime < $ts)
   {
      $from   = $ctime;
      $ctime += 6 * $msday;
      $to     = $ctime;
      
      $value = date('Y-m-d', $from).'|'.date('Y-m-d', $to);
      $disab = isset($exists[$value]);
      
      if ($start == $from && $disab) $start = null;
      
      $periods[] = array(
         'value'    => $value,
         'text'     => sprintf("Week %02d (%s - %s)", $week, date('d.m.Y', $from), date('d.m.Y', $to)).($disab ? ' - Time card exists' : (!isset($wassign[$week]) ? ' - No assignments' : '')),
         'disabled' => $disab 
      );
      
      $week++;
      $ctime += $msday;
   }
   
   
   // Generate TimeCard
   if (!$start)
   {
      $period = 0;
      include(self::$templates_dir.$name.'.php');
      return;
   }
   
   $end     = $start + 6*24*60*60;
   $period  = date('Y-m-d', $start).'|'.date('Y-m-d', $end);
   
   if ($employee)
   {
      $assign = MProjects::getEmployeeAssignmentInfo($employee, date('Y-m-d', $start), date('Y-m-d', $end+$msday));
   }
   else $assign = array();
   
   if ($id)
   {
      $records = $container->getCModel('documents.TimeCard.tabulars', 'TimeRecords');
      $cleared = empty($params['cleared']) ? false : (bool) $params['cleared'];
      
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
      if (!isset($card[$row['Project']][$row['SubProject']]))
      {

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
            0 => array('Planed' => 0, 'Hours' => 0),
            1 => array('Planed' => 0, 'Hours' => 0),
            2 => array('Planed' => 0, 'Hours' => 0),
            3 => array('Planed' => 0, 'Hours' => 0),
            4 => array('Planed' => 0, 'Hours' => 0),
            5 => array('Planed' => 0, 'Hours' => 0),
            6 => array('Planed' => 0, 'Hours' => 0)
         );
      }
      
      $day = MGlobal::getDayNumber($row['Date']);
      
      $card[$row['Project']][$row['SubProject']][$day]['Planed'] = $row['Hours'];
   }
   
   foreach ($tabulars/*['list']*/ as $item)
   {
      $time = strtotime($item['Date']);
       
      if ($time < $start || $time > $end) continue;
      
      if (!isset($card[$item['Project']][$item['SubProject']]))
      {
         $card[$item['Project']][$item['SubProject']] = array(
            0 => array('Planed' => 0, 'Hours' => 0),
            1 => array('Planed' => 0, 'Hours' => 0),
            2 => array('Planed' => 0, 'Hours' => 0),
            3 => array('Planed' => 0, 'Hours' => 0),
            4 => array('Planed' => 0, 'Hours' => 0),
            5 => array('Planed' => 0, 'Hours' => 0),
            6 => array('Planed' => 0, 'Hours' => 0)
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
      
      if ($method != 'create') unset($tabresult[$i]['result']['_id']);
      
      $status = $status && $tabresult[$i]['status'];
   }
   
   if ($status)
   {
      $result['msg'] = 'Document '.(isset($attrs['_id']) ? 'updated' : 'created').' successfully';
   }
   else
   {
      $result['msg'] = 'Time Records not updated';
   }
   
   $event->setReturnValue(array(
      'status'   => $status,
      'result'   => $result,
      'errors'   => $errors,
      'tabulars' => array('TimeRecords' => $tabresult)
   ));
}
