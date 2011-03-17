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
   
   $errors    = array();
   $container = Container::getInstance();
   
   /* Create document */
   $doc_date = date('Y-m-d H:i:s');
   $document = $container->getModel('documents', 'ProjectTimeRecorder');
   $params   = array('Date' => $doc_date);
   if (!$err = $document->fromArray($params)) $err = $document->save();
   
   if (empty($err))
   {
      $have_doc = true;
      $tabular = $container->getModel('documents.ProjectTimeRecorder.tabulars', 'Records');
      $tabular->setAttribute('Owner', $document->getId());
   }
   else
   {
      $have_doc = false;
      $errors   = "Document not created.";
   }
   
   /* Fill catalogs and document tabular section */
      
   $rejected   = $container->getModel('information_registry', 'RejectedImportRecords');
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
      $rejectReason   = array();
      
      
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
            $add_in_tabular      = false;
            $empl_dump[$data[4]] = 0;
            $rejectReason        = array_merge($rejectReas, $err);
            
            $errors[] = "Employee not added: Code - '".$data[4]."', Name - '".$data[5]."'.";
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
            $add_in_tabular      = false;
            $area_dump[$data[6]] = 0;
            $rejectReason        = array_merge($rejectReas, $err);
            
            $errors[] = "Business Area not added: Code - '".$data[6]."'.";
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
            $add_in_tabular      = false;
            $proj_dump[$data[0]] = 0;
            $rejectReason        = array_merge($rejectReas, $err);
            
            $errors[] = "Project not added: Code - '".$data[0]."', Name - '".$data[1]."'.";;
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
               $add_in_tabular = false;
               $rejectReason[] = 'Parent project not exists.';
               
               $errors[] = "SubProject not added: Code - '".$data[2]."', Name - '".$data[3]."', Project - '".$data[0]."'.";
            }
            else
            {
               $params = array(
                  'Code'        => $data[2],
                  'Description' => $data[3],
                  'Project'     => $proj_dump[$data[0]]
               );

               if (!$err = $entity->fromArray($params, array('replace' => true))) $err = $entity->save();

               if (!empty($err))
               {
                  $add_in_tabular      = false;
                  $subp_dump[$data[2]] = 0;
                  $rejectReason        = array_merge($rejectReas, $err);
                  
                  $errors[] = "SubProject not added: Code - '".$data[2]."', Name - '".$data[3]."', Project - '".$data[0]."'.";
               }
               else $subp_dump[$data[2]] = $entity->getId();
            }
         }
         elseif (empty($subp_dump[$data[2]]))
         {
            $add_in_tabular = false;
         }
      }
      
      
      /* Add current row in tabular section */
      
      if (!$add_in_tabular || !$have_doc)
      {
         $add_in_rejected = true;
         
         if (!$have_doc)
         {
            $rejectReason[] = 'Document not created.'; 
         }
      }
      else
      {
         $add_in_rejected = false;
      
         $params = array(
            'Project'      => $proj_dump[$data[0]],
            'SubProject'   => empty($data[2]) ? 0 : $subp_dump[$data[2]],
            'Employee'     => $empl_dump[$data[4]],
            'BusinessArea' => $area_dump[$data[6]],
            'HoursSpent'   => str_replace(',', '.', $data[7])
         );
         $entity = clone $tabular;

         if (!$err = $entity->fromArray($params)) $err = $entity->save();

         if (!empty($err))
         {
            $add_in_rejected = true;
            $rejectReason    = $err;
            
            $errors[] = 'Row not added in tabular section.';
         }
      }
      
      
      /* Add in Rejected */
      
      if ($add_in_rejected)
      {
         $ret = true;
         $ir  = clone $rejected;
         
         $ret = $ret && $ir->setAttribute('Project', $data[0]);
         $ret = $ret && $ir->setAttribute('SubProject', $data[2]);
         $ret = $ret && $ir->setAttribute('Employee', $data[4]);
         $ret = $ret && $ir->setAttribute('BusinessArea', $data[6]);
         $ret = $ret && $ir->setAttribute('HoursSpent', str_replace(',', '.', $data[7]));
         $ret = $ret && $ir->setAttribute('DocumentType', '');
         $ret = $ret && $ir->setAttribute('DocumentNumber', 0);
         $ret = $ret && $ir->setAttribute('Date', $doc_date);
         $ret = $ret && $ir->setAttribute('RejectReason', implode('; ', $rejectReason).'.');

         if ($ret)
         {
            if ($err = $ir->save()) $ret = false;
         }
         else $err[] = 'Some values have a wrong type';

         if (!$ret) $errors[] = 'RejectedImportRecord not added.';
      }
   }
   
   fclose($handle);
   
   if ($have_doc)
   {
      if ($err = $document->post()) $errors[] = 'Document not posted.';
   } 
   
   if (!empty($errors)) throw new Exception(implode('<br/>', $errors));
   
   $event->setReturnValue(true);
}
?>