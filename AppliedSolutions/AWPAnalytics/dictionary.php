<?php

$_dictionary = array(
    
   ////////////////////
   // Catalogs Section
   ////////////////////
   'catalogs' => array(
   
      // List of Departments of the Enterprise (Organization)
      'OrganizationalUnits' => array(
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            ),
         )
      ),
      
      // Contains a list of Positions that can be assigned, when hiring the person
      'OrganizationalPositions' => array(
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            ),
         )
      ),
      
      // List of Private individuals and their base attributes
      'NaturalPersons' => array(
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
            'Birthday' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Gender' => array(
               'type' => 'enum',
               'sql'  => array(
                  'type' => "ENUM('Male', 'Female')"
               ),
               'precision' => array(
                  'in' => array(1 => 'Male', 2 => 'Female'),
                  'required' => true
               )
            ),
            'PlaceOfBirh' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
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
            'NaturalPerson' => array(
               'reference' => 'catalogs.NaturalPersons',
               'precision' => array(
                  'required' => true
               )
            ),
            'NowEmployed' => array(
               'type' => 'bool',
               'sql'  => array(
                  'type' => "TINYINT(1) NOT NULL default 0"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         )
      ),
      
      // List of Projects
      'Projects' => array(
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            )
         )
      ),
      
      // List of SubProjects
      'SubProjects' => array(
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            ),
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            )
         )
      ),
      
      // List of Counteragents
      'Counteragents' => array(
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            ),
            'ContactInformation' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               )
            )
         )
      ),
      
      // List of Schedules
      'Schedules' => array(
         'fields' => array(
            'Code' => array(
               'precision' => array(
                  'max_length' => 8
               )
            )
         ),
         
         'Forms' => array(
            'Schedule'
         ),
         
         'Templates' => array(
            'Schedule'
         )
      )
   ),
   
   
    
   ////////////////////
   // Documents Section
   ////////////////////
   'documents' => array(
   
      // Document RecruitingOrder
      'RecruitingOrder' => array(
         'recorder_for' => array(
            'information_registry.StaffEmploymentPeriods',
            'information_registry.StaffHistoricalRecords'
         ),
         
         'fields' => array(
            'Manager' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'tabular_sections' => array(
            'Employees' => array(
               'fields' => array(
                  'NaturalPerson' => array(
                     'reference' => 'catalogs.NaturalPersons',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Employee' => array(
                     'reference' => 'catalogs.Employees',
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
                  ),
                  'Position' => array(
                     'reference' => 'catalogs.OrganizationalPositions',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'OrganizationalUnit' => array(
                     'reference' => 'catalogs.OrganizationalUnits',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Schedule' => array(
                     'reference' => 'catalogs.Schedules',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'InternalHourlyRate' => array(
                     'type' => 'float',
                     'sql'  => array(
                        'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
                     ),
                     'precision' => array(
                        'required' => true,
                        'min' => 0
                     )
                  ),
                  'YearlyVacationDays' => array(
                     'type' => 'int',
                     'sql'  => array(
                        'type' => "TINYINT UNSIGNED NOT NULL default 0"
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
      
      // Document DismissalOrder
      'DismissalOrder' => array(
         'recorder_for' => array(
            'information_registry.StaffEmploymentPeriods'
         ),
         
         'fields' => array(
            'Manager' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'tabular_sections' => array(
            'Employees' => array(
               'fields' => array(
                  'Employee' => array(
                     'reference' => 'catalogs.Employees',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'DismissalDate' => array(
                     'type' => 'date',
                     'sql'  => array(
                        'type' => "DATE NOT NULL default '0000-00-00'"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Reason' => array(
                     'type' => 'string',
                     'sql'  => array(
                        'type' => "varchar(255) NOT NULL default ''"
                     )
                  )
               )
            )
         )
      ),
      
      // Document VacationOrder
      'VacationOrder' => array(
         'fields' => array(
            'Manager' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'tabular_sections' => array(
            'Employees' => array(
               'fields' => array(
                  'NaturalPerson' => array(
                     'reference' => 'catalogs.NaturalPersons',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Employee' => array(
                     'reference' => 'catalogs.Employees',
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
                  ),
                  'EndDate' => array(
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
         )
      ),
      
      // Document ProjectRegistration
      'ProjectRegistration' => array(
         /*'recorder_for' => array(
            'information_registry.ProjectRegistrationRecords'
         ),*/
         
         'fields' => array(
            'ProjectManager' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            ),
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            ),
            'BudgetNOK' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => 0
               )
            ),
            'BudgetHRS' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => 0
               )
            ),
            'DeliveryDate' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               )
            ),
            'Customer' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            )
         )
      ),
      
      // Document ProjectAssignment
      'ProjectAssignment' => array(
         /*'recorder_for' => array(
            'information_registry.ProjectAssignmentRecords'
         ),*/
         
         'tabular_sections' => array(
            'Resources' => array(
               'fields' => array(
                  'Employee' => array(
                     'reference' => 'catalogs.Employees',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Project' => array(
                     'reference' => 'catalogs.Projects',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'BudgetHRS' => array(
                     'type' => 'float',
                     'sql'  => array(
                        'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
                     ),
                     'precision' => array(
                        'min' => 0
                     )
                  ),
                  'Rate' => array(
                     'type' => 'float',
                     'sql'  => array(
                        'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
                     ),
                     'precision' => array(
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
      
      // Calendar
      'BaseCalendar' => array(
         'dimensions' => array(
            'Year' => array(
               'type' => 'year',
               'sql'  => array(
                  'type' => "YEAR NOT NULL default '0000'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Date' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'fields' => array(
            'Working' => array(
               'type' => 'bool',
               'sql'  => array(
                  'type' => "TINYINT(1) NOT NULL default 0"
               )
            ),
            'FiveDaysSchedule' => array(
               'type' => 'bool',
               'sql'  => array(
                  'type' => "TINYINT(1) NOT NULL default 0"
               )
            ),
            'WorkingDayNumber' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT(2) NOT NULL default 0"
               )
            )
         ),
         
         'Forms' => array(
            'BaseCalendar'
         ),
         
         'Templates' => array(
            'BaseCalendar'
         )
      ),
      
      // Schedules
      'Schedules' => array(
         'dimensions' => array(
            'Schedule' => array(
               'reference' => 'catalogs.Schedules',
               'precision' => array(
                  'required' => true
               )
            ),
            'Year' => array(
               'type' => 'year',
               'sql'  => array(
                  'type' => "YEAR NOT NULL default '0000'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Date' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'fields' => array(
            'Hours' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => 0
               )
            )
         )
      ),
      
      // Staff Employment Periods
      'StaffEmploymentPeriods' => array(
         'dimensions' => array(
            'Employee' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            ),
            'StartDate' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               )
            ),
            'EndDate' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               )
            )
         ),
         
         'recorders' => array(
            'RecruitingOrder',
            'DismissalOrder'
         )
      ),
      
      // Staff Historical Records
      'StaffHistoricalRecords' => array(
         'dimensions' => array(
            'Employee' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'periodical' => 'day', // [ second | day | month | quarter | year ]
         
         'fields' => array(
            'OrganizationalUnit' => array(
               'reference' => 'catalogs.OrganizationalUnits'
            ),
            'Schedule' => array(
               'reference' => 'catalogs.Schedules'
            ),
            'OrganizationalPosition' => array(
               'reference' => 'catalogs.OrganizationalPositions'
            ),
            'InternalHourlyRate' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => 0
               )
            ),
            'YearlyVacationDays' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "TINYINT UNSIGNED NOT NULL default 0"
               ),
               'precision' => array(
                  'min' => 0
               )
            ),
            'RegisteredEvent' => array(
               'type' => 'enum',
               'sql'  => array(
                  'type' => "ENUM('Hiring', 'Firing', 'Move')"
               ),
               'precision' => array(
                  'in' => array(1 => 'Hiring', 2 => 'Firing', 3 => 'Move'),
                  'required' => true
               )
            ),
         ),
         
         'recorders' => array(
            'RecruitingOrder'
         )
      ),
      
   /*   'ProjectTimeRecords' => array(
         'dimensions' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            ),
            'SubProject' => array(
               'reference' => 'catalogs.SubProjects',
               /*'precision' => array(
                  'required' => true
               )*/
/*            ),
            'Employee' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            ),
         ),
         
         //'periodical' => 'day', // [ second | day | month | quarter | year ]
         
         'fields' => array(
            'BusinessArea' => array(
               'reference' => 'catalogs.BusinessAreas',
               'precision' => array(
                  'required' => true
               )
            ),
            'HoursSpent' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => 0
               )
            ),
            'Date' => array(
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
             'ProjectTimeRecorder'
         )
      ),
      
      'RejectedImportRecords' => array(
         'fields' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects'
            ),
            'SubProject' => array(
               'reference' => 'catalogs.SubProjects'
            ),
            'Employee' => array(
               'reference' => 'catalogs.Employees'
            ),
            'BusinessArea' => array(
               'reference' => 'catalogs.BusinessAreas'
            ),
            'HoursSpent' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(128) NOT NULL default ''"
               )
            ),
            'DocumentType' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(128) NOT NULL default ''"
               )
            ),
            'DocumentNumber' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "INT(11) NOT NULL default 0"
               )
            ),
            'Date' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               )
            ),
            'RejectReason' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(256) NOT NULL default ''"
               )
            )
         )
      ),
      
      'ProjectRegistrationRecords' => array(
         'dimensions' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'periodical' => 'day', // [ second | day | month | quarter | year ]
         
         'fields' => array(
            'BudgetNOK' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => 0
               )
            ),
            'BudgetHRS' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => 0
               )
            ),
            'Deadline' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               )
            )
         )
      ),
      
      'ProjectAssignmentRecords' => array(
         'dimensions' => array(
            'Resource' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            ),
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'periodical' => 'day', // [ second | day | month | quarter | year ]
         
         'fields' => array(
            'BudgetHRS' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => 0
               )
            ),
            'Rate' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => 0
               )
            )
         )
      )*/
   ),
   
   ///////////////////
   // Reports Section
   ///////////////////
/*   'reports' => array(
      'ProjectManHours' => array(
         'fields' => array(
            'Date' => array(
               'type' => 'date',
               'precision' => array(
                  'required' => true
               )
            )
         )
      ),
      'ProjectResources' => array(
         'fields' => array(
            'Date' => array(
               'type' => 'date',
               'precision' => array(
                  'required' => true
               )
            ),
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            )
         )
      ),
      // Which projects are ongoing
      'RegisteredProjects' => array(
         'fields' => array(
            'Date' => array(
               'type' => 'date'
            )
         )
      ),
      // How large are the projects (personnel involved and budget)
      'ProjectOverview' => array(
         'fields' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            )
         )
      ),
      // Who are working on my projects
      'ResourceAssignments' => array(
         'fields' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            )
         )
      ),
      // How many hours/money have we spent vs. how much is budgeted.
      'ProjectPerformance' => array(
         'fields' => array(                        // Use extra field extProjects
            'Projects' => array(
               'reference' => 'catalogs.Projects'
            )
         )
      ),
      // What is the hourly cost of the different consultants?
      'ResourceCost' => array(
         'fields' => array(                        // Use extra field extEmployees
            'Employees' => array(
               'reference' => 'catalogs.Employees'
            )
         )
      )
   ),
   
   ///////////////////////////
   // Data processors Section
   ///////////////////////////
   'data_processors' => array(
      'NavCsvImport' => array(
         'fields' => array(
            'Server' => array(
               'type' => 'string',
               'precision' => array(
                  'required' => true
               )
            ),
            'FileName' => array(
               'type' => 'string',
               'precision' => array(
                  'required' => true
               )
            ),
            'UserName' => array(
               'type' => 'string',
               'precision' => array(
                  'required' => true
               )
            ),
            'Password' => array(
               'type' => 'password',
               'precision' => array(
                  'required' => true
               )
            )
         )
      )
   ),
   
   ////////////////
   // Web services
   ////////////////
   'web_services' => array(
      'Pm' => array(
         'actions' => array(
            'getProjectList' => array(
               'fields' => array()
            ),
            'getProjectMembers' => array(
               'fields' => array(
                  'Project' => array(
                     'reference' => 'catalogs.Projects',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Date' => array(
                     'type' => 'date'
                  )
               )
            ),
            'getUserProjects' => array(
               'fields' => array(
                  'Date' => array(
                     'type' => 'date'
                  )
               )
            ),
            'getProjectCost' => array(
               'fields' => array(
                  'Project' => array(
                     'reference' => 'catalogs.Projects',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Date' => array(
                     'type' => 'date'
                  )
               )
            )
         )
      )
   ),
   */
   
   ////////////
   // Security
   ////////////
   'AccessRights' => array(
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
   )
);

