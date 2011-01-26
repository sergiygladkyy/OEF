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
            'Responsible' => array(
               'reference' => 'catalogs.SystemUsers',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'tabular_sections' => array(
            'Employees' => array(
               'fields' => array(
                  'Manager' => array(
                     'reference' => 'catalogs.Employees',
                  ),
                  'NaturalPerson' => array(
                     'reference' => 'catalogs.NaturalPersons',
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
                        'min' => 0,
                        'max_length' => 10
                     )
                  ),
                  'YearlyVacationDays' => array(
                     'type' => 'int',
                     'sql'  => array(
                        'type' => "TINYINT UNSIGNED NOT NULL default 0"
                     ),
                     'precision' => array(
                        'required' => true,
                        'min' => 0,
                        'max_length' => 3
                     )
                  )
               )
            )
         )
      ),
      
      // Document DismissalOrder
      'DismissalOrder' => array(
         'recorder_for' => array(
            'information_registry.StaffEmploymentPeriods',
            'information_registry.StaffHistoricalRecords',
            'AccumulationRegisters.EmployeeVacationDays'
         ),
         
         'fields' => array(
            'Responsible' => array(
               'reference' => 'catalogs.SystemUsers',
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
         'recorder_for' => array(
            'information_registry.ScheduleVarianceRecords',
            'AccumulationRegisters.EmployeeVacationDays'
         ),
         
         'fields' => array(
            'Responsible' => array(
               'reference' => 'catalogs.SystemUsers',
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
      
      // Document PeriodicClosing
      'PeriodicClosing' => array(
         'recorder_for' => array(
            'AccumulationRegisters.EmployeeVacationDays'
         )
      ),
      
      // Document ProjectRegistration
      'ProjectRegistration' => array(
         'recorder_for' => array(
            'information_registry.ProjectRegistrationRecords',
            'information_registry.SubprojectRegistrationRecords',
            'information_registry.MilestoneRecords'
         ),
         
         'fields' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true,
                  'dynamic_update' => true
               )
            ),
            'ProjectDepartment' => array(
               'reference' => 'catalogs.OrganizationalUnits',
               'precision' => array(
                  'required' => true,
                  'dynamic_update' => true
               )
            ),
            'ProjectManager' => array(
               'reference' => 'catalogs.Employees',
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
                  'required' => true,
                  'min' => 0
               )
            ),
            'BudgetNOK' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'required' => true,
                  'min' => 0
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
            'DeliveryDate' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Customer' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true,
                  'dynamic_update' => true
               )
            )
         ),
         
         'tabular_sections' => array(
            'Subprojects' => array(
               'fields' => array(
                  'SubProject' => array(
                     'reference' => 'catalogs.SubProjects',
                     'precision' => array(
                        'required' => true,
                        'dynamic_update' => true
                     )
                  )
               )
            ),
            'Milestones' => array(
               'fields' => array(
                  'MileStoneName' => array(
                     'type' => 'string',
                     'sql'  => array(
                        'type' => "varchar(255) NOT NULL default ''"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'MileStoneDeadline' => array(
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
      
      // Document ProjectClosure
      'ProjectClosure' => array(
         'recorder_for' => array(
            'information_registry.ProjectClosureRecords'
         ),
         
         'fields' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            ),
            'ClosureDate' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'ClosureComment' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               )
            )
         )
      ),
      
      // Document ProjectAssignment
      'ProjectAssignment' => array(
         'recorder_for' => array(
            'information_registry.ProjectAssignmentRecords'
         ),
         
         'fields' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'tabular_sections' => array(
            'Resources' => array(
               'fields' => array(
                  'Employee' => array(
                     'reference' => 'catalogs.Employees',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'SubProject' => array(
                     'reference' => 'catalogs.SubProjects'
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
                  ),
                  'HoursPerDay' => array(
                     'type' => 'float',
                     'sql'  => array(
                        'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
                     ),
                     'precision' => array(
                        'required' => true,
                        'min' => 0
                     )
                  ),
                  'Comment' => array(
                     'type' => 'string',
                     'sql'  => array(
                        'type' => "varchar(255) NOT NULL default ''"
                     )
                  )
               )
            )
         )
      ),
      
      // Document TimeCard
      'TimeCard' => array(
         'recorder_for' => array(
            'information_registry.TimeReportingRecords',
            'AccumulationRegisters.EmployeeHoursReported'
         ),
         
         'fields' => array(
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
            ),
         ),
         
         'tabular_sections' => array(
            'TimeRecords' => array(
               'fields' => array(
                  'Project' => array(
                     'reference' => 'catalogs.Projects',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'SubProject' => array(
                     'reference' => 'catalogs.SubProjects'
                  ),
                  'Date' => array(
                     'type' => 'date',
                     'sql'  => array(
                        'type' => "DATE NOT NULL default '0000-00-00'"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Hours' => array(
                     'type' => 'float',
                     'sql'  => array(
                        'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
                     ),
                     'precision' => array(
                        'min' => 0
                     )
                  ),
                  'Comment' => array(
                     'type' => 'string',
                     'sql'  => array(
                        'type' => "varchar(255) NOT NULL default ''"
                     )
                  )
               )
            )
         ),
         
         'Forms' => array(
            'TimeCard'
         ),
         
         'Templates' => array(
            'TimeCard'
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
            'RecruitingOrder',
            'DismissalOrder'
         )
      ),
      
      // Schedule Variance Records
      'ScheduleVarianceRecords' => array(
         'fields' => array(
            'Employee' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            ),
            'DateFrom' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'DateTo' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'VarianceKind' => array(
               'type' => 'enum',
               'sql'  => array(
                  'type' => "ENUM('Vacation', 'Sick', 'Other')"
               ),
               'precision' => array(
                  'in' => array(1 => 'Vacation', 2 => 'Sick', 3 => 'Other'),
                  'required' => true
               )
            )
         ),
         
         'recorders' => array(
            'VacationOrder'
         )
      ),
      
      // Project Registration Records
      'ProjectRegistrationRecords' => array(
         'dimensions' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'fields' => array(
            'ProjectDepartment' => array(
               'reference' => 'catalogs.OrganizationalUnits',
               'precision' => array(
                  'required' => true
               )
            ),
            'ProjectManager' => array(
               'reference' => 'catalogs.Employees',
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
                  'required' => true,
                  'min' => 0
               )
            ),
            'BudgetNOK' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'required' => true,
                  'min' => 0
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
            'DeliveryDate' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Customer' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'recorders' => array(
            'ProjectRegistration'
         )
      ),
      
      // Subproject Registration Records
      'SubprojectRegistrationRecords' => array(
         'dimensions' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            ),
            'SubProject' => array(
               'reference' => 'catalogs.SubProjects',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'recorders' => array(
            'ProjectRegistration'
         )
      ),
      
      // Milestone Records
      'MilestoneRecords' => array(
         'dimensions' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            ),
            'MileStoneName' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'fields' => array(
            'MileStoneDeadline' => array(
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
            'ProjectRegistration'
         )
      ),
      
      // Project Closure Records
      'ProjectClosureRecords' => array(
         'dimensions' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         'fields' => array(
            'ClosureDate' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Comment' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               )
            )
         ),
         
         'recorders' => array(
            'ProjectClosure'
         )
      ),
      
      // Project Assignment Records
      'ProjectAssignmentRecords' => array(
         'dimensions' => array(
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
                  'required' => true,
                  'min' => 0
               )
            ),
            'SubProject' => array(
               'reference' => 'catalogs.SubProjects'
            ),
            'ProjectDepartment' => array(
               'reference' => 'catalogs.OrganizationalUnits',
               'precision' => array(
                  'required' => true
               )
            ),
            'EmployeeDepartment' => array(
               'reference' => 'catalogs.OrganizationalUnits',
               'precision' => array(
                  'required' => true
               )
            ),
            'Comment' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               )
            )
         ),
         
         'recorders' => array(
            'ProjectAssignment'
         )
      ),
      
      // Time Reporting Records
      'TimeReportingRecords' => array(
         'dimensions' => array(
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
            ),
            'SubProject' => array(
               'reference' => 'catalogs.SubProjects'
            ),
            'ProjectDepartment' => array(
               'reference' => 'catalogs.OrganizationalUnits',
               'precision' => array(
                  'required' => true
               )
            ),
            'EmployeeDepartment' => array(
               'reference' => 'catalogs.OrganizationalUnits',
               'precision' => array(
                  'required' => true
               )
            ),
            'Comment' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               )
            )
         ),
         
         'recorders' => array(
            'TimeCard'
         )
      ),
      
      // Divisional Chiefs
      'DivisionalChiefs' => array(
         'dimensions' => array(
            'OrganizationalUnit' => array(
               'reference' => 'catalogs.OrganizationalUnits',
               'precision' => array(
                  'required' => true
               )
            ),
            'DivisionalChief' => array(
               'reference' => 'catalogs.OrganizationalPositions',
               'precision' => array(
                  'required' => true
               )
            )
         )
      )
   ),
   
   
   
   //////////////////////////////////
   // Accumulation Registers Section
   //////////////////////////////////
   'AccumulationRegisters' => array(
      'EmployeeVacationDays'  => array(
         'register_type' => 'Balances',
         
         'dimensions' => array(
            'Employee' => array(
               'reference' => 'catalogs.Employees',
               'precision' => array(
                  'required' => true
               )
            )
         ),
         
         //'periodical' - Always second
         
         'fields' => array(
            'VacationDays' => array(
               'type' => 'int',
               'sql'  => array(
                  'type' => "SMALLINT NOT NULL default 0"
               ),
               'precision' => array(
                  'min' => 0
               )
            )
         ),
         
         'recorders' => array(
             'VacationOrder',
             'PeriodicClosing',
             'DismissalOrder'
         )
      ),
      
      // Employee Hours Reported
      'EmployeeHoursReported' => array(
         'register_type' => 'Balances',
      
         'dimensions' => array(
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
            ),
            'OvertimeHours' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => 0
               )
            ),
            'ExtraHours' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'min' => 0
               )
            )
         ),
         
         'recorders' => array(
            'TimeCard'
         )
      )
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
   ),
   
   
   
   /////////////////////
   // Constants Section
   /////////////////////
   'Constants' => array(
      'ProjectManagerPosition' => array(
         'reference' => 'catalogs.OrganizationalPositions',
         'precision' => array(
            'required' => true
         )
      ),
      'DivisionalChiefPosition' => array(
         'reference' => 'catalogs.OrganizationalPositions',
         'precision' => array(
            'required' => true
         )
      )
   )
);
