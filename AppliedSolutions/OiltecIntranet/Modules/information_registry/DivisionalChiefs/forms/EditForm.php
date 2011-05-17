<?php 

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
   
   $employees = MEmployees::getListOfDivisionalChiefsForSelect();
   
   $event->setReturnValue(array(
      'select' => array(
         'Employee' => array_values($employees),
      )
   ));
}
