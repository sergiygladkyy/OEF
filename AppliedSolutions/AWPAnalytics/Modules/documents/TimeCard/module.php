<?php 

/**
 * Called after standart validation, before saving tabular TimeRecords item
 * 
 * @param object $event
 * @return void
 */
function onBeforeAddingResourcesRecord($event)
{
   $model  = $event->getSubject();
   $attrs  = $model->toArray();
   $errors = array(); 
   
   $container = Container::getInstance();
   
   $ir = $container->getModel('information_registry', 'ProjectRegistrationRecords');
   
   if (!$ir->loadByDimensions(array('Project' => $attrs['Project'])))
   {
      $event->setReturnValue(array('Project' => 'Unknow project'));
      return;
   }

   if ($attrs['SubProject'] > 0)
   {
      $sub = $container->getModel('catalogs', 'SubProjects');
      
      if ($sub->load($attrs['SubProject']) && $attrs['Project'] != $sub->getAttribute('Project')->getId())
      {
         $errors['SubProject'] = 'Invalid SubProject';
      }
   }
   
   $event->setReturnValue($errors);
}

/**
 * Post document
 * 
 * @param object $event
 * @return void
 */
function onPost($event)
{
   $document  = $event->getSubject();
   $container = Container::getInstance();
   $kind = $document->getKind();
   $type = $document->getType();
   $id   = $document->getId();
   $doc  = $document->toArray();
   
   // Retrieve TimeRecords
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'TimeRecords');
   
   $criterion = "WHERE `Owner` = ".$id." ORDER BY `Date`";
   
   if (null === ($result = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   if (empty($result))
   {
      $event->setReturnValue(true);
      return;
   }
   
   $cmodel  = $container->getCModel('information_registry', 'Schedules');
   $irModel = $container->getModel('information_registry', 'TimeReportingRecords');
   $arModel = $container->getModel('AccumulationRegisters', 'EmployeeHoursReported');

   if (!$irModel->setRecorder($type, $id) || !$arModel->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   $arModel->setOperation('+');
   $arModel->setOption('auto_update_total', false);
   
   $dates = array();
   
   // Document attributes
   $employee = $doc['Employee'];
   $start = $doc['StartDate'];
   $end   = date('Y-m-d', strtotime($doc['EndDate'])+86400);
   
   // Retrieve employee params
   $eparams  = MEmployees::retrieveParametersInPeriod($employee, $start, $end);
   
   if (empty($eparams)) throw new Exception('Unknow employee');
   
   // Retrieve Variance Days
   $variance = MVacation::getScheduleVarianceDays($employee, $start, $end);
   
   foreach ($variance as $date => $vkind)
   {
      unset($eparams[$date]);
   }
   
   
   // Post document
   $errors = array();
   $pdepts = array();
   $pcheck = array();
   $pdate  = null;
   $phours = array('all' => 0);
   $arrecs = array();
   
   foreach ($result as $values)
   {
      // Check Vacation
      if (!isset($eparams[$values['Date']]) && $values['Hours'] != 0)
      {
         throw new Exception('Employee has vacation days in this period ('.getFormattedDate($values['Date']).')');
      }
      
      // Check Employee
      if (empty($eparams[$values['Date']]) && $values['Hours'] != 0)
      {
         throw new Exception('Employee was firing in ('.getFormattedDate($values['Date']).')');
      }
      
      $edep = $eparams[$values['Date']]['OrganizationalUnit'];
      
      // Check Project
      if (empty($pcheck[$values['Project']]))
      {
         if ($links = MProjects::isClose($values['Project'], date('Y-m-d')))
         {
            MGlobal::returnMessageByLinks($links);
         }
         else $pcheck[$values['Project']] = true;
      }
      
      // Retrieve project params
      if (!isset($pdepts[$values['Project']]))
      {
         $model = $container->getCModel('information_registry', 'ProjectRegistrationRecords');

         if (null === ($res = $model->getEntities($values['Project'], array('attributes' => 'Project'))) || isset($res['errors']))
         {
            throw new Exception('Database error');
         }
         elseif (empty($res[0]))
         {
            throw new Exception('Unknow project');
         }

         $pdepts[$values['Project']] = $res[0]['ProjectDepartment'];
      }
      
      $department = $pdepts[$values['Project']];
      
      
      // TimeReportingRecords
      $ir = clone $irModel;
      
      if (!$ir->setAttribute('Employee', $employee))               $err[] = 'Invalid value for Employee';
      if (!$ir->setAttribute('Project',  $values['Project']))      $err[] = 'Invalid value for Project';
      if (!$ir->setAttribute('Date',     $values['Date']))         $err[] = 'Invalid value for Period';
      if (!$ir->setAttribute('Hours',    $values['Hours']))        $err[] = 'Invalid value for Hours';
      if (!$ir->setAttribute('SubProject', $values['SubProject'])) $err[] = 'Invalid value for SubProject';
      if (!$ir->setAttribute('ProjectDepartment',  $department))   $err[] = 'Invalid value for ProjectDepartment';
      if (!$ir->setAttribute('EmployeeDepartment', $edep))         $err[] = 'Invalid value for EmployeeDepartment';
      if (!$ir->setAttribute('Comment', $values['Comment']))       $err[] = 'Invalid value for Comment';
      
      if (!$err)
      {
         if ($err = $ir->save())
         {
            throw new Exception('Can\'t add record in TimeReportingRecords');
         }
      }
      else throw new Exception('Invalid attributes for TimeReportingRecords');
      
      
      // EmployeeHoursReported
      $date  = strtotime($values['Date']);
      $dates[] = $values['Date'];
      
      if (!$pdate)
      {
         $pdate = $date;
      }
      elseif ($pdate != $date && !empty($arrecs))
      {
         // Calculate overtime
         self::calculateOvertime(&$arrecs, &$phours, $hours);
         
         $pdate = $date;
         
         // Add records
         self::addEmployeeHoursReported($arModel, $arrecs);
         
         $arrecs = array();
      }
      
      $hours = $eparams[$values['Date']]['WorkingHours'];
      $owertime = 0;
      
      if ($hours == 0)
      {
         $extra = $values['Hours'];
      }
      else
      {
         $extra = 0;
         
         $phours[$values['Project']] = $values['Hours'];
         $phours['all'] += $values['Hours'];
      }
      
      $arrecs[] = array(
         'Employee'           => $employee,
         'Project'            => $values['Project'],
         'EmployeeDepartment' => $edep,
         'Period'             => date('Y-m-d H:i:s', $date),
         'Hours'              => $values['Hours'],
         'OvertimeHours'      => $owertime,
         'ExtraHours'         => $extra
      );
      
      if ($hours == 0)
      {
         self::addEmployeeHoursReported($arModel, $arrecs);
         $arrecs = array();
      }
   }
   
   if (!empty($arrecs))
   {
      self::calculateOvertime(&$arrecs, &$phours, $hours);
      self::addEmployeeHoursReported($arModel, $arrecs);
   }
   
   // Calculate totals for EmployeeHoursReported
   if (!empty($dates) && $container->getCModel('AccumulationRegisters', 'EmployeeHoursReported')->countTotals($dates))
   {
      throw new Exception('Can\'t recount totals for EmployeeHoursReported');
   }
   
   $event->setReturnValue(true);
}

/**
 * Clear posting
 * 
 * @param object $event
 * @return void
 */
function onUnpost($event)
{
   $document  = $event->getSubject();
   $container = Container::getInstance();
   
   $irModel = $container->getCModel('information_registry', 'TimeReportingRecords');
   $arModel = $container->getCModel('AccumulationRegisters', 'EmployeeHoursReported');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   $iRes = $irModel->delete(true, $options);
   $aRes = $arModel->delete(true, $options);
   
   $return = (empty($iRes) && empty($aRes)) ? true : false;
   
   $event->setReturnValue($return);
}


/**
 * Add records in EmployeeHoursReported DB
 * 
 * @param object $model  - model class
 * @param array $records - rows
 * @return void
 */
function addEmployeeHoursReported(&$model, &$records)
{
   foreach ($records as $record)
   {
      $ar = clone $model;

      if (!$ar->setAttribute('Employee',           $record['Employee']))           $err[] = 'Invalid value for Employee';
      if (!$ar->setAttribute('Project',            $record['Project']))            $err[] = 'Invalid value for Project';
      if (!$ar->setAttribute('EmployeeDepartment', $record['EmployeeDepartment'])) $err[] = 'Invalid value for EmployeeDepartment';
      if (!$ar->setAttribute('Period',             $record['Period']))             $err[] = 'Invalid value for attribute Period';
      if (!$ar->setAttribute('Hours',              $record['Hours']))              $err[] = 'Invalid value for Hours';
      if (!$ar->setAttribute('OvertimeHours',      $record['OvertimeHours']))      $err[] = 'Invalid value for OvertimeHours';
      if (!$ar->setAttribute('ExtraHours',         $record['ExtraHours']))         $err[] = 'Invalid value for ExtraHours';

      if (!$err)
      {
         if ($err = $ar->save())
         {
            throw new Exception('Can\'t add record in EmployeeHoursReported');
         }
      }
      else throw new Exception('Invalid attributes for EmployeeHoursReported');
   }
}

/**
 * Calculate overtime
 * 
 * @param array& $arrecs - rows
 * @param array& $phours - project/hours
 * @param float $hours   - working hours
 * @return void
 */
function calculateOvertime(&$arrecs, &$phours, $hours)
{
   if ($phours['all'] > $hours)
   {
      if (($cnt = count($phours)) == 2)
      {
         $arrecs[0]['OvertimeHours'] = $phours['all'] - $hours;
      }
      else
      {
         $sum  = 0;
         $max  = 0;
         $ower = $phours['all'] - $hours;
         $mkey = array();
         next($phours);
          
         for ($i = 2; $i < $cnt; $i++)
         {
            list($project, $Hpi) = each($phours);
            list($key, $record)  = each($arrecs);
            
            $cower = round($ower*$Hpi/$phours['all']);
            if ($cower == 0)
            {
               if ($max < $Hpi)
               {
                  $max  = $Hpi;
                  $mkey = array($key);
               }
               elseif ($max == $Hpi)
               {
                  $mkey[] = $key;
               }
            }
            else $sum += $cower;
            
            $arrecs[$key]['OvertimeHours'] = $cower;
         }
         
         list($key, $record) = each($arrecs);
         
         if ($sum == 0)
         {
            list($project, $Hpi) = each($phours);
            
            if ($Hpi > $max)
            {
               $arrecs[$key]['OvertimeHours'] = $ower;
            }
            else
            {
               if ($Hpi == $max) $mkey[] = $key;
               
               if (1 === ($cnt = count($mkey)))
               {
                  $arrecs[$mkey[0]]['OvertimeHours'] = $ower;
               }
               else
               {
                  $cower = ceil($ower/$cnt);
                  
                  foreach ($mkey as $key)
                  {
                     $ower -= $cower;
                     $arrecs[$key]['OvertimeHours'] = ($ower < 0) ? $cower+$ower : $cower;
                     
                     if ($ower <= 0) break;
                  }
               }
            }
         }
         else $arrecs[$key]['OvertimeHours'] = $ower - $sum;
      }
   }
   
   $phours = array('all' => 0);
}