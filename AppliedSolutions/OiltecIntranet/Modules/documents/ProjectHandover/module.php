<?php 

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
   
   $pReg = $container->getModel('information_registry', 'ProjectHandoverRecords');
   
   // Post document
   $errors = array();
   $values = $document->toArray();
   unset(
      $values['_id'],
      $values['Code'],
      $values['Date'],
      $values['_deleted'],
      $values['_post']
   );
   
   if ($err = $pReg->fromArray($values))
   {
      foreach ($err as $attr => $err)
      {
         $errors[] = 'Invalid value for '.$attr;
      }
   }
   
   if (!$pReg->setRecorder($type, $id))
   {
      throw new Exception('Invalid recorder');
   }
   
   if (!$errors && $err = $pReg->save())
   {
      $errors = array('Row not added');
   }
   
   if ($errors) throw new Exception(implode('<br>', $errors));
   
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
   
   $pModel = $container->getCModel('information_registry', 'ProjectHandoverRecords');
   
   $options = array(
      'attributes' => array('%recorder_type', '%recorder_id'),
      'criterion'  => "`%recorder_type`='".$document->getType()."' AND `%recorder_id`=".$document->getId()
   );
   
   
   $pRes = $pModel->delete(true, $options);
   
   $return = empty($pRes) ? true : false;
   
   $event->setReturnValue($return);
}

/**
 * Print document
 * 
 * @param object $event
 * @return void
 */
function onPrint($event)
{
   $model = $event->getSubject();
   $attrs = $model->toArray(array('with_link_desc' => true));
   $kind  = $model->getKind();
   $type  = $model->getType();
   $id    = $model->getId();
   
   $container = Container::getInstance();
   
   // Retrieve Misc
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Misc');
   
   if (null === ($misc = $cmodel->getEntities($id, array('attributes' => 'Owner'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   // Retrieve Conditions
   $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Conditions');
   
   if (null === ($cond = $cmodel->getEntities($id, array('attributes' => 'Owner'))) || isset($result['errors']))
   {
      throw new Exception('Database error');
   }
   
   
   /* Generate print form */
   
   $mockup = new Mockup(self::$layout_dir.'ProjectHandover.php');
   $print  = new TabularDoc();
   
   $print->addCSS($mockup->getCSS());
   $print->setGridAttributes($mockup->getGridAttributes());
   
   
   $area = $mockup->getArea('C1.R1:C22.R73');
   $area->parameters['current_date'] = date('d F Y');
   
   $yes = 'X';
   $no  = 'X';
   
   // Project information
   $area->parameters['SalesManager']           = empty($attrs['SalesManager'])     ? ' ' : $attrs['SalesManager']['text'];
   $area->parameters['ProjectManager']         = empty($attrs['ProjectManager'])   ? ' ' : $attrs['ProjectManager']['text'];
   $area->parameters['TenderResonsible']       = empty($attrs['TenderResonsible']) ? ' ' : $attrs['TenderResonsible']['text'];
   $area->parameters['MainProject']            = empty($attrs['MainProject'])      ? ' ' : $attrs['MainProject']['text'];
   $area->parameters['ProjectCode']            = $attrs['ProjectCode'];
   $area->parameters['ProjectName']            = $attrs['ProjectName'];
   $area->parameters['Contract']               = empty($attrs['Contract']) ? ' ' : $attrs['Contract']['text'];
   $area->parameters['Customer']               = empty($attrs['Customer']) ? ' ' : $attrs['Customer']['text'];
   $area->parameters['CustomerMainContact']    = $attrs['CustomerMainContact'];
   $area->parameters['SelligPrice']            = $attrs['SelligPrice'];
   $area->parameters['MaterialsCost']          = $attrs['MaterialsCost'];
   $area->parameters['TotalIndirectLaborCost'] = $attrs['TotalIndirectLaborCost'];
   $area->parameters['NumberOfHours']          = $attrs['NumberOfHours'];
   $area->parameters['GrossMargin']            = $attrs['GrossMargin'];
   $area->parameters['AddedValuePerHour']      = $attrs['AddedValuePerHour'];
   $area->parameters['EstimatedStartDate']     = date('d F Y', strtotime($attrs['EstimatedStartDate']));
   $area->parameters['EstimatedEndDate']       = date('d F Y', strtotime($attrs['EstimatedEndDate']));
   
   
   $param = array
   (
      // 1. REVIEW OF CONTRACT
      'HaveContract',
      'ReportFormatAgreed',
      'PaymentScheduleGuaranteesInsurance',
      'HavePenalty',
      'DeliveryConditions',
      'DemandForDocumentation',
      'IsTotalBudget',
      'PriceStrategyUsed',
      
      // 2. OFFER/QUOTE REVIEW
      'HaveAllDesc',
      'AnythingMissing',
      'HardwareDelivery',
      
      // 3. BID CLARIFICATIONS (BC)
      'HaveBCMinutesOfMeeting',
      'AllBCCorrespondenceArhived',
      
      // 4. ECONOMY
      'ContainBankGuarantees',
      'IsSACASheet',
      'DemandsForReportingToEconomy',
      
      // 5. ORGANIZING
      'HasWorkforce',
      'NeedNewEmployments',
      'HasResources',
      
      // 7. TECHNICAL SOLUTION
      'HasTechnicalSolution',
      'IsInternalDevelopment',
      
      // 13. EXPERIENCE
      'ConditionsComparedToPrevious'
   );
   
   foreach ($param as $name)
   {
      $area->parameters[$name.'_1']      = $attrs[$name] ? $yes : ' ';
      $area->parameters[$name.'_0']      = $attrs[$name] ? ' '  : $no;
      $area->parameters[$name.'Comment'] = $attrs[$name.'Comment'];
   }
   
   // 10. CRITICAL FACTORS
   $area->parameters['CriticalFactors']        = nl2br($attrs['CriticalFactors']);
   $area->parameters['CriticalFactorsComment'] = nl2br($attrs['CriticalFactorsComment']);
   
   $print->put($area);
   
   // 14. MISC
   $area = $mockup->getArea('misc_header');
   $print->put($area);
   
   $area = $mockup->getArea('misc_item');
   
   $i = 0;
   foreach ($misc as $item)
   {
      $area->parameters['MiscIssue']   = $item['Issue'];
      $area->parameters['MiscComment'] = $item['Comment'];
      
      $print->put($area);
      
      $i++;
   }
   
   $area->parameters['MiscIssue']   = '&nbsp;';
   $area->parameters['MiscComment'] = '&nbsp;';
   
   while($i < 35)
   {
      $print->put($area);
      
      $i++;
   }
   
   $area = $mockup->getArea('misc_footer');
   $print->put($area);
   
   
   $area = $mockup->getArea('C1.R79:C22.R98');
   
   // 15. PROJECT MANAGERS DECLARATION OF ACCEPTANCE
   $param = array('SatisfyingAccept', 'PartialSatisfyingAccept', 'NotSatisfyingNotAccept');
   
   foreach ($param as $val)
   {
      $area->parameters[$val] = $attrs['AcceptCondition'] == $val ? $yes : '';
   }
   
   // 16. CONDITION / CAUSES
   for ($i = 0; $i < 4; $i++)
   {
      $cur_cond = isset($cond[$i]) ? $cond[$i] : array('Description' => ' ', 'Comment' => '');
      
      $area->parameters['CondDescription_'.$i] = $cur_cond['Description'];
      $area->parameters['CondComment_'.$i]     = $cur_cond['Comment'];
   }
   
   // 17. WITNESS
   $area->parameters['SalesManager']   = empty($attrs['SalesManager'])   ? ' ' : $attrs['SalesManager']['text'];
   $area->parameters['ProjectManager'] = empty($attrs['ProjectManager']) ? ' ' : $attrs['ProjectManager']['text'];
   
   $print->put($area);
   
   
   echo $print->show();
}
