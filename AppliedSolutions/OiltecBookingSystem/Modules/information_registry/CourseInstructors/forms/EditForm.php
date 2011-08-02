<?php 

/**
 * Form default values
 * 
 * @param object $event
 * @return void
 */
function onBeforeOpening($event)
{
   $options = $event['options'];
   
   $event->setReturnValue(array(
      'attributes' => array(
         'Date' => date('Y-m-d')
      ),
      'select' => array(
         'InstructorsOrganization' => Instructors::getOrganizationsForSelect()
      )
   ));
}
