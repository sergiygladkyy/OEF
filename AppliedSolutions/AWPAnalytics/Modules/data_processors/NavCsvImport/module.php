<?php

/**
 * Data import
 * 
 * ftp://user:password@example.com/somefile.txt
 * 
 * @param object $event
 * @return void
 */
function onImport($event)
{  
   extract($event['headline'], EXTR_OVERWRITE);
   
   $filepath = 'ftp://'.$UserName.':'.$Password.'@'.$Server.$FileName;
   
   $handle = fopen($filepath, "r");
   
   if (!$handle || ($head = fgetcsv($handle, 1000, ';')) === false)
   {
      throw new Exception('File not found');
   }
   
   $container = Container::getInstance();
   
   /* Create document */
   
   $document = $container->getModel('documents', 'ProjectTimeRecorder');
   $params   = array('Date' => date('Y-m-d H:i:s'));
   if (!$err = $document->fromArray($params)) $err = $document->save();
   
   if (empty($err))
   {
      $have_doc = true;
      $tabular = $container->getModel('documents.ProjectTimeRecorder.tabulars', 'Records');
      $tabular->setAttribute('Owner', $document->getId());
   }
   else $have_doc = false;
   
   /* Fill catalogs and document tabular section */
   
   $errors     = array();
   $employee   = $container->getModel('catalogs', 'Employees');
   $project    = $container->getModel('catalogs', 'Projects');
   $subproject = $container->getModel('catalogs', 'SubProjects');
   $b_area     = $container->getModel('catalogs', 'BusinessAreas');
   
   $dump = array(
      'catalogs' => array('Employees' => array()),
      'catalogs' => array('Projects'  => array()),
      'catalogs' => array('SubProjects'  => array()),
      'catalogs' => array('BusinessAreas' => array())
   );
   $empl_dump =& $dump['catalogs']['Employees'];
   $proj_dump =& $dump['catalogs']['Projects'];
   $subp_dump =& $dump['catalogs']['SubProjects'];
   $area_dump =& $dump['catalogs']['BusinessAreas'];
   
   while (($data = fgetcsv($handle, 1000, ';')) !== FALSE)
   {
      $row++;
      
      $add_in_tabular = true;
      
      
      /* catalogs.Employees */
      
      if (!isset($empl_dump[$data[4]]))
      {
         $params = array(
            'Code'        => $data[4],
            'Description' => $data[5]
         );
         $entity = clone $employee;
         if (preg_match_all('/[^\s]+/i', $data[5], $names))
         {
            $names = $names[0];
         }
         $cnt = count($names);
         switch ($cnt)
         {
            case 1:
               $params['Surname'] = $names[0];
               break;
                
            case 2:
               $params['Name'] = $names[0];
               $params['Surname'] = $names[1];
               break;
                
            case 3:
               $params['Name'] = $names[0];
               $params['MiddleName'] = $names[1];
               $params['Surname'] = $names[2];
               break;
            
            default:
               $params['Name'] = $names[0];
               $params['Surname'] = $names[$cnt - 1];
               unset($names[0], $names[$cnt - 1]);
               $params['MiddleName'] = implode(' ', $names);
         }

         if (!$err = $entity->fromArray($params, array('replace' => true))) $err = $entity->save();

         if (!empty($err))
         {
            $add_in_tabular = false;
            $empl_dump[$data[4]] = 0;
            $errors[$row]['catalogs'][$entity->getType()] = $err;
            $errors[$row]['catalogs'][$entity->getType()][] = 'Name: '.$data[5];
         }
         else $empl_dump[$data[4]] = $entity->getId();
      }
      elseif (empty($empl_dump[$data[4]]))
      {
         $add_in_tabular = false;
      }
      
      
      /* catalogs.BusinessArea */
      
      if (!isset($area_dump[$data[6]]))
      {
         $params = array(
            'Code'        => $data[6],
            'Description' => $data[6]
         );
         $entity = clone $b_area;

         if (!$err = $entity->fromArray($params, array('replace' => true))) $err = $entity->save();

         if (!empty($err))
         {
            $add_in_tabular = false;
            $area_dump[$data[6]] = 0;
            $errors[$row]['catalogs'][$entity->getType()] = $err;

         }
         else $area_dump[$data[6]] = $entity->getId();
      }
      elseif (empty($area_dump[$data[6]]))
      {
         $add_in_tabular = false;
      }
      
      
      /* catalogs.Projects */
      
      if (!isset($proj_dump[$data[0]]))
      {
         $params = array(
            'Code'        => $data[0],
            'Description' => $data[1]
         );
         $entity = clone $project;

         if (!$err = $entity->fromArray($params, array('replace' => true))) $err = $entity->save();

         if (!empty($err))
         {
            $add_in_tabular = false;
            $proj_dump[$data[0]] = 0;
            $errors[$row]['catalogs'][$entity->getType()] = $err;
         }
         else $proj_dump[$data[0]] = $entity->getId();
      }
      elseif (empty($proj_dump[$data[0]]))
      {
         $add_in_tabular = false;
      }
      
      
      /* catalogs.Subprojects */
      
      if (!empty($data[2]))
      {
         if (!isset($subp_dump[$data[2]]))
         {
            $entity = clone $subproject;
             
            if (empty($proj_dump[$data[0]]))
            {
               $errors[$row]['catalogs'][$entity->getType()][] = 'Parent project not exists';
               continue;
            }
             
            $params = array(
               'Code'        => $data[2],
               'Description' => $data[3],
               'Project'     => $proj_dump[$data[0]]
            );

            if (!$err = $entity->fromArray($params, array('replace' => true))) $err = $entity->save();

            if (!empty($err))
            {
               $subp_dump[$data[2]] = 0;
               $errors[$row]['catalogs'][$entity->getType()] = $err;
               continue;
            }
            else $subp_dump[$data[2]] = $entity->getId();
         }
         elseif (empty($subp_dump[$data[2]]))
         {
            $add_in_tabular = false;
         }
      }
      
      
      /* Add current row in tabular section */
      
      if (!$have_doc || !$add_in_tabular) continue;
      
      $params = array(
         'Project'      => $proj_dump[$data[0]],
         'SubProject'   => empty($data[2]) ? 0 : $subp_dump[$data[2]],
         'Employee'     => $empl_dump[$data[4]],
         'BusinessArea' => $area_dump[$data[6]],
         'HoursSpent'   => str_replace(',', '.', $data[7])
      );
      $entity = clone $tabular;

      if (!$err = $entity->fromArray($params)) $err = $entity->save();

      if (!empty($err)) $errors[$row][$entity->getKind()][$entity->getType()] = $err;
   }
   
   fclose($handle);
   
   if (!empty($errors)) throw new Exception('<pre>'.print_r($errors, true).'</pre>');
   
   $event->setReturnValue(true);
}
?>