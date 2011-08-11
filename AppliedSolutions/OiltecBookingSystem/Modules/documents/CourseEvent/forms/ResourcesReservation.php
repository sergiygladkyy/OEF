<?php
/**
 * Generate form
 *
 * @param object $event
 * @return void
 */
function onGenerate($event)
{
   $subject = $event->getSubject();
   $name    = $event['name'];
   $params  = $event['parameters'];
   
   $kind = $subject->getKind();
   $type = $subject->getType();
   
   $container = Container::getInstance();
   $document  = $container->getModel($kind, $type);
   $schedule  = array();
   
   // Retrieve attributes
   if (!empty($params['document']) && is_numeric($params['document']) && (int) $params['document'] > 0)
   {
      $id = (int) $params['document'];
      
      if (!$document->load($id))
      {
         throw new Exception('Document not exists');
      }
   }
   
   $attrs = $document->toArray();
   
   $a_select = $container->getCModel('documents', 'ApplicationForm')->retrieveSelectData();
   
   // Retrieve list of courses
   if (!empty($params['ApplicationForm']) && is_numeric($params['ApplicationForm']) && (int) $params['ApplicationForm'] > 0)
   {
      $attrs['ApplicationForm'] = (int) $params['ApplicationForm'];
   }
   
   $c_select = $attrs['ApplicationForm'] > 0 ? ApplicationForm::getCoursesForSelect($attrs['ApplicationForm']) : array();
   
   // Retrieve Schedule
   if (!empty($id))
   {
      $cmodel = $container->getCModel($kind.'.'.$type.'.tabulars', 'Schedule');
      
      $criterion = "WHERE `Owner` = ".$id." ORDER BY `DateTimeFrom`";
      
      if (null === ($schedule = $cmodel->getEntities(null, array('criterion' => $criterion))) || isset($result['errors']))
      {
         throw new Exception('Database error');
      }
   }
   
   
   include(self::$templates_dir.$name.'.php');
}

/**
 * Process form
 *
 * @param object $event
 * @return void
 */
function onProcess($event)
{
   $errors = array();
   $values = $event['values'];
   
   $attrs  = empty($values['attributes']) ? array() : $values['attributes'];
   $action = isset($attrs['_id']) ? 'update' : 'create';
   
   if (!empty($attrs['ApplicationForm']))
   {
      $model = Container::getInstance()->getModel('documents', 'ApplicationForm');
      
      if ($model->load($attrs['ApplicationForm']))
      {
         $attrs['LearnersOrganization'] = $model->getAttribute('LearnersOrganization')->getId();
      }
   }
   
   if (!empty($attrs['Course']))
   {
      $course = explode('/', $attrs['Course']);
      
      $attrs['Course'] = isset($course[0]) ? $course[0] : 0;
      $attrs['CourseNumber'] = isset($course[1]) ? $course[1] : 0;
   }
   
   $controller = Container::getInstance()->getController('documents', 'CourseEvent');
   $return     = $controller->$action(Utility::escaper($attrs));

   if (!$return['status'])
   {
      if (isset($return['errors']['LearnersOrganization']))
      {
         if ($return['errors']['LearnersOrganization'] != 'Required')
         {
            if (empty($return['errors']['ApplicationForm']))
            {
               $return['errors']['ApplicationForm'] = $return['errors']['LearnersOrganization'];
            }
            else if (is_array($return['errors']['ApplicationForm']))
            {
               $return['errors']['ApplicationForm'][] = $return['errors']['LearnersOrganization'];
            }
            else
            {
               $return['errors']['ApplicationForm'] = array($return['errors']['ApplicationForm'], $return['errors']['LearnersOrganization']);
            }
         }
         
         unset($return['errors']['LearnersOrganization']);
      }
      
      if (isset($return['errors']['CourseNumber']))
      {
         if ($return['errors']['CourseNumber'] != 'Required')
         {
            if (empty($return['errors']['Course']))
            {
               $return['errors']['Course'] = $return['errors']['CourseNumber'];
            }
            else if (is_array($return['errors']['Course']))
            {
               $return['errors']['Course'][] = $return['errors']['CourseNumber'];
            }
            else
            {
               $return['errors']['Course'] = array($return['errors']['Course'], $return['errors']['CourseNumber']);
            }
         }
         
         unset($return['errors']['CourseNumber']);
      }
      
      
      $event->setReturnValue($return);
      
      return;
   }
   
   // Save tabular section
   $owner_id = $return['result']['_id'];
   
   if ($action != 'create') unset($return['result']['_id']);
   
   if (empty($values['tabulars']['Schedule']))
   {
      $return['tabulars']['Schedule'] = array('status' => false, 'result' => array('msg' => 'Schedule can\'t be empty'));
   }
   else
   {
      $return['tabulars']['Schedule'] = self::processTabularSection('documents.CourseEvent.tabulars', 'Schedule', $values['tabulars']['Schedule'], $owner_id);
   }

   /*$return = array(
      'status' => false,
      'result' => array(),
      'errors' => array('<pre>'.print_r($values, true).'</pre>')
   );*/
   
   $event->setReturnValue($return);
}

/**
 * Process tabular section form
 *
 * @param string $kind
 * @param string $type
 * @param array $params
 * @param int $owner_id
 * @return array
 */
function processTabularSection($kind, $type, array $params, $owner_id)
{
   $result = array();
   $ids    = array();

   $controller = Container::getInstance()->getController($kind, $type);

   // Check values
   $params = Utility::escapeRecursive($params);

   // Delete
   if (isset($params['deleted']))
   {
      if (!empty($params['deleted']))
      {
         $options = array(
            'attributes' => array('%pkey', 'Owner'),
            'criterion'  => '`Owner` = %%Owner%% AND `%pkey` IN (%%pkey%%)'
         );
         $result['delete'] = $controller->delete(array('%pkey' => $params['deleted'], 'Owner' => $owner_id), $options);
      }

      unset($params['deleted']);
   }

   // Save all
   foreach ($params as $key => $values)
   {
      $values['Owner'] = $owner_id;

      $action = isset($values['_id']) ? 'update' : 'create';

      $result[$key] = $controller->$action($values);

      if ($result[$key]['status'] && $action == 'update')
      {
         unset($result[$key]['result']['_id']);
      }
   }

   return $result;
}

/**
 * Process onScheduleItemUpdate event
 *
 * @param object $event
 * @return void
 */
function onChangeCourse($event)
{
   $formData = $event['formData'];
   $doc = $formData['aeform']['documents']['CourseEvent']['attributes']['attributes'];
   
   $container = Container::getInstance();
   
   // Clear tabular section Schedule
   if (!empty($doc['_id']))
   {
      $cmodel = $container->getCModel('documents.CourseEvent.tabulars', 'Schedule');
      
      if ($cmodel->delete($doc['_id'], array('attributes' => 'Owner')))
      {
         throw new Exception('Database error');
      }
   }
   
   // Generate default Schedule
   $_course = explode('/', $doc['Course']);
   
   $instructor = Courses::getDefaultInstructor($_course[0]);
   $lectures   = Courses::getLections($_course[0]);
   $course     = ApplicationForm::getCourse($doc['ApplicationForm'], $_course[0], $_course[1]);
   $start_date = $course['StartDate'];
   $options    = self::getScheduleOptions();
   
   $schedule = array();
   
   foreach ($lectures as $key => $item)
   {
      $schedule[$key]['DateTimeFrom'] = $start_date.' '.$options['time_from'];
      $schedule[$key]['DateTimeTo']   = date('Y-m-d H:i:s', strtotime($schedule[$key]['DateTimeFrom']) + $item['Duration']*3600);
      $schedule[$key]['Room']         = $item['Room'];
      $schedule[$key]['Instructor']   = $instructor;
   }
   
   $html = self::include_template('schedule', array(
      'kind'  => 'documents',
      'type'  => 'CourseEvent',
      'owner' => (empty($doc['_id']) ? 0 : $doc['_id']),
      'schedule' => $schedule
   ));
   
   $event->setReturnValue(array('type' => 'html', 'data' => $html));
}


/**
 * Process onScheduleItemUpdate event
 *
 * @param object $event
 * @return void
 */
function onScheduleItemUpdate($event)
{
   $formData = $event['formData'];
   $params   = $event['parameters'];
   
   if (!isset($params['index'])) throw new Exception('Invalid schedule index');
   
   $index = $params['index'];
   
   if (!isset($formData['aeform']['documents']['CourseEvent']['attributes']['tabulars']['Schedule'][$index]))
   {
      throw new Exception('Invalid data');
   }
   
   $item = $formData['aeform']['documents']['CourseEvent']['attributes']['tabulars']['Schedule'][$index];
   $doc  = $formData['aeform']['documents']['CourseEvent']['attributes']['attributes'];
   
   $doc_id = empty($doc['_id']) ? 0 : $doc['_id'];
   
   $event->setReturnValue(array(
      'type' => 'array',
      'data' => array(
         'index' => $index,
         'html'  => self::generateScheduleItem($item, $index, $doc_id)
      )
   ));
}





/**
 * Generate edit form for tabular section Schedule record
 * 
 * @param array $item  - attributes
 * @param int   $index - unique index
 * @param int   $owner - owner id
 * @return string - HTML
 */
function generateScheduleItem($item, $index, $owner = 0)
{
   $room_recs = array();
   $inst_recs = array();
   $ts_from = $ts_to = null;
   
   $options = self::getScheduleOptions();
   
   $check_period = true;
   
   if (empty($item['DateTimeFrom']))
   {
      if (empty($item['Date']))
      {
         $check_period = false;
      }
      else if (($ts_from = strtotime($item['Date'].' '.$options['time_from'])) === -1)
      {
         throw new Exception('Invalid date format');
      }
   }
   else if (($ts_from = strtotime($item['DateTimeFrom'])) === -1)
   {
      throw new Exception('Invalid date format');
   }
   
   if (empty($item['DateTimeTo']))
   {
      if (empty($item['Date']))
      {
         $check_period = false;
      }
      else
      {
         if (($ts_to = strtotime($item['Date'].' '.date('H:i:s', $ts_from))) === -1)
         {
            throw new Exception('Invalid date format');
         }
         
         if (!empty($item['Duration']))
         {
            $ts_to += $item['Duration'] * 3600;
         }
      }
   }
   else if (($ts_to = strtotime($item['DateTimeTo'])) === -1)
   {
      throw new Exception('Invalid date format');
   }
   
   $container = Container::getInstance();
   
   if ($check_period)
   {
      if (date('Y-m-d', $ts_from) != date('Y-m-d', $ts_to))
      {
         throw new Exception('Invalid period: '.date('Y-m-d H:i:s', $ts_from).' - '.date('Y-m-d H:i:s', $ts_to));
      }
      
      $fdate  = $tdate = date('Y-m-d', $ts_from).' ';
      $fdate .= $options['time_from'];
      $tdate .= $options['time_to'];
       
      // Room
      if (!empty($item['Room']))
      {
         $room_recs = Rooms::getRoomSchedule($item['Room'], $fdate, $tdate);
      }
       
      // Instructor
      if (!empty($item['Instructor']))
      {
         $inst_recs = Instructors::getInstructorSchedule($item['Instructor'], $fdate, $tdate);
      }
   }
   
   // Generate HTML
   $params = array(
      'index'     => $index,
      'kind'      => 'documents',
      'type'      => 'CourseEvent',
      'owner'     => $owner,
      'options'   => $options,
      'ts_from'   => $ts_from, 
      'ts_to'     => $ts_to,
      'item'      => $item,
      'room_recs' => $room_recs,
      'inst_recs' => $inst_recs,
      'links'     => $container->getCModel('documents.CourseEvent.tabulars', 'Schedule')->retrieveSelectDataForRelated()
   );
   
   return self::include_template('schedule_item', $params);
}

/**
 * Return schedule options
 * 
 * @return array
 */
function getScheduleOptions()
{
   return array(
      'time_from' => '08:00:00',
      'time_to'   => '19:00:00',
      'step'      => '30' // minutes
   );
}

/**
 * Include template
 * 
 * @param string $name - template name
 * @param $params      - list of attributes
 * @return string
 */
function include_template($name, $params)
{
   extract($params, EXTR_OVERWRITE);
   
   ob_start();
   
   include(self::$templates_dir.'_'.$name.'.php');
   
   return ob_get_clean();
}
