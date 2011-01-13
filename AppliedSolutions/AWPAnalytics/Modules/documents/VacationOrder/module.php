<?php

/**
 * Called after standart validation, before saving tabular Employees item
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingEmployeesRecord($event)
{
   $model = $event->getSubject();
   $attrs = $model->toArray();

   $errors = MVacation::checkVacationItem($attrs);
   
   $event->setReturnValue($errors);
}
