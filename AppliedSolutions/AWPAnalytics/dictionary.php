<?php

$_dictionary = array(
    
    ////////////////////
    // Catalogs Section
    ////////////////////
    'catalogs' => array(
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
                    )
                ),
                'MiddleName' => array(
                    'type' => 'string',
                    'sql'  => array(
                        'type' => "varchar(255) NOT NULL default ''"
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
                )
            )
        ),
        
        // Only system attributes "code" and "description"
        'Projects' => array(
            'fields' => array(
                'Code' => array(
                    'precision' => array(
                        'max_length' => 8
                    )
                )
            )
        ),
        
        // Only system attributes "code" and "description"
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
        
        'BusinessAreas' => array(
            'fields' => array(
                'Code' => array(
                    'precision' => array(
                        'max_length' => 16
                    )
                )
            )
        )
    ),
    
    ////////////////////
    // Documents Section
    ////////////////////
    'documents' => array(
        'ProjectTimeRecorder' => array(
            'recorder_for' => array(
                'information_registry.ProjectTimeRecords'
            ),
            
            // Tabular Parts go here
            'tabular_sections' => array(
                'Records' => array(
                    'fields' => array(
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
                        ),
                        'Employee' => array(
                            'reference' => 'catalogs.Employees',
                            'precision' => array(
                                'required' => true
                            )
                        ),
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
                        )
                    )
                )
            )
        ),
        
        'ProjectRegistration' => array(
            'recorder_for' => array(
                'information_registry.ProjectRegistrationRecords'
            ),
            
            'tabular_sections' => array(
                'Records' => array(
                    'fields' => array(
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
                        'Deadline' => array(
                            'type' => 'date',
                            'sql'  => array(
                               'type' => "DATE NOT NULL default '0000-00-00'"
                            )
                        )
                    )
                )
            )
        ),
        
        'ProjectAssignment' => array(
            'recorder_for' => array(
                'information_registry.ProjectAssignmentRecords'
            ),
            
            'tabular_sections' => array(
                'Records' => array(
                    'fields' => array(
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
      
      'ProjectTimeRecords' => array(
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
            ),
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
      )
   ),
   
   ///////////////////
   // Reports Section
   ///////////////////
   'reports' => array(
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

