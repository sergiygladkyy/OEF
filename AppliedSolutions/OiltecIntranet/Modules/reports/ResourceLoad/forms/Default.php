<?php
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
   
   $event->setReturnValue(array(
      'select' => array(
         'PM' => MEmployees::getListOfPMForSelect()
      )
   ));
}
