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
            ),
            'Phone' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               )
            ),
            'Photo' => array(
               'type' => 'file',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'image' => array(
                     'preview' => array(
                        'max_width'  => 200,
                        'max_height' => 200
                     )
                  )
               )
            )
         ),

         'Forms' => array(
            'LoginRecords'
         ),

         'Templates' => array(
            'LoginRecords'
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
         ),

         'Forms' => array(
            'UserProfile'
         ),

         'Templates' => array(
            'UserProfile'
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
            ),
            'InvoicingInformation' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'Customer' => array(
               'type' => 'bool',
               'sql'  => array(
                  'type' => "TINYINT(1) NOT NULL default 0"
               )
            ),
            'Supplier'=> array(
               'type' => 'bool',
               'sql'  => array(
                  'type' => "TINYINT(1) NOT NULL default 0"
               )
            )
         )
      ),

      // List of Nomenclature
      'Nomenclature' => array(
         'fields' => array(
            'Price' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'required' => true,
                  'min' => 0
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
            ),
            'Contract' => array(
               'reference' => 'documents.Contract',
               'precision' => array(
                  'required' => true
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
            'information_registry.ProjectAssignmentRecords',
            'information_registry.ProjectAssignmentPeriods'
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
      ),

      // Document Contract
      'Contract' => array(
         /*'recorder_for' => array(

         ),*/

         'basis_for' => array(
            'documents.ProjectRegistration',
            'catalogs.Projects'
         ),

         'fields' => array(
            'Customer' => array(
               'reference' => 'catalogs.Counteragents',
               'precision' => array(
                  'required' => true,
                  'dynamic_update' => true
               )
            ),
            'ContractNumber' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(255) NOT NULL default ''"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'ContractConclusionDate' => array(
               'type' => 'date',
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
               'precision' => array(
                  'required' => true
               )
            ),
            'TotalAmountNOK' => array(
               'type' => 'float',
               'sql'  => array(
                  'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
               ),
               'precision' => array(
                  'required' => true,
                  'min' => 0
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
            )
         ),

         'tabular_sections' => array(
            'Milestones' => array(
               'fields' => array(
                  'MilestoneName' => array(
                     'type' => 'string',
                     'sql'  => array(
                        'type' => "varchar(255) NOT NULL default ''"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'MilestoneDeadline' => array(
                     'type' => 'date',
                     'sql'  => array(
                        'type' => "DATE NOT NULL default '0000-00-00'"
                     ),
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'MilestoneAmountNOK' => array(
                     'type' => 'float',
                     'sql'  => array(
                        'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
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

      // Document Invoice
      'Invoice' => array(
         /*'recorder_for' => array(

         ),*/

         'fields' => array(
            'Customer' => array(
               'reference' => 'catalogs.Counteragents',
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

         'tabular_sections' => array(
            'Milestones' => array(
               'fields' => array(
                  'Nomenclature' => array(
                     'reference' => 'catalogs.Nomenclature',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Quantity' => array(
                     'type' => 'int',
                     'sql'  => array(
                        'type' => "int(8) NOT NULL default 0"
                     ),
                     'precision' => array(
                        'required' => true,
                        'min' => 0
                     )
                  ),
                  'Price' => array(
                     'type' => 'float',
                     'sql'  => array(
                        'type' => "float(8,2) UNSIGNED NOT NULL default 0.00"
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

      // Project Assignment Periods
      'ProjectAssignmentPeriods' => array(
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
      ),

      // LoginRecords
      'LoginRecords' => array(
         'dimensions' => array(
            'NaturalPerson' => array(
               'reference' => 'catalogs.NaturalPersons',
               'precision' => array(
                  'required' => true
               )
            ),
            'AuthType' => array(
               'type' => 'enum',
               'sql'  => array(
                  'type' => "ENUM('MTAuth', 'Basic', 'LDAP')"
               ),
               'precision' => array(
                  'in' => array(1 => 'MTAuth', 2 => 'Basic', 3 => 'LDAP'),
                  'required' => true
               )
            )
         ),

         'fields' => array(
            'SystemUser' => array(
               'reference' => 'catalogs.SystemUsers',
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
         'register_type' => 'Turnovers',

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
            'EmployeeDepartment' => array(
               'reference' => 'catalogs.OrganizationalUnits',
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
   'reports' => array(
      // Time Cards Report
      'TimeCards' => array(
         'fields' => array(
            'Employee' => array(
               'reference' => 'catalogs.Employees'
            ),
            'Period' => array(
               'type' => 'string'
            )
         )
      ),

      // Resource Load Report
      'ResourceLoad' => array(
         'fields' => array(
            'Period' => array(
               'type' => 'enum',
               'precision' => array(
                  'in' => array(
                     'This Week'    => 'This Week',
                     'Last Week'    => 'Last Week',
                     'This Month'   => 'This Month',
                     'Last Month'   => 'Last Month',
                     'This Quarter' => 'This Quarter',
                     'Last Quarter' => 'Last Quarter',
                     'This Year'    => 'This Year',
                     'Last Year'    => 'Last Year'
                  ),
                  'required' => true
               )
            ),
            'ReportKind' => array(
               'type' => 'enum',
               'precision' => array(
                  'in' => array(1 => 'Who does what', 2 => 'Project Workload'),
                  'required' => true
               )
            ),
            'Department' => array(
               'reference' => 'catalogs.OrganizationalUnits'
            ),
            'PM' => array(
               'reference' => 'catalogs.Employees'
            )
         )
      ),

      // Resource Allocation Records Report
      'ResourceAllocationRecords' => array(
         'fields' => array(
            'Period' => array(
               'type' => 'string',
               'precision' => array(
                  'regexp' => '/(\d{4}-\d{2}-\d{2})|This\sWeek|Last\sWeek|This\sMonth|Last\sMonth|'.
                     'This\sQuarter|Last\sQuarter|This\sYear|Last\sYear/',
                  'required' => true
               )
            ),
            'Employee' => array(                   // Use extra field ex_employees
               'reference' => 'catalogs.Employees'
            ),
            'Project' => array(                    // Use extra field ex_projects
               'reference' => 'catalogs.Projects'
            )
         )
      )
   ),

   ///////////////////////////
   // Data processors Section
   ///////////////////////////
/*   'data_processors' => array(
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
   ),*/

   ////////////////
   // Web services
   ////////////////
   'web_services' => array(
      'Pm' => array(
         'actions' => array(
            'getProjectOverview' => array(
               'fields' => array(
                  'Project' => array(
                     'type' => 'string',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Date' => array(
                     'type' => 'date'
                  )
               )
            ),
            'getResourcesAvailable' => array(
               'fields' => array(
                  'Period' => array(
                     'type' => 'string'
                  ),
                  'Department' => array(
                     'type' => 'string'
                  )
               )
            ),
            'getWorkingOnMyProjects' => array(
               'fields' => array(
                  'Date' => array(
                     'type' => 'date'
                  )
               )
            ),
            'getResourcesSpentVsBudgeted' => array(
               'fields' => array(
                  'Project' => array(
                     'type' => 'string',
                     'precision' => array(
                        'required' => true
                     )
                  ),
                  'Date' => array(
                     'type' => 'date'
                  ),
                  'ResourceKind' => array(
                     'type' => 'string'
                  )
               )
            ),
            'getProjectMilestones' => array(
               'fields' => array(
                  'Project' => array(
                     'type' => 'string',
                     'precision' => array(
                        'required' => true
                     )
                  )
               )
            ),
            'getProjectsOngoing' => array(
               'fields' => array(
                  'Department' => array(
                     'type' => 'string'
                  ),
                  'Date' => array(
                     'type' => 'date'
                  )
               )
            ),
            'getWorkingOnProjectsInMyDepartment' => array(
               'fields' => array(
                  'Department' => array(
                     'type' => 'string'
                  ),
                  'Date' => array(
                     'type' => 'date'
                  )
               )
            ),
            'getDepartmentHoursSpent' => array(
               'fields' => array(
                  'Department' => array(
                     'type' => 'string'
                  ),
                  'Period' => array(
                     'type' => 'date'
                  )
               )
            ),
            'getResourcesWorkload' => array(
               'fields' => array(
                  'Department' => array(
                     'type' => 'string'
                  ),
                  'Period' => array(
                     'type' => 'date'
                  )
               )
            )
         )
      ),

      'Personal' => array(
         'actions' => array(
            'getEmployeeHours' => array(
               'fields' => array(
                  'Period' => array(
                     'type' => 'string'
                  )
               )
            ),
            'getEmployeeVacationDays' => array(
            ),
            'getEmployeeProjects' => array(
               'fields' => array(
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
