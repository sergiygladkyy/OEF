<?php
/**
 * Web-service action "getProjectList"
 * 
 * @param string $attributes
 * @return array
 */
function getProjectList(array $attributes)
{
   $container = Container::getInstance();
   
   $model = $container->getModel('catalogs', 'Projects');
   
   $list = $model->getEntities();

   if (is_null($list)) throw new Exception('Internal model error');
   
   return $list;
}
?>