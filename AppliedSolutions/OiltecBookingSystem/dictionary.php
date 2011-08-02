<?php

$_dictionary = array(
    
   ////////////////////
   // Catalogs Section
   ////////////////////
   'catalogs' => array(
   
      // List of counteragents
      'Counteragents' => array(
         'Hierarchy' => array(
            'type' => 'Folder and item' // Item | Folder and item 
         ),
         
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            ),
            'Type' => array(
               'type' => 'enum',
               'use'  => 'For item',
               'sql'  => array(
                  'type' => "ENUM('LearnersOrganization', 'InstructorsOrganization')"
               ),
               'precision' => array(
                  'in' => array(1 => 'LearnersOrganization', 2 => 'InstructorsOrganization'),
                  'required' => true
               )
            )
         )
      ),
      
      // List of employees
      'Employees' => array(
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            ),
            'Name' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Surname' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Email' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'regexp' => '/^[A-Za-z0-9_.]+@[A-Za-z0-9_]+\.[A-Za-z]+$/i'
               )
            )
         )
      ),
      
      // List of learners
      'Learners' => array(
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            ),
            'LearnersOrganization' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            ),
            'Name' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Surname' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Email' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'regexp' => '/^[A-Za-z0-9_.]+@[A-Za-z0-9_]+\.[A-Za-z]+$/i'
               )
            )
         ),
         
         'Forms' => array(
            'EditForm' => array()
         )
      ),
      
      // List of rooms
      'Rooms' => array(
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            ),
            'Seats' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         )
      ),
      
      // List of course groups
      'CourseGroups' => array(
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            )
         )
      ),
      
      // List of courses
      'Courses' => array(
         'Owners' => array(
            'catalogs.CourseGroups'
         ),
         
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            ),
            'MaxLearners' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'tabular_sections' => array(
            'Lectures' => array(
               'fields' => array(
                  'Duration' => array(
                     'type' => 'float',
                     'sql'  => array(
                        'type' => "float(3,1) UNSIGNED NOT NULL default 0.00"
                     ),
                     'precision' => array(
                        'required' => true,
                        'min' => 0,
                        'max' => 24
                     )
                  ),
                  'Room' => array(
                     'reference' => 'catalogs.Rooms',
                     'precision' => array(
                        'required' => true
                     )
                  )
               )
            )
         )
      )
   ),
   
   
    
   ////////////////////
   // Documents Section
   ////////////////////
   'documents' => array(
   
      // Document PriceList
      'PriceList' => array(
         'recorder_for' => array(
            'information_registry.PriceListRecords'
         ),
         
         'tabular_sections' => array(
            'Costs' => array(
               'fields' => array(
                  'Course' => array(
                     'reference' => 'catalogs.Courses',
                  ),
                  'Cost' => array(
                     'type' => 'float',
                     'sql'  => array(
                        'type' => "FLOAT UNSIGNED NOT NULL default 0.00"
                     ),
                     'precision' => array(
                        'required' => true,
                        'min' => 0
                     )
                  )
               )
            )
         )
      ),
      
      // Document ApplicationForm
      'ApplicationForm' => array(
         'recorder_for' => array(
            'information_registry.ApplicationFormRecords'
         ),
         
         'fields' => array(
            'LearnersOrganization' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true,
                  'dynamic_update' => true
               )
            )
         ),
         
         'tabular_sections' => array(
            'Courses' => array(
               'fields' => array(
                  'Course' => array(
                     'reference' => 'catalogs.Courses',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'CourseNumber' => array(
                     'type' => 'int',
                     'sql'  => array(
                        'type' => "SMALLINT NOT NULL default 0"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'LearnersAmount' => array(
                     'type' => 'int',
                     'sql'  => array(
                        'type' => "SMALLINT NOT NULL default 0"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'StartDate' => array(
                     'type' => 'date',
                     'sql'  => array(
                        'type' => "DATE NOT NULL default '0000-00-00'"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  )
               )
            )
         ),
         
         'Forms' => array(
            'EditForm' => array()
         )
      ),
      
      // Document CourseEvent
      'CourseEvent' => array(
         'recorder_for' => array(
            'information_registry.RoomsScheduleRecords',
            'information_registry.InstructorsScheduleRecords'
         ),
         
         'fields' => array(
            'ApplicationForm' => array(
               'reference' => 'documents.ApplicationForm',
               'precision' => array(
                  'required' => true
               )
            ),
            'LearnersOrganization' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            ),
            'Course' => array(
               'reference' => 'catalogs.Courses',
               'precision' => array(
                  'required' => true
               )
            ),
            'CourseNumber' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'tabular_sections' => array(
            'Schedule' => array(
               'fields' => array(
                  'DateTimeFrom' => array(
                     'type' => 'datetime',
                     'sql'  => array(
                        'type' => "DATETIME NOT NULL default '0000-00-00 00:00:00'"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'DateTimeTo' => array(
                     'type' => 'datetime',
                     'sql'  => array(
                        'type' => "DATETIME NOT NULL default '0000-00-00 00:00:00'"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Room' => array(
                     'reference' => 'catalogs.Rooms',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Instructor' => array(
                     'reference' => 'catalogs.Employees',
                     'precision' => array(
                        'required' => true
                     )
                  )
               )
            )
         ),
         
         'Forms' => array(
            'EditForm' => array()
         )
      ),
      
      // Document LearnersRegistration
      'LearnersRegistration' => array(
         'recorder_for' => array(
            'information_registry.LearnersRegistrationRecords'
         ),
         
         'fields' => array(
            'ApplicationForm' => array(
               'reference' => 'documents.ApplicationForm',
               'precision' => array(
                  'required' => true
               )
            ),
            'Course' => array(
               'reference' => 'catalogs.Courses',
               'precision' => array(
                  'required' => true
               )
            ),
            'CourseNumber' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
         ),
         
         'tabular_sections' => array(
            'Learners' => array(
               'fields' => array(
                  'Learner' => array(
                     'reference' => 'catalogs.Learners',
                     'precision' => array(
                        'required' => true
                     )
                  )
               )
            )
         )
      ),
      
      // Document PO
      'PO' => array(
         'recorder_for' => array(
            'information_registry.PORecords'
         ),
         
         'fields' => array(
            'ApplicationForm' => array(
               'reference' => 'documents.ApplicationForm',
               'precision' => array(
                  'required' => true
               )
            ),
            'LearnersOrganization' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'tabular_sections' => array(
            'Orders' => array(
               'fields' => array(
                  'Course' => array(
                     'reference' => 'catalogs.Courses',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'CourseNumber' => array(
                     'type' => 'int',
                     'sql'  => array(
                        'type' => "SMALLINT NOT NULL default 0"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'LearnersAmount' => array(
                     'type' => 'int',
                     'sql'  => array(
                        'type' => "SMALLINT NOT NULL default 0"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  )
               )
            )
         ),
         
         'Forms' => array(
            'EditForm' => array()
         )
      ),
      
      // Document Invoice
      'Invoice' => array(
         'recorder_for' => array(
            'information_registry.InvoiceRecords'
         ),
         
         'fields' => array(
            'PO' => array(
               'reference' => 'documents.PO',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'tabular_sections' => array(
            'Courses' => array(
               'fields' => array(
                  'Course' => array(
                     'reference' => 'catalogs.Courses',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'CourseNumber' => array(
                     'type' => 'int',
                     'sql'  => array(
                        'type' => "SMALLINT NOT NULL default 0"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Discount' => array(
                     'type' => 'float',
                     'sql'  => array(
                        'type' => "FLOAT(5,2) NOT NULL default 0.00"
                     ),
                     'precision' => array(
                        'min' => -100,
                        'max' => 100
                     )
                  ),
                  'Total' => array(
                     'type' => 'float',
                     'sql'  => array(
                        'type' => "FLOAT UNSIGNED NOT NULL default 0.00"
                     ),
                     'precision' => array(
                        'required' => true,
                        'min' => 0
                     )
                  )
               )
            )
         )
      )
   ),
   

   
   ////////////////////////////////
   // Information registry Section
   ////////////////////////////////
   'information_registry' => array(
      
      // Price List Records
      'PriceListRecords' => array(
         'dimensions' => array(
            'Course' => array(
               'reference' => 'catalogs.Courses',
            )
         ),
         
         'periodical' => 'day', // [ second | day | month | quarter | year ]
         
         'fields' => array(
            'Cost' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "FLOAT UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'required' => true,
                  'min' => 0
               )
            )
         ),
         
         'recorders' => array(
            'PriceList'
         )
      ),
      
      // Application Form Records
      'ApplicationFormRecords' => array(
         'fields' => array(
            'LearnersOrganization' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            ),
            'Course' => array(
               'reference' => 'catalogs.Courses',
               'precision' => array(
                  'required' => true
               )
            ),
            'CourseNumber' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'LearnersAmount' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'StartDate' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'recorders' => array(
            'ApplicationForm'
         )
      ),
      
      // Rooms Schedule Records
      'RoomsScheduleRecords' => array(
         'fields' => array(
            'ApplicationForm' => array(
               'reference' => 'documents.ApplicationForm',
               'precision' => array(
                  'required' => true
               )
            ),
            'LearnersOrganization' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            ),
            'Course' => array(
               'reference' => 'catalogs.Courses',
               'precision' => array(
                  'required' => true
               )
            ),
            'CourseNumber' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Room' => array(
               'reference' => 'catalogs.Rooms',
               'precision' => array(
                  'required' => true
               )
            ),
            'DateTimeFrom' => array(
               'type' => 'datetime',
               'sql'  => array(
                  'type' => "DATETIME NOT NULL default '0000-00-00 00:00:00'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'DateTimeTo' => array(
               'type' => 'datetime',
               'sql'  => array(
                  'type' => "DATETIME NOT NULL default '0000-00-00 00:00:00'"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'recorders' => array(
            'CourseEvent'
         )
      ),
      
      // Instructors Schedule Records
      'InstructorsScheduleRecords' => array(
         'fields' => array(
            'ApplicationForm' => array(
               'reference' => 'documents.ApplicationForm',
               'precision' => array(
                  'required' => true
               )
            ),
            'LearnersOrganization' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            ),
            'Course' => array(
               'reference' => 'catalogs.Courses',
               'precision' => array(
                  'required' => true
               )
            ),
            'CourseNumber' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Instructor' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            ),
            'DateTimeFrom' => array(
               'type' => 'datetime',
               'sql'  => array(
                  'type' => "DATETIME NOT NULL default '0000-00-00 00:00:00'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'DateTimeTo' => array(
               'type' => 'datetime',
               'sql'  => array(
                  'type' => "DATETIME NOT NULL default '0000-00-00 00:00:00'"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'recorders' => array(
            'CourseEvent'
         )
      ),
      
      // Learners Registration Records
      'LearnersRegistrationRecords' => array(
         'dimensions' => array(
            'ApplicationForm' => array(
               'reference' => 'documents.ApplicationForm',
               'precision' => array(
                  'required' => true
               )
            ),
            'Course' => array(
               'reference' => 'catalogs.Courses',
               'precision' => array(
                  'required' => true
               )
            ),
            'Learner' => array(
               'reference' => 'catalogs.Learners',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'fields' => array(
            'CourseNumber' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'LearnersOrganization' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            ),
            'Name' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Surname' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Email' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'regexp' => '/^[A-Za-z0-9_.]+@[A-Za-z0-9_]+\.[A-Za-z]+$/i'
               )
            )
         ),
         
         'recorders' => array(
            'LearnersRegistration'
         )
      ),
      
      // PO Records
      'PORecords' => array(
         'dimensions' => array(
            'ApplicationForm' => array(
               'reference' => 'documents.ApplicationForm',
               'precision' => array(
                  'required' => true
               )
            ),
            'Course' => array(
               'reference' => 'catalogs.Courses',
               'precision' => array(
                  'required' => true
               )
            ),
            'CourseNumber' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'fields' => array(
            'LearnersOrganization' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            ),
            'LearnersAmount' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'recorders' => array(
            'PO'
         )
      ),
      
      // Invoice Records
      'InvoiceRecords' => array(
         'dimensions' => array(
            'PO' => array(
               'reference' => 'documents.PO',
               'precision' => array(
                  'required' => true
               )
            ),
            'Course' => array(
               'reference' => 'catalogs.Courses',
               'precision' => array(
                  'required' => true
               )
            ),
            'CourseNumber' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'fields' => array(
            'LearnersOrganization' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            ),
            'Discount' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "FLOAT(5,2) NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => -100,
                  'max' => 100
               )
            ),
            'Total' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "FLOAT UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'required' => true,
                  'min' => 0
               )
            )
         ),
         
         'recorders' => array(
            'Invoice'
         )
      ),
      
      // Course Instructors
      'CourseInstructors' => array(
         'dimensions' => array(
            'Course' => array(
               'reference' => 'catalogs.Courses',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'fields' => array(
            'InstructorsOrganization' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            ),
            'Instructor' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            ),
         ),
         
         'Forms' => array(
            'EditForm' => array()
         )
      )
   ),
   
   
   
   //////////////////////////////////
   // Accumulation Registers Section
   //////////////////////////////////
   /*'AccumulationRegisters' => array(
   ),*/
   
   
   
   ///////////////////
   // Reports Section
   ///////////////////
   /*'reports' => array(
   ),*/
   
   ///////////////////////////
   // Data processors Section
   ///////////////////////////
   /*'data_processors' => array(
   ),*/
   
   ////////////////
   // Web services
   ////////////////
   /*'web_services' => array(
   ),*/
   
   
   ////////////
   // Security
   ////////////
   /*'AccessRights' => array(
      'OEF_ROLE_3' => array(
         'entities' => array(
            'catalogs' => array(
               'SubProjects' => array(
                  'Read'   => true,
                  'Insert' => false,
                  'Update' => true,
                  'Delete' => true,
                  'View'   => true,
                  'Edit'   => true,
                  'InteractiveInsert' => true,
                  'InteractiveDelete' => false,
                  'InteractiveMarkForDeletion'   => true,
                  'InteractiveUnmarkForDeletion' => true,
                  'InteractiveDeleteMarked'      => true
               ),
               'Projects' => array(
                  'Read'   => false,
                  'Insert' => false,
                  'Update' => false,
                  'Delete' => false,
                  'View'   => false,
                  'Edit'   => false,
                  'InteractiveInsert' => false,
                  'InteractiveDelete' => false,
                  'InteractiveMarkForDeletion'   => false,
                  'InteractiveUnmarkForDeletion' => false,
                  'InteractiveDeleteMarked'      => false
               )
            )
         ),
         'global' =>array(
            'UseRemoteCalls' => true
         )
      )
   ),
   
   'Roles' => array(
      'Admin' => array(
         'password' => 'Admin',
         'roles'    => array('Admin')
      ),
      'User_1' => array(
         'password' => 'User_1',
         'roles'    => array('OEF_ROLE_3')
      )
   ),*/
   
   
   
   /////////////////////
   // Constants Section
   /////////////////////
   /*'Constants' => array(
   )*/
);
