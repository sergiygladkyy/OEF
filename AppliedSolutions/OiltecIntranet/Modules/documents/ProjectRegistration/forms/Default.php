<?php 

/**
 * Process update request
 * 
 * @param object $event
 * @return void
 */
function onFormUpdateRequest($event)
{
   $subject   = $event->getSubject();
   $kind      = $subject->getKind();
   $type      = $subject->getType();
   $formData  = $event['formData'];
  //$formName = $event['formName'];
//$parameters = $event['parameters'];
   $attrs    =& $formData['aeform'][$kind][$type]['attributes'];
   $tabulars =& $formData['aeform'][$kind][$type]['tabulars'];
   
   $container = Container::getInstance();
   
   // Clean tabular sections
   if (!empty($attrs['_id']))
   {
      $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Subprojects');
      
      if ($cmodel->delete($attrs['_id'], array('attributes' => 'Owner')))
      {
         throw new Exception('Database error');
      }
      
      $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Milestones');
      
      if ($cmodel->delete($attrs['_id'], array('attributes' => 'Owner')))
      {
         throw new Exception('Database error');
      }
   }
   
   // Retrieve update data
   if (empty($attrs['Project']))
   {
      throw new Exception('Unknow Project');
   }
   
   $project = $attrs['Project'];
   
   $sp['select']['SubProject'] = self::getSubProjectsForSelect($project);
   array_unshift($sp['select']['SubProject'], array('value' => 'new', 'text' => ''));
   
   $cmodel  = $container->getCModel('catalogs', 'SubProjects');
   
   if (null === ($res = $cmodel->getEntities($project, array('attributes' => 'Project'))))
   {
      throw new Exception('Database error');
   }
   
   $sp['items'] = array();
   
   foreach ($res as $row)
   {
      $sp['items'][] = array('SubProject' => $row['_id']);
   }
   
   $event->setReturnValue(array(
      'type' => 'array',
      'data' => array(
         "$kind" => array(
            "$type" => array(
               'tabulars' => array(
                  'Subprojects' => $sp,
                  'Milestones'  => array(
                     'items' => array()
                  )
               )
            )
         )
      )
   ));
}

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
   $crit      = array();
   $select    = array();
   
   if (!empty($options['id']))
   {
      $model = $container->getModel('documents', 'ProjectRegistration');
      
      if ($model->load($options['id']) && ($pmID = $model->getAttribute('ProjectManager')))
      {
         $crit[]  = 'empl.`_id` = '.$pmID->getId();
         
         if ($project = $model->getAttribute('Project'))
         {
            $select = array_merge($select, self::getSubProjectsForSelect($project->getId()));
         }
      }
   }
   
   if ($pmPos = Constants::get('ProjectManagerPosition'))
   {
      $crit = "ir.OrganizationalPosition = ".$pmPos;
   }
   
   $pms = array();
   
   if (!empty($crit))
   {
      $odb   = $container->getODBManager();
      $query = "SELECT ir.Employee AS `value`, empl.Description AS `text` ".
               "FROM catalogs.Employees AS `empl`, information_registry.StaffHistoricalRecords AS `ir` ".
               "WHERE empl.NowEmployed = 1 AND (".implode(' OR ', $crit).") AND empl._id = ir.Employee ".
               "ORDER BY empl.Description";
       
      if (null === ($pms = $odb->loadAssocList($query)))
      {
         throw new Exception('Database error');
      }
   }
   
   $event->setReturnValue(array(
      'attributes' => array(
         'Date'      => date('Y-m-d'),
         'StartDate' => date('Y-m-d')
      ),
      'select' => array(
         'ProjectManager' => $pms 
      ),
      'tabulars' => array(
         'Subprojects' => array(
            'select' => array(
               'SubProject' => $select
            )
         )
      )
   ));
}


/**
 * Return list of SubProjects by project id
 * 
 * @param $project - id
 * @return array
 */
function getSubProjectsForSelect($project)
{
   $odb   = Container::getInstance()->getODBManager();
   $query = "SELECT `_id`, `Description` FROM catalogs.SubProjects WHERE `Project` = ".$project." AND `_deleted`=0 ORDER BY `Description` ASC";
   
   if (null === ($res = $odb->executeQuery($query)))
   {
      throw new Exception('Database error');
   }
   
   $SubProjects = array();
   
   while ($row = $odb->fetchRow($res))
   {
      $SubProjects[] = array('value' => $row[0], 'text' => $row[1]);
   }
   
   return $SubProjects;
}