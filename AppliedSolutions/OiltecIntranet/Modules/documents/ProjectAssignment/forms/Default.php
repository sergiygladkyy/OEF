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
   $attrs     =& $formData['aeform'][$kind][$type]['attributes'];
   $tabulars  =& $formData['aeform'][$kind][$type]['tabulars'];
   $errors    = array();
   $container = Container::getInstance();
   
   // Retrieve update data
   if (empty($attrs['Project']))
   {
      $errors['Project'] = 'Project is required';
      $subprojects = array();
   }
   else $subprojects = MProjects::getRegisteredSubProjectForSelect($attrs['Project']);
   
   $event->setReturnValue(array(
      'type' => 'array',
      'data' => array(
         "$kind" => array(
            "$type" => array(
               'attributes' => $attrs,
               'errors' => $errors,
               'tabulars' => array(
                  'Resources' => array(
                     'items'  => empty($tabulars['Resources']) ? array() : $tabulars['Resources'],
                     'select' => array(
                        'SubProject' => $subprojects
                     )
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
 //$formName = $event['formName'];
   $options  = $event['options'];
   
   $employees = MEmployees::getNowWorksForSelect();
   $projects  = MProjects::getRegisteredProjectsForSelect();
   $subprojs  = array();
   
   if (!empty($options['id']))
   {
      $doc = Container::getInstance()->getModel('documents', 'ProjectAssignment');
      
      if ($doc->load($options['id']))
      {
         $subprojs = MProjects::getRegisteredSubProjectForSelect($doc->getAttribute('Project')->getId());
      }
   }
   
   $event->setReturnValue(array(
      'attributes' => array(
         'Date' => date('Y-m-d H:i:s')
      ),
      'select' => array(
         'Project' => $projects
      ),
      'tabulars' => array(
         'Resources' => array(
            'select' => array(
               'Employee' => $employees,
               'SubProject' => $subprojs
            )
         )
      )
   ));
}
