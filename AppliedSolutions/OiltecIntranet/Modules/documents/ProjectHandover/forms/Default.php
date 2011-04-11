<?php

/**
 * Form default values
 * 
 * @param object $event
 * @return void
 */
function onBeforeOpening($event)
{
   /*$formName = $event['formName'];*/
   $options   = $event['options'];
   $container = Container::getInstance();
   $select    = array();
   
   $cmodel = $container->getCModel('catalogs', 'Counteragents');
   $crit   = 'WHERE `Parent`=0 AND `_folder`=1 AND `_deleted`=0 ORDER BY `Description`';
   
   if (null === ($groups = $cmodel->getEntities(null, array('criterion' => $crit, 'key' => '_id'))) || isset($groups['errors']))
   {
      throw new Exception('Database error');
   }
   
   if (!empty($groups))
   {
      $crit  = 'WHERE `Parent` IN ('.implode(',', array_keys($groups)).') AND `_folder`=0 AND `_deleted`=0 ORDER BY `Parent`, `Description`';
      
      if (null === ($items = $cmodel->getEntities(null, array('criterion' => $crit, 'key' => '_id'))) || isset($items['errors']))
      {
         throw new Exception('Database error');
      }
   }
   
   foreach ($groups as $gid => $group)
   {
      $gname = $group['Description'];
      
      $select[$gname] = array();
      
      $exec = true;
      
      while ($exec && list($id, $item) = each($items))
      {
         if ($item['Parent'] != $gid)
         {
            reset($items);
            
            $exec = false;
            
            continue;
         }
         
         $select[$gname][] = array('text' => $item['Description'], 'value' => $id);
      }
   }
   
   $event->setReturnValue(array(
      'attributes' => array(
         'Date' => date('Y-m-d H:i:s')
      ),
      'select' => array(
         'Customer' => $select
      )
   ));
}
