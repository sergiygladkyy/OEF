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
   
   $cmodel = $container->getCModel('catalogs', 'Projects');
   
   $list = $cmodel->getEntities();

   if (is_null($list)) throw new Exception('Internal model error');
   
   return $list;
}
?>