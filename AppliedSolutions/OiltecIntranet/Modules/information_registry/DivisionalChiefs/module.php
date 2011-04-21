<?php 

/**
 * Called after standart validation, before saving item
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingRecord($event)
{
   $model  = $event->getSubject();
   $errors = array();
   
   if (!($employee = $model->getAttribute('Employee')))
   {
      $errors[] = 'Unknow Employee';
   }
   elseif (!($hist = MEmployees::getLastHistoricalRecord($employee->getId())))
   {
      $errors[] = 'Invalid Employee';
   }
   elseif ($hist['OrganizationalPosition'] != Constants::get('DivisionalChiefPosition'))
   {
      $errors[] = 'This employee can\'t be head of department';
   }

   $event->setReturnValue($errors);
}
