<?php
/**
 * Generate report
 *
 * @param object $event
 * @return void
 */
function onGenerate($event)
{
   $headline  = $event['headline'];
   $container = Container::getInstance();

   /* Get data */

   if (null === ($period = MGlobal::parseDatePeriodString($headline['Period'])))
   {
      if (is_string($headline['Period']) && (-1 !== ($ts = strtotime($headline['Period']))))
      {
         $period = array(
            0 => date('Y-m-d', $ts),          // !!! $headline['Period'] is Y-m-d without H:i:s
            1 => date('Y-m-d', $ts + 86399),
            'from' => date('Y-m-d', $ts),
            'to'   => date('Y-m-d', $ts + 86399)
         );
      }
      else throw new Exception('Invalid period');
   }
   
   if (empty($headline['extra']['ex_employees']) && 
       empty($headline['extra']['ex_projects'])  &&
       empty($headline['Project']) && 
       empty($headline['Employee']))
   {
      throw new Exception('You must choise projects and/or employees');
   }
   
   $projects  = array();
   $employees = array();
   
   if (!empty($headline['extra']['ex_projects']))
   {
      $projects = is_array($headline['extra']['ex_projects']) ? $headline['extra']['ex_projects'] : array($headline['extra']['ex_projects']);
   }
   
   if (!empty($headline['Project']))
   {
      $projects[] = $headline['Project'];
   }
   
   if (!empty($headline['extra']['ex_employees']))
   {
      $employees = is_array($headline['extra']['ex_employees']) ? $headline['extra']['ex_employees'] : array($headline['extra']['ex_employees']);
   }
   
   if (!empty($headline['Employee']))
   {
      $employees[] = $headline['Employee'];
   }
   
   // Retrieve records
   $odb   = $container->getODBManager();
   $query = "SELECT `Employee`, `Project`, `Date`, `Hours`, `_rec_type` AS `type`, `_rec_id` AS `id` ".
            "FROM information_registry.ProjectAssignmentRecords ".
            "WHERE `Date` >= '".$period[0]."' AND `Date` <= '".$period[1]."' ".
            ($employees ? 'AND `Employee` IN ('.implode(',', $employees).') ' : '').
            ($projects  ? 'AND `Project`  IN ('.implode(',', $projects ).') ' : '').
            "ORDER BY `_rec_type`, `_rec_id`, `Date`";
   
   if (false == ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }
   
   $docs = array();
   $dids = array();
   
   while ($row = $odb->fetchAssoc($res))
   {
      if (isset($docs[$row['type']][$row['id']]))
      {
         $docs[$row['type']][$row['id']] += $row['Hours'];
      }
      else $docs[$row['type']][$row['id']] = $row['Hours'];
      
      $dids[$row['type']][$row['id']] = $row['id'];
   }
   
   $links = array();
   
   if (!empty($dids))
   {
      foreach ($dids as $type => $ids)
      {
         $links[$type] = $container->getCModel('documents', $type)->retrieveLinkData($ids);
      }
   }
   
   
   /* Generate report */
   
   $mockup = new Mockup(self::$templates_dir.'ResourceAllocationRecords.htm');
   $report = new TabularDoc();

   $area = $mockup->getArea('header');
   $area->parameters['header'] = 'Resource Allocation Records';
   
   $report->put($area);
   $report->put($mockup->getArea('headline'));

   $area = $mockup->getArea('item');
   
   foreach ($docs as $type => $params)
   {
      foreach ($params as $id => $hours)
      {
         $area->parameters['Document'] = $links[$type][$id]['text'];
         $area->parameters['Hours']    = $hours;
         
         $area->decode['Document'] = array(
            'uid'     => 'documents.'.$type,
            'actions' => 'displayItemForm',
            'id'      => $id
         );
         
         $report->put($area);
      }
   }
   
   echo $report->show();
}
 
/**
 * Decode item value
 *
 * @param object $event
 * @return void
 */
function onDecode($event)
{
   list($decode, $param) = each($event['parameters']);

   switch($decode)
   {
      case 'Document':
         $ret['reference'] = $param;
         break;

      default:
         $ret = null;
   }

   $event->setReturnValue($ret);
}
