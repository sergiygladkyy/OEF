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
   $formData  = $formData['aeform'][$kind][$type]['attributes'];
   $container = Container::getInstance();
   
   if (empty($formData['Project']))
   {
      throw new Exception('Unknow Project');
   }
   
   $project = $formData['Project'];
   $cmodel  = $container->getCModel('catalogs', 'SubProjects');
   
   if (null === ($res = $cmodel->getEntities($project, array('attributes' => 'Project'))))
   {
      throw new Exception('Database error');
   }
   $subprojects = array();
   
   foreach ($res as $row)
   {
      $subprojects[] = array('SubProject' => $row['_id']);
   }
   
   $event->setReturnValue(array(
      'type' => 'array',
      'data' => array(
         "$kind" => array(
            "$type" => array(
               'tabulars' => array(
                  'Subprojects' => array(
                     'items' => $subprojects
                  ),
                  'Milestones' => array(
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
   /*$formName = $event['formName'];
   $options  = $event['options'];*/
   
   $container = Container::getInstance();
   
   $odb   = $container->getODBManager(); 
   $query = "SELECT ir.Employee AS `value`, empl.Description AS `text` ".
            "FROM catalogs.Employees AS `empl`, information_registry.StaffHistoricalRecords AS `ir` ".
            "WHERE empl.NowEmployed = 1 AND empl._id = ir.Employee AND ir.OrganizationalPosition = ".Constants::get('ProjectManagerPosition')." ".
            "ORDER BY empl.Description";
   
   if (null === ($pms = $odb->loadAssocList($query)))
   {
      throw new Exception('Database error');
   }
   
   $event->setReturnValue(array(
      'attributes' => array(
         'Date'      => date('Y-m-d'),
         'StartDate' => date('Y-m-d')
      ),
      'select' => array(
         'ProjectManager' => $pms
      )
   ));
}
