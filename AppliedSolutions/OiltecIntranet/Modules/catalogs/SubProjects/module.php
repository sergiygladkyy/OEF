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
   
   if (null !== ($id = $model->getId()))
   {
      $attrs  = $model->toArray();
      
      $cmodel = Container::getInstance()->getCModel('documents.ProjectRegistration.tabulars', 'Subprojects');
      
      if (null === ($items = $cmodel->getEntities($id, array('attributes' => 'SubProject'))) || $items['errors'])
      {
         $errors[] = 'Databese error';
      }
      elseif (!empty($items))
      {
         $docs['ProjectRegistration'] = array();
         
         foreach ($items as $item)
         {
            $docs['ProjectRegistration'][] = $item['Owner'];
         }
         
         $links    = MGlobal::getDocumentLinks($docs);
         $errors[] = MGlobal::returnMessageByLinks($links, true, 'To change a project, you must change following documents:');
      }
   }
   
   if ($errors) $errors['Project'] = 'Invalid project';
   
   $event->setReturnValue($errors);
}
