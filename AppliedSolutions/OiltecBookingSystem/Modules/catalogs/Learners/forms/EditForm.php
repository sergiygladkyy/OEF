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
      'select' => array(
         'LearnersOrganization' => Learners::getOrganizationsForSelect()
      )
   ));
}
