<?php
// This file declares a managed database record of type "ReportTemplate".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'CRM_Nrm_Form_Report_Admission',
    'entity' => 'ReportTemplate',
    'params' => 
    array (
      'version' => 3,
      'label' => 'Admission Daily Report',
      'description' => 'Admission Daily Report (biz.jmaconsulting.nrm)',
      'class_name' => 'CRM_Nrm_Form_Report_Admission',
      'report_url' => 'brevard/admission',
      'component' => '',
    ),
  ),
);