<?php

$_dictionary = array(
    
    ////////////////////
    // Catalogs Section
    ////////////////////
    'catalogs' => array(
        'Employees' => array(
            'fields' => array(
                'code' => array(
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
                'Middle_name' => array(
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
                'code' => array(
                    'precision' => array(
                        'max_length' => 8
                    )
                )
            )
        ),
        
        // Only system attributes "code" and "description"
        'Subprojects' => array(
            'fields' => array(
                'code' => array(
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
        
        'BusinessArea' => array(
            'fields' => array(
                'code' => array(
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
        'ProjectTimeRecords' => array(
            'recorder_for' => array(
                'ResourcesAssignments'
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
                        'Subproject' => array(
                            'reference' => 'catalogs.Subprojects',
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
                            'reference' => 'catalogs.BusinessArea',
                            'precision' => array(
                                'required' => true
                            )
                        ),
                        'Number_of_hours' => array(
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
      'ResourcesAssignments' => array(
         'dimensions' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects',
               'precision' => array(
                  'required' => true
               )
            ),
            'Subproject' => array(
               'reference' => 'catalogs.Subprojects',
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
               'reference' => 'catalogs.BusinessArea',
               'precision' => array(
                  'required' => true
               )
            ),
            'Number_of_hours' => array(
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
             'ProjectTimeRecords'
         )
      ),
      
      'RejectedImportRecords' => array(
         'fields' => array(
            'Project' => array(
               'reference' => 'catalogs.Projects'
            ),
            'Subproject' => array(
               'reference' => 'catalogs.Subprojects'
            ),
            'Employee' => array(
               'reference' => 'catalogs.Employees'
            ),
            'BusinessArea' => array(
               'reference' => 'catalogs.BusinessArea'
            ),
            'Number_of_hours' => array(
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
            'DocumentID' => array(
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
            'Errors' => array(
               'type' => 'string',
               'sql'  => array(
                  'type' => "varchar(256) NOT NULL default ''"
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
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
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
               'sql'  => array(
                  'type' => "DATE NOT NULL default '0000-00-00'"
               ),
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
      )
   ),
   
   ///////////////////////////
   // Data processors Section
   ///////////////////////////
   'data_processors' => array(
      'NavImport' => array(
         'fields' => array(
            'Server' => array(
               'type' => 'string',
               'precision' => array(
                  'required' => true
               )
            ),
            'Filename' => array(
               'type' => 'string',
               'precision' => array(
                  'required' => true
               )
            ),
            'Username' => array(
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
   )
);
