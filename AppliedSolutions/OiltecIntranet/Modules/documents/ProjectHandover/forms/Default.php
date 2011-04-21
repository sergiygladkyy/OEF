<?php

/**
 * Form default values
 * 
 * @param object $event
 * @return void
 */
function onBeforeOpening($event)
{
   $formName = $event['formName'];
   $options  = $event['options'];
   
   $event->setReturnValue(array(
      'attributes' => array(
         'Date' => date('Y-m-d H:i:s')
      ),
      'select' => array(
         'Customer' => MGlobal::getCustomersForSelect($options)
      )
   ));
}
