<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:Nrm.AddVisit',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Add Watchdog visits',
      'description' => 'Add visits for watchdog to custom table',
      'run_frequency' => 'Daily',
      'api_entity' => 'Nrm',
      'api_action' => 'Addvisit',
      'parameters' => '',
    ),
  ),
);
