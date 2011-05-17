<?php

/**
 * Set default values for edit form
 * 
 * @param object $event
 * @return void
 */
function onBeforeOpening($event)
{
   $formName = $event['formName'];
   $options  = $event['options'];
   
   $start = (int) date('Y') - 1;
   
   for ($year = $start; $year < $start + 3; $year++)
   {
      for ($month = 1; $month < 13; $month++)
      {
         $date = mktime(23,59,59, $month + 1, 0, $year);
         $dates[] = array(
            'value' => date('Y-m-d H:i:s', $date),
            'text'  => strftime('%B %Y', $date)
         );
      }
   }
   
   $event->setReturnValue(array(
      'attributes' => array(
         'Date' => date('Y-m-d H:i:s', mktime(23,59,59, ((int) date('m') + 1), 0, date('Y')))
      ),
      'select' => array(
         'Date' => $dates
      )
   ));
}
