<?php
function getmicrotime() 
{ 
    list($usec, $sec) = explode(" ", microtime()); 
    return ((float)$usec + (float)$sec); 
} 

require_once('lib/utility/Loader.php');
require_once('lib/container/Container.php');
require_once('lib/persistent/PersistentLayer.php');
require_once('lib/utility/Utility.php');
require_once('lib/model/catalogs/CatalogModel.php');
require_once('lib/model/catalogs/CatalogsModel.php');
require_once('lib/model/documents/DocumentModel.php');
require_once('lib/model/information_registry/InfRegistryModel.php');


$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action)
{
   case 'install':
      
      $perLay = PersistentLayer::getInstance();
      $errors = $perLay->install();
      $msg = 'Install succesfully';
      
      break;
      
   case 'remove':
      
      $perLay = PersistentLayer::getInstance();
      $errors = $perLay->remove();
      $msg = 'Remove succesfully';
      
      break;
   
   case 'update':
      
      require_once('config/init.php');
      $container = Container::createInstance();
      
      $container->getModulesManager()->clearCache();
      $msg = 'Update succesfully';
      
      break;
  /*    
   case 'add':
      
      $container = Container::createInstance();
      /*var_dump($container->getModel('catalogs', 'employee'));
      var_dump($container->getModel('catalogs', 'employee'));*/
      /*$office = new CatalogModel('office');
      $ret = $office->fromArray(array(
         'description' => 'odessa_office',
         'name'    => 'office_1',
         'address' => 'odessa'
      ));
      echo '<pre>'; print_r($office->save()); echo '</pre><hr />';
      
      $office = new CatalogModel('office');
      $ret = $office->fromArray(array(
         'description' => 'norway_office',
         'name'    => 'office_2',
         'address' => 'norway'
      ));
      echo '<pre>'; print_r($office->save()); echo '</pre><hr />';
      
      $entity = new CatalogModel('employee');
      $ret = $entity->fromArray(array(
         'description' => 'description_1',
         'name'    => 'name_1',
         'surname' => 'surname_1',
         'office1' => 1
      ));
      echo '<pre>'; print_r($entity->save()); echo '</pre><hr />';
      
      $entity = new CatalogModel('employee');
      $ret = $entity->fromArray(array(
         'description' => 'description_2',
         'name'    => 'name_2',
         'surname' => 'surname_2',
         'office1' => 2
      ));
      echo '<pre>'; print_r($entity->save()); echo '</pre><hr />';
      
      $entity->load(2);*/
      /*
      $entity = $container->getModel('catalogs.employee.tabulars' ,'employee_ts');
      $ret = $entity->fromArray(array(
         'owner'     => 1,
         'name'      => 'name_1',
         'locations' => 1
      ));
      echo '<pre>'; print_r($entity->save()); echo '</pre><hr />';
      $ret = $entity->fromArray(array(
         'owner'     => 2,
         'name'      => 'name_2',
         'locations' => 2
      ));
      echo '<pre>'; print_r($entity->save()); echo '</pre><hr />';
      */
      //$entity->setAttribute('name', 'ccc');
      //echo $entity->getKind().'<hr>';
      //$entity_2 = new DocumentModel('test'/*'project'*/);
      //echo $entity->getKind().'<hr>'.$entity_2->getKind().'<hr>';
      
      //echo '<pre>'; print_r($entity->toArray()); echo '</pre><hr />';
      /*echo '<pre>'; print_r($entity->delete()); echo '</pre><hr />';
      echo '<pre>'; print_r($entity->save()); echo '</pre><hr />';*/
      //var_dump($entity);
      
      //echo '<pre>'; print_r(BaseObjectsModel::markAsRemoved('catalogs', 'office', 1)); echo '</pre><hr />';
      //$empls = CatalogsModel::getInstance('office');
      //var_dump($empls->delete(1));
      //echo '<hr>';
      //echo '<pre>'; print_r($empls->getEntities()); echo '</pre><hr />';
     
      /*$ir = new InfRegistryModel('project_registry');
      $ir->fromArray(array(
         'project'  => 1,
         'employee' => 7,
         'period'   => '2010-10-10 00:00:00',
         'load' => 25
      ));
      var_dump($ir->save());*/
      //$tabular = $container->getModel('catalogs.test.tabulars', 'tabular_2');
      //var_dump($tabular);
      
      
      /*$controller = $container->getController('catalogs', 'employee');
      echo '<pre>'.print_r($controller->displayListForm(), true).'</pre><hr />';
      echo '<pre>'.print_r($container->getConfigManager()->getInternalConfigurationByKind('catalogs.field_type', 'employee'), true).'</pre><hr />';
      break;*/
}

?>
<html>
<head>
   <title>Persistent Layer</title>
</head>
<body>
   <?php if (!empty($errors) && is_array($errors)): ?>
   <div class="errors">
      <?php echo '<pre>'; print_r($errors); echo '</pre><hr />';//foreach ($errors as $error): ?>
      <?php //echo $error; ?><br/>
      <?php //endforeach; ?>
   </div>
   <?php else: ?>
   <div class="msg">
      <h3><?php echo $msg; ?></h3>
   </div>
   <?php endif;?>
   <?php if (PersistentLayer::getInstance()->isInstalled()): ?>
      <input type="button" name="emove" value="Remove" onclick="location='index.php?action=remove';" />&nbsp;
      <input type="button" name="update_modules" value="Update Modules" onclick="location='index.php?action=update';" /><!-- <br />
      <input type="button" name="add_entity" value="Add Entity" onclick="location='index.php?action=add';" />-->
   <?php else: ?>
      <input type="button" name="install" value="install" onclick="location='index.php?action=install';" />
   <?php endif; ?>
</body>
</html>
