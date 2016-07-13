<?php

require_once 'nrm_constants.php';

class CRM_Nrm_Form_Report_IndividualCounselor extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_phoneField = FALSE;

  protected $_logField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE;
  
  public static $_customFieldOptions = array();
  
  public static $_fieldLabels = array();
 
  function __construct() {
    $this->_drupalDatabase = 'chowan_drupal';
    self::getWebforms();
    self::createSurveyResponse();
    self::createCUVDRegistration();
    self::createPVDRegistration();
    self::createSOARRegistration();
    self::createInfoRequest();
    $counsellors = self::getCounsellors();

    $this->_columns = array(
      'civicrm_contact' => array(
        'dao' => 'CRM_Contact_DAO_Contact',
        'fields' => array(
          'display_name' => array(
            'title' => ts('Student Info'),
            'required' => TRUE,
            'default' => TRUE,
            'no_repeat' => TRUE,
          ),
        ),
        'filters' => array(
          'display_name' => array(
            'title' => ts('Student Name'),
            'operator' => 'like',
          ),
          'counsellor' => array(
            'title' => ts('Counsellor'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $counsellors,
          ),
          'id' => array(
            'title' => ts('Contact ID'),
            'name' => 'id',
            'operator' => CRM_Report_Form::OP_MULTISELECT,
            'options' => array(1),
            'no_display' => TRUE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_address' => array(
        'dao' => 'CRM_Core_DAO_Address',
        'fields' => array(
          'street_address' => array('default' => TRUE),
          'city' => array('default' => TRUE),
          'postal_code' => array('default' => TRUE),
          'state_province_id' => array('default' => TRUE),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_phone' => array(
        'dao' => 'CRM_Core_DAO_Phone',
        'fields' => array(
          'phone' => array('default' => TRUE)
        ),
        'grouping' => 'contact-fields',
      ),
      NRM_PRO => array(
        'dao' => 'CRM_Core_DAO_CustomField',
        'fields' => array(
          HIGH_SCHOOL => array(
            'title' => ts('High School'),
            'required' => TRUE,
            'default' => TRUE,
            'no_repeat' => TRUE,
          ),
          GRAD_YEAR => array(
            'title' => ts('Grad Year'),
            'required' => TRUE,
            'default' => TRUE,
            'no_repeat' => TRUE,
          ),
          MAJOR => array(
            'title' => ts('Major Interests'),
            'required' => TRUE,
            'default' => TRUE,
            'no_repeat' => TRUE,
          ),
        ),
        'filters' => array(
          HIGH_SCHOOL => array(
            'title' => ts('High School'),
            'operator' => 'like',
          ),
          MAJOR => array(
            'title' => ts('Major Interests'),
            'operator' => 'like',
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_email' => array(
        'dao' => 'CRM_Core_DAO_Email',
        'fields' => array(
          'email' => array('default' => TRUE)
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_log' => array(
        'dao' => 'CRM_Core_DAO_Log',
        'fields' => array(
          'modified_date' => array(
            'title' => ts('Last Updated'),
            'default' => TRUE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
      'civicrm_last_visit' => array(
        'dao' => 'CRM_Core_DAO_Log',
        'fields' => array(
          'completed' => array(
            'title' => ts('Previous Visit'),
            'default' => TRUE,
          ),
        ),
        'grouping' => 'contact-fields',
      ),
    );
    $this->_columns = array_merge($this->_columns, $this->surveyColumn);
    $this->_columns = array_merge($this->_columns, $this->cuvdColumn);
    $this->_columns = array_merge($this->_columns, $this->pvdColumn);
    $this->_columns = array_merge($this->_columns, $this->soarColumn);
    $this->_columns = array_merge($this->_columns, $this->infoColumn);
    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;
    $this->_aliases['civicrm_contact'] = 'contact_civireport';
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Daily Counselor Report'));
    parent::preProcess();
  }

  function select() {
    $select = $this->_columnHeaders = array();
    $cid = CID;

    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('fields', $table)) {
        foreach ($table['fields'] as $fieldName => $field) {
          if (CRM_Utils_Array::value('required', $field) ||
            CRM_Utils_Array::value($fieldName, $this->_params['fields'])
          ) {
            if ($tableName == 'civicrm_address') {
              $this->_addressField = TRUE;
              if ($fieldName == 'street_address') {
                $s = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', {$field['dbAlias']})";
              }
              if ($fieldName == 'city') {
                $c = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', {$field['dbAlias']})";
              }
              if ($fieldName == 'state_province_id') {
                $sp = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT(', ', csp.abbreviation))";
              }
              if ($fieldName == 'postal_code') {
                $p = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT(', ', {$field['dbAlias']}))";
              }
              if (isset($s) && isset($c) && isset($p) && isset($sp)) {
                $select[] = "CONCAT($s, '<br/>', $c, $sp, $p, '<br/>')";
              }
            }
            elseif ($tableName == 'civicrm_phone') {
              $this->_phoneField = TRUE;
              $select[] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT('Home: ', {$field['dbAlias']}, '<br/>'))";
            }
            elseif ($tableName == NRM_PRO) {
              if ($fieldName == HIGH_SCHOOL || $fieldName == MAJOR) {
                $this->_customNRMField = TRUE;
                $select[$field['dbAlias']] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT({$field['dbAlias']}, '::::{$field['field_id']}<br/>'))";
              }
              elseif ($fieldName == GRAD_YEAR) {
                $select[$field['dbAlias']] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT('{$field['title']}: ', {$field['dbAlias']}, '::::{$field['field_id']}<br/>'))";
              }
              elseif (array_key_exists($tableName, $this->infoColumn)) {
                $this->_infoField = TRUE;
                $infoFields[] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT({$field['dbAlias']}, '::::{$field['field_id']}<br/>'))";
                $this->customNRMField = "CONCAT(" . implode(', ', $infoFields) . ")";
                $nrmField = "{$this->customNRMField} as civicrm_contact_info_request";
              }
            }
            elseif ($tableName == 'civicrm_email') {
              $this->_emailField = TRUE;
              $select[] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT({$field['dbAlias']}, '<br/>'))";
            }
            elseif ($tableName == 'civicrm_log') {
              $this->_logField = TRUE;
              $logSelect = "DATE_FORMAT(MAX({$field['dbAlias']}), '%m/%d/%Y') as civicrm_contact_last_update,";
            }
            elseif ($tableName == 'civicrm_last_visit') {
              $this->_visitedField = TRUE;
              $visitedSelect = "DATE_FORMAT(FROM_UNIXTIME(MAX(CASE WHEN DATE(FROM_UNIXTIME(cvt.visit_time)) <> DATE_SUB(DATE(NOW()), INTERVAL 1 day) THEN cvt.visit_time ELSE NULL END)), '%m/%d/%Y')
                as civicrm_contact_last_visited,";
            }
            elseif (array_key_exists($tableName, $this->surveyColumn)) {
              $this->_surveyField = TRUE;
              $surveyFields[] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT({$field['dbAlias']}, '<br/>'))";
              $this->customSurveyField = "CONCAT(" . implode(', ', $surveyFields) . ")";
              $surveyField = "{$this->customSurveyField} as civicrm_contact_survey_response,";
            }
            elseif (array_key_exists($tableName, $this->cuvdColumn)) {
              $this->_cuvdField = TRUE;
              if ($field['is_alias'] == TRUE) {
                if ($field['is_select'] == TRUE) {
                  $cuvdFields[] = "CONCAT('{$field['title']}', ': ', {$field['field_name']}, '>>>>{$field['cid']}<br/>')";
                }
                $cuvdFields[] = "CONCAT('{$field['title']}', ': ', {$field['field_name']}, '<br/>')";
              }
              else {
                $cuvdFields[] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT({$field['dbAlias']}, '::::{$field['field_id']}<br/>'))";
              }
              $this->customCUVDField = "CONCAT(" . implode(', ', $cuvdFields) . ")";
              $cuvdField = "{$this->customCUVDField} as civicrm_contact_cuvd_registration,";
            }
            elseif (array_key_exists($tableName, $this->pvdColumn)) {
              $this->_pvdField = TRUE;
              $pvdFields[] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT({$field['dbAlias']}, '::::{$field['field_id']}<br/>'))";
              $this->customPVDField = "CONCAT(" . implode(', ', $pvdFields) . ")";
              $pvdField = "{$this->customPVDField} as civicrm_contact_pvd_registration,";
            }
            elseif (array_key_exists($tableName, $this->soarColumn)) {
              $this->_soarField = TRUE;
              if ($field['is_alias'] == TRUE) {
                if ($field['is_select'] == TRUE) {
                  $soarFields[] = "CONCAT('{$field['title']}', ': ', {$field['field_name']}, '>>>>{$field['cid']}<br/>')";
                }
                $soarFields[] = "CONCAT('{$field['title']}', ': ', {$field['field_name']}, '<br/>')";
              }
              }
              else {
                $soarFields[] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT({$field['dbAlias']}, '::::{$field['field_id']}<br/>'))";
              }
              $this->customSOARField = "CONCAT(" . implode(', ', $soarFields) . ")";
              $soarField = "{$this->customSOARField} as civicrm_contact_soar_registration,";
            }
            else {
              $select[] = "{$field['dbAlias']}";
              $select[] = "'<br/>'";
            }
          }
        }
      }
    }

    $this->_select = "SELECT {$this->_aliases['civicrm_contact']}.id as civicrm_contact_contact_id, CONCAT(" . implode(', ', $select) . ") as civicrm_contact_display_name,
      t.first_visit as civicrm_contact_first_visit,
      {$logSelect}
      {$visitedSelect}
      {$surveyField}
      {$cuvdField}
      {$pvdField}
      {$soarField}
      {$nrmField}";
    $this->_columnHeaders["civicrm_contact_contact_id"]['title'] = ts('Contact ID');
    $this->_columnHeaders["civicrm_contact_display_name"]['title'] = $this->_columns["civicrm_contact"]['fields']['display_name']['title'];
    $this->_columnHeaders["civicrm_contact_first_visit"]['title'] = ts('First Visit');
    if ($this->_logField) {
      $this->_columnHeaders["civicrm_contact_last_update"]['title'] = ts('Last Update');
    }
    if ($this->_visitedField) {
      $this->_columnHeaders["civicrm_contact_last_visited"]['title'] = ts('Previous Visit');
    }
    $this->_columnHeaders["civicrm_contact_survey_response"]['title'] = ts('Survey Responses');
    $this->_columnHeaders["civicrm_contact_cuvd_registration"]['title'] = ts('CUVD Registrations');
    $this->_columnHeaders["civicrm_contact_pvd_registration"]['title'] = ts('PVD Registrations');
    $this->_columnHeaders["civicrm_contact_soar_registration"]['title'] = ts('SOAR Registrations');
  }

  function from() {
    $this->_drupalDatabase = 'chowan_drupal';

    $this->_from = NULL;

    $this->_from = "
         FROM  civicrm_contact {$this->_aliases['civicrm_contact']} ";

    // For first visit times
    $this->_from .= "
             INNER JOIN civicrm_watchdog_temp_b t
                       ON t.contact_id = {$this->_aliases['civicrm_contact']}.id\n";

    $this->_from .= "{$this->surveyTables}";

    $this->_from .= "{$this->nrmTables}";

    $this->_from .= "{$this->cuvdTables}";

    $this->_from .= "{$this->pvdTables}";

    $this->_from .= "{$this->soarTables}";
    

    $this->_from .= "
        LEFT JOIN {$this->_drupalDatabase}.webform_submitted_data wsd1 ON wsd1.data = contact_civireport.id
        LEFT JOIN {$this->_drupalDatabase}.webform_component wc ON wc.nid = wsd1.nid AND wc.cid = wsd1.cid AND wc.name = 'Contact ID'
        LEFT JOIN {$this->_drupalDatabase}.webform_submitted_data wsd ON wsd1.sid = wsd.sid";

    if ($this->_params['fields']['wsd2.data'] == 1) {
      $this->_from .= " LEFT JOIN {$this->_drupalDatabase}.webform_submitted_data wsd2
        ON wsd2.sid = wsd.sid and wsd2.cid = 34 
        LEFT JOIN civicrm_event ce ON ce.id = SUBSTRING_INDEX(wsd2.data, '-', 1)";
    }

    if ($this->_params['fields']['wsd3.data'] == 1) {
      $this->_from .= " LEFT JOIN {$this->_drupalDatabase}.webform_submitted_data wsd3
        ON wsd3.sid = wsd.sid and wsd3.cid = 38";
    }
    
    if ($this->_params['fields']['wsd4.data'] == 1) {
      $this->_from .= " LEFT JOIN {$this->_drupalDatabase}.webform_submitted_data wsd4
        ON wsd4.sid = wsd.sid and wsd4.cid = 39";
    }
    
    if ($this->_params['fields']['wsd5.data'] == 1) {
      $this->_from .= " LEFT JOIN {$this->_drupalDatabase}.webform_submitted_data wsd5
        ON wsd5.sid = wsd.sid and wsd4.cid = 42";
    }
    
    if ($this->_params['fields']['wsd6.data'] == 1) {
      $this->_from .= " LEFT JOIN {$this->_drupalDatabase}.webform_submitted_data wsd6
        ON wsd6.sid = wsd.sid and wsd4.cid = 43";
    } 

    if ($this->_params['fields']['wsd7.data'] == 1) {
      $this->_from .= " LEFT JOIN {$this->_drupalDatabase}.webform_submitted_data wsd7
        ON wsd7.sid = wsd.sid and wsd7.cid = 26 
        LEFT JOIN civicrm_event ce2 ON ce2.id = SUBSTRING_INDEX(wsd7.data, '-', 1)";
    }
    
    //used when address field is selected
    if ($this->_addressField) {
      $this->_from .= "
             LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
                       ON {$this->_aliases['civicrm_contact']}.id =
                          {$this->_aliases['civicrm_address']}.contact_id AND
                          {$this->_aliases['civicrm_address']}.is_primary = 1
             LEFT JOIN civicrm_state_province csp
                       ON {$this->_aliases['civicrm_address']}.state_province_id = csp.id\n";
    }
    //used when email field is selected
    if ($this->_emailField) {
      $this->_from .= "
              LEFT JOIN civicrm_email {$this->_aliases['civicrm_email']}
                        ON {$this->_aliases['civicrm_contact']}.id =
                           {$this->_aliases['civicrm_email']}.contact_id AND
                           {$this->_aliases['civicrm_email']}.is_primary = 1\n";
    }
    //used when phone field is selected
    if ($this->_phoneField) {
      $this->_from .= "
              LEFT JOIN civicrm_phone {$this->_aliases['civicrm_phone']}
                        ON {$this->_aliases['civicrm_contact']}.id =
                           {$this->_aliases['civicrm_phone']}.contact_id AND
                           {$this->_aliases['civicrm_phone']}.is_primary = 1\n";
    }
    //used when log field is selected
    if ($this->_logField) {
      $this->_from .= "
              LEFT JOIN civicrm_log {$this->_aliases['civicrm_log']}
                        ON {$this->_aliases['civicrm_contact']}.id =
                           {$this->_aliases['civicrm_log']}.entity_id AND
                           {$this->_aliases['civicrm_log']}.entity_table = 'civicrm_contact'\n";
    }
    if ($this->_visitedField) {
      $this->_from .= "
              LEFT JOIN civicrm_visit_times cvt 
                        ON {$this->_aliases['civicrm_contact']}.id =
                           cvt.contact_id\n";   
    }
    //used when log field is selected
    if ($this->_customNRMField) {
      $this->_from .= "
              LEFT JOIN ". NRM_PRO . " value_nrmlayer_6_civireport
                        ON {$this->_aliases['civicrm_contact']}.id = value_nrmlayer_6_civireport.entity_id\n";
    }
  }

  function where() {
    $clauses = array();
    foreach ($this->_columns as $tableName => $table) {
      if (array_key_exists('filters', $table)) {
        foreach ($table['filters'] as $fieldName => $field) {
          $clause = NULL;
          if (CRM_Utils_Array::value('operatorType', $field) & CRM_Utils_Type::T_DATE) {
            $relative = CRM_Utils_Array::value("{$fieldName}_relative", $this->_params);
            $from     = CRM_Utils_Array::value("{$fieldName}_from", $this->_params);
            $to       = CRM_Utils_Array::value("{$fieldName}_to", $this->_params);

            $clause = $this->dateClause($field['name'], $relative, $from, $to, $field['type']);
          }
          else {
            if ($fieldName == 'counsellor' || strpos($fieldName, 'wsd')) {
              continue;
            }
            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
            if ($op) {
              $clause = $this->whereClause($field,
                $op,
                CRM_Utils_Array::value("{$fieldName}_value", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_min", $this->_params),
                CRM_Utils_Array::value("{$fieldName}_max", $this->_params)
              );
            }
          }

          if (!empty($clause)) {
            $clauses[] = $clause;
          }
        }
      }
    }
    
    if (($counsellor = CRM_Utils_Array::value("counsellor_value", $this->_params)) || $counsellor = $_GET['counsellor_id_value']) {
      if (is_array($counsellor)) {
        $counsellor = implode(',', $counsellor);
      }
      $sql = CRM_Core_DAO::singleValueQuery("SELECT TRIM(TRAILING \",'\" FROM (TRIM(LEADING \"',\" FROM (REPLACE(t26.admissions_territory_459, '" . CRM_Core_DAO::VALUE_SEPARATOR . "', \"','\")))))
        FROM civicrm_value_territory_26 t26 WHERE t26.entity_id IN (" . $counsellor . ")");
      $clauses[] = " (value_nrmlayer_6_civireport.territory_147 IN ({$sql}))";
    }

    if (empty($clauses)) {
      $this->_where = "WHERE ( 1 ) ";
    }
    else {
      $this->_where = "WHERE " . implode(' AND ', $clauses);
    }

    if ($this->_aclWhere) {
      $this->_where .= " AND {$this->_aclWhere} ";
    }
  }

  function groupBy() {
    $this->_groupBy = " GROUP BY {$this->_aliases['civicrm_contact']}.id";
  }

  function orderBy() {
    $this->_orderBy = " ORDER BY {$this->_aliases['civicrm_contact']}.display_name, {$this->_aliases['civicrm_contact']}.id";
  }

  function postProcess() {

    $this->beginPostProcess();

    // get the acl clauses built before we assemble the query
    $this->buildACLClause($this->_aliases['civicrm_contact']);
    CRM_Nrm_BAO_Nrm::updateWatchdog_nrm();
    self::createTemp();
    $sql = $this->buildQuery(TRUE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS civicrm_watchdog_temp_a");
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS civicrm_watchdog_temp_b");
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS civicrm_watchdog_temp_c");
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS civicrm_visit_times");
  }
  
  function createTemp() {
    $microsite = MICROSITE;
    $sql = "CREATE TEMPORARY TABLE civicrm_watchdog_temp_a AS
            SELECT DISTINCT w.* FROM (
              SELECT wid, SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '.', 1) as purl, 
              DATE_FORMAT(DATE(FROM_UNIXTIME(MIN(timestamp))),'%m/%d/%Y') as first_visit
              FROM {$this->_drupalDatabase}.watchdog_nrm
              GROUP BY SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '.', 1)
              ) AS w INNER JOIN (
              SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '.', 1) as purl 
              FROM {$this->_drupalDatabase}.watchdog_nrm
              WHERE DATE(FROM_UNIXTIME(timestamp)) = DATE_SUB(DATE(NOW()), INTERVAL 1 day)) as wy 
            ON w.purl=wy.purl";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $sql = "ALTER TABLE civicrm_watchdog_temp_a ADD INDEX idx_purl (purl(255)) USING HASH";
    $dao = CRM_Core_DAO::executeQuery($sql);
    
    $sql = "CREATE TEMPORARY TABLE civicrm_watchdog_temp_b AS
      SELECT {$this->_aliases['civicrm_contact']}.id as contact_id, p.purl_145, first_visit
      FROM civicrm_contact {$this->_aliases['civicrm_contact']}
      INNER JOIN civicrm_value_nrmpurls_5 p ON {$this->_aliases['civicrm_contact']}.id = p.entity_id
      INNER JOIN civicrm_watchdog_temp_a w ON w.purl = p.purl_145 COLLATE utf8_general_ci";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $sql = "ALTER TABLE civicrm_watchdog_temp_b ADD INDEX idx_purl (purl_145(255)) USING HASH, ADD INDEX idx_c_id (contact_id) USING HASH";
    $dao = CRM_Core_DAO::executeQuery($sql);

    $sql = "CREATE TEMPORARY TABLE civicrm_visit_times AS 
      SELECT wsd.data as contact_id, ws.completed as visit_time
      FROM {$this->_drupalDatabase}.webform_submitted_data wsd    
      INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = wsd.cid AND c.name = 'Contact ID' and wsd.nid = c.nid
      INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = wsd.nid AND wsd.sid = ws.sid
      GROUP BY wsd.sid
      UNION
      SELECT p.entity_id as contact_id, w.timestamp as visit_time
      FROM {$this->_drupalDatabase}.watchdog_nrm w
      LEFT JOIN civicrm_value_nrmpurls_5 p ON REPLACE(w.purl, '.{$microsite}', '') COLLATE utf8_unicode_ci = p.purl_145
      WHERE w.purl <> '{$microsite}'
      GROUP BY w.location ";
    $dao = CRM_Core_DAO::executeQuery($sql);
  }
  
  function createSurveyResponse() {
    $sql = "SELECT g.id as group_id, g.table_name, c.column_name, c.label
      FROM civicrm_custom_group g 
      LEFT JOIN civicrm_custom_field c ON c.custom_group_id = g.id 
      WHERE title LIKE '%Survey%'";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $fieldAlias = 'group_' . $dao->group_id;
      $field =  $fieldAlias . '.' . $dao->column_name;
      $tables[$dao->group_id] = " LEFT JOIN {$dao->table_name} {$fieldAlias} ON {$fieldAlias}.entity_id = contact_civireport.id ";
      $this->surveyColumn[$dao->table_name]['fields'][$dao->column_name] = array(
        'title' => $dao->label,
        'default' => TRUE,
        'dbAlias' => $fieldAlias . '.' . $dao->column_name,
      );
      $this->surveyColumn[$dao->table_name]['use_accordian_for_field_selection'] = TRUE;
      $this->surveyColumn[$dao->table_name]['group_title'] = ts('Survey Information');
    }
    $this->surveyTables = implode(' ', $tables);
  }

  function createInfoRequest() {
    $sql = "SELECT c.id as field_id, g.id as group_id, g.table_name, c.column_name, c.label
      FROM civicrm_custom_group g 
      LEFT JOIN civicrm_custom_field c ON c.custom_group_id = g.id 
      WHERE title LIKE '%NRM%'";
    $dao = CRM_Core_DAO::executeQuery($sql);
    
    while ($dao->fetch()) {
      $fieldAlias = 'group_' . $dao->group_id;
      $field =  $fieldAlias . '.' . $dao->column_name;
      $tables[$dao->group_id] = " LEFT JOIN {$dao->table_name} {$fieldAlias} ON {$fieldAlias}.entity_id = contact_civireport.id ";
      $this->infoColumn[$dao->table_name]['fields'][$dao->column_name] = array(
        'title' => $dao->label,
        'dbAlias' => $fieldAlias . '.' . $dao->column_name,
        'field_id' => $dao->field_id,
      );
      if (in_array($dao->label, array('Activity Interests', 'Major Interests', 'Athletic Interests'))) {
        $this->infoColumn[$dao->table_name]['fields'][$dao->column_name]['default'] = TRUE;
      }
      $this->infoColumn[$dao->table_name]['use_accordian_for_field_selection'] = TRUE;
      $this->infoColumn[$dao->table_name]['group_title'] = ts('Information Requests & Downloads');
    }
    $this->nrmTables = implode(' ', $tables);
  }

  function createCUVDRegistration() {
    $sql = "SELECT c.id as field_id, g.id as group_id, g.table_name, c.column_name, c.label
      FROM civicrm_custom_group g 
      LEFT JOIN civicrm_custom_field c ON c.custom_group_id = g.id 
      WHERE g.id IN (6,8)";
    $dao = CRM_Core_DAO::executeQuery($sql);
    
    while ($dao->fetch()) {
      $fieldAlias = 'cgroup_' . $dao->group_id;
      $field =  $fieldAlias . '.' . $dao->column_name;
      $tables[$dao->group_id] = " LEFT JOIN {$dao->table_name} {$fieldAlias} ON {$fieldAlias}.entity_id = contact_civireport.id ";
      $this->cuvdColumn[$dao->table_name]['fields'][$fieldAlias . $dao->column_name] = array(
        'title' => $dao->label,
        'dbAlias' => $fieldAlias . '.' . $dao->column_name,
        'field_id' => $dao->field_id,
        'default' => TRUE,
      );
      $this->cuvdColumn[$dao->table_name]['use_accordian_for_field_selection'] = TRUE;
      $this->cuvdColumn[$dao->table_name]['group_title'] = ts('CU Visit Day Registrations');
      $this->cuvdColumn[$dao->table_name]['fields']['wsd2.data'] = array(
        'title' => 'Which CU Visit Day will you be attending?',
        'dbAlias' => 'wsd2.data',
        'is_alias' => TRUE,
        'default' => TRUE,
        'field_name' => 'ce.title',
      );
      $this->cuvdColumn[$dao->table_name]['fields']['wsd3.data'] = array(
        'title' => 'Anticipated Academic Enroll Term',
        'dbAlias' => 'wsd3.data',
        'is_alias' => TRUE,
        'default' => TRUE,
        'cid' => 38,
        'is_select' => TRUE,
        'field_name' => 'wsd3.data',
      );
      $this->cuvdColumn[$dao->table_name]['fields']['wsd4.data'] = array(
        'title' => 'Anticipated Academic Enroll Year',
        'dbAlias' => 'wsd4.data',
        'is_alias' => TRUE,
        'default' => TRUE,
        'is_select' => TRUE,
        'cid' => 39,
        'field_name' => 'wsd4.data',
      );
      $this->cuvdColumn[$dao->table_name]['fields']['wsd5.data'] = array(
        'title' => 'How did you hear about Chowan?',
        'dbAlias' => 'wsd5.data',
        'is_alias' => TRUE,
        'default' => TRUE,
        'is_select' => TRUE,
        'cid' => 42,
        'field_name' => 'wsd5.data',
      );
      $this->cuvdColumn[$dao->table_name]['fields']['wsd6.data'] = array(
        'title' => 'How did you hear about CU Visit Day?',
        'dbAlias' => 'wsd6.data',
        'is_alias' => TRUE,
        'default' => TRUE,
        'is_select' => TRUE,
        'cid' => 43,
        'field_name' => 'wsd6.data',
      );
    }
    $this->cuvdTables = implode(' ', $tables);
  }

  function createPVDRegistration() {
    $sql = "SELECT c.id as field_id, g.id as group_id, g.table_name, c.column_name, c.label
      FROM civicrm_custom_group g 
      LEFT JOIN civicrm_custom_field c ON c.custom_group_id = g.id 
      WHERE g.id IN (6,11)";
    $dao = CRM_Core_DAO::executeQuery($sql);
    
    while ($dao->fetch()) {
      $fieldAlias = 'vgroup_' . $dao->group_id;
      $field =  $fieldAlias . '.' . $dao->column_name;
      $tables[$dao->group_id] = " LEFT JOIN {$dao->table_name} {$fieldAlias} ON {$fieldAlias}.entity_id = contact_civireport.id ";
      $this->pvdColumn[$dao->table_name]['fields'][$fieldAlias . $dao->column_name] = array(
        'title' => $dao->label,
        'dbAlias' => $fieldAlias . '.' . $dao->column_name,
        'field_id' => $dao->field_id,
        'default' => TRUE,
      );
      $this->pvdColumn[$dao->table_name]['use_accordian_for_field_selection'] = TRUE;
      $this->pvdColumn[$dao->table_name]['group_title'] = ts('Personal Visit Day Registrations');
    }
    $this->pvdTables = implode(' ', $tables);
  }

  function createSOARRegistration() {
    $sql = "SELECT c.id as field_id, g.id as group_id, g.table_name, c.column_name, c.label
      FROM civicrm_custom_group g 
      LEFT JOIN civicrm_custom_field c ON c.custom_group_id = g.id 
      WHERE g.id IN (6)";
    $dao = CRM_Core_DAO::executeQuery($sql);
    
    while ($dao->fetch()) {
      $fieldAlias = 'sgroup_' . $dao->group_id;
      $field =  $fieldAlias . '.' . $dao->column_name;
      $tables[$dao->group_id] = " LEFT JOIN {$dao->table_name} {$fieldAlias} ON {$fieldAlias}.entity_id = contact_civireport.id ";
      $this->soarColumn[$dao->table_name]['fields'][$fieldAlias . $dao->column_name] = array(
        'title' => $dao->label,
        'dbAlias' => $fieldAlias . '.' . $dao->column_name,
        'field_id' => $dao->field_id,
        'default' => TRUE,
      );
      $this->soarColumn[$dao->table_name]['use_accordian_for_field_selection'] = TRUE;
      $this->soarColumn[$dao->table_name]['group_title'] = ts('SOAR Registrations');
      $this->cuvdColumn[$dao->table_name]['fields']['wsd7.data'] = array(
        'title' => 'Which SOAR event would you like to attend?',
        'dbAlias' => 'wsd7.data',
        'is_alias' => TRUE,
        'default' => TRUE,
        'field_name' => 'ce2.title',
      );
    }
    $this->soarTables = implode(' ', $tables);
  }

  function getWebforms() {
    $this->webForms = array();

    $sql = "SELECT w.nid, n.title
      FROM {$this->_drupalDatabase}.webform w 
      INNER JOIN {$this->_drupalDatabase}.node n ON n.nid = w.nid";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $this->webForms[$dao->nid] = $dao->title;
    }
  }

  function alterDisplay(&$rows) {
    // custom code to alter rows
    $entryFound = FALSE;
    $checkList = array();
    foreach ($rows as $rowNum => $row) {

      if (array_key_exists('civicrm_contact_display_name', $row)) {
        $rows[$rowNum]['civicrm_contact_display_name'] = self::getCustomFieldDataLabels($row['civicrm_contact_display_name']);
        $rows[$rowNum]['civicrm_contact_display_name'] = str_replace("<br/>", "<br/>\n", $rows[$rowNum]['civicrm_contact_display_name']);
        $entryFound = TRUE;
      }
      
      if (array_key_exists('civicrm_contact_survey_response', $row)) {
        $validNids = array(128,131,287,288);
        $dao = self::hideInvalidRows($row['civicrm_contact_contact_id'], $validNids);
        if (!$dao->N) {
          $rows[$rowNum]['civicrm_contact_survey_response'] = NULL;
        }
        else {
          $where = "form_key LIKE '%cg20%'";
          $rows[$rowNum]['civicrm_contact_survey_response'] = self::getLabels($where, $separator = '<br/>', $row['civicrm_contact_survey_response']);
          $rows[$rowNum]['civicrm_contact_survey_response'] = str_replace("<br/>", "<br/>\n", $rows[$rowNum]['civicrm_contact_survey_response']);
          $entryFound = TRUE;
        }
      }
      
      if (array_key_exists('civicrm_contact_vip_application', $row)) {
        $validNids = array(70);
        $dao = self::hideInvalidRows($row['civicrm_contact_contact_id'], $validNids);
        if (!$dao->N) {
          $rows[$rowNum]['civicrm_contact_vip_application'] = NULL;
        }
        else {
          $rows[$rowNum]['civicrm_contact_vip_application'] = self::getCustomFieldDataLabels($row['civicrm_contact_vip_application']);
          $rows[$rowNum]['civicrm_contact_vip_application'] = str_replace("<br/>", "<br/>\n", $rows[$rowNum]['civicrm_contact_vip_application']);
          $entryFound = TRUE;
        }
      }
      
      if (array_key_exists('civicrm_contact_visit_registration', $row)) {
        $validNids = array(71,89);
        $dao = self::hideInvalidRows($row['civicrm_contact_contact_id'], $validNids);
        if (!$dao->N) {
          $rows[$rowNum]['civicrm_contact_visit_registration'] = NULL;
        }
        else {
          $rows[$rowNum]['civicrm_contact_visit_registration'] = self::getCustomFieldDataLabels($row['civicrm_contact_visit_registration']);
          $rows[$rowNum]['civicrm_contact_visit_registration'] = str_replace("<br/>", "<br/>\n", $rows[$rowNum]['civicrm_contact_visit_registration']);
          $entryFound = TRUE;
        }
      }
      
      if (array_key_exists('civicrm_contact_cuvd_registration', $row)) {
        $validNids = array(299);
        $dao = self::hideInvalidRows($row['civicrm_contact_contact_id'], $validNids);
        if (!$dao->N) {
          $rows[$rowNum]['civicrm_contact_cuvd_registration'] = NULL;
        }
        else {
          $rows[$rowNum]['civicrm_contact_cuvd_registration'] = self::getCustomFieldDataLabels($row['civicrm_contact_cuvd_registration']);
          $rows[$rowNum]['civicrm_contact_cuvd_registration'] = str_replace("<br/>", "<br/>\n", $rows[$rowNum]['civicrm_contact_cuvd_registration']);
          $entryFound = TRUE;
        }
      }
      
      if (array_key_exists('civicrm_contact_pvd_registration', $row)) {
        $validNids = array(302);
        $dao = self::hideInvalidRows($row['civicrm_contact_contact_id'], $validNids);
        if (!$dao->N) {
          $rows[$rowNum]['civicrm_contact_pvd_registration'] = NULL;
        }
        else {
          $rows[$rowNum]['civicrm_contact_pvd_registration'] = self::getCustomFieldDataLabels($row['civicrm_contact_pvd_registration']);
          $rows[$rowNum]['civicrm_contact_pvd_registration'] = str_replace("<br/>", "<br/>\n", $rows[$rowNum]['civicrm_contact_pvd_registration']);
          $entryFound = TRUE;
        }
      }
      
      if (array_key_exists('civicrm_contact_soar_registration', $row)) {
        $validNids = array(298);
        $dao = self::hideInvalidRows($row['civicrm_contact_contact_id'], $validNids);
        if (!$dao->N) {
          $rows[$rowNum]['civicrm_contact_soar_registration'] = NULL;
        }
        else {
          $rows[$rowNum]['civicrm_contact_soar_registration'] = self::getCustomFieldDataLabels($row['civicrm_contact_soar_registration']);
          $rows[$rowNum]['civicrm_contact_soar_registration'] = str_replace("<br/>", "<br/>\n", $rows[$rowNum]['civicrm_contact_soar_registration']);
          $entryFound = TRUE;
        }
      }

      if (CRM_Utils_Array::value('civicrm_contact_info_request', $row)) {
        $validNids = array(72);
        $dao = self::hideInvalidRows($row['civicrm_contact_contact_id'], $validNids);
        if (!$dao->N) {
          $rows[$rowNum]['civicrm_contact_info_request'] = NULL;
        }
        else {
          $rows[$rowNum]['civicrm_contact_info_request'] = self::getCustomFieldDataLabels($row['civicrm_contact_info_request']);
        }
        $purl = CRM_Core_DAO::singleValueQuery("SELECT CONCAT(\"'%\", purl_145 ,\".%'\") FROM civicrm_value_nrmpurls_5 WHERE entity_id = {$row['civicrm_contact_contact_id']}");
        if ($purl) {
          $string = '';
          $sql = "SELECT location FROM {$this->_drupalDatabase}.watchdog_nrm
            WHERE location LIKE {$purl}
            AND location LIKE '%.pdf%'
            AND DATE(FROM_UNIXTIME(timestamp)) = DATE_SUB(DATE(NOW()), INTERVAL 1 day)";
          $dao = CRM_Core_DAO::executeQuery($sql);
          if ($dao->N) {
            $string = "<br/><hr><b>Downloads:</b><br/>";
          }
          while ($dao->fetch()) {
            $string .= urldecode(basename($dao->location)) . "<br/>";
          }
          $rows[$rowNum]['civicrm_contact_info_request'] = $rows[$rowNum]['civicrm_contact_info_request'] . $string;
          $rows[$rowNum]['civicrm_contact_info_request'] = str_replace("<br/>", "<br/>\n", $rows[$rowNum]['civicrm_contact_info_request']);
        }
        $entryFound = TRUE;
      }
      unset($this->_columnHeaders["civicrm_contact_contact_id"]);
      if (!$entryFound) {
        break;
      }
    }
  }
  
  function hideInvalidRows($cid, $validNids) {
    $validNids = implode(',', $validNids);
    $sql = "SELECT ws.sid from {$this->_drupalDatabase}.webform_submissions ws
      LEFT JOIN {$this->_drupalDatabase}.webform_component wc ON wc.nid = ws.nid AND wc.name = 'Contact ID'
      LEFT JOIN {$this->_drupalDatabase}.webform_submitted_data wsd ON wsd.sid = ws.sid AND wsd.nid = ws.nid AND wsd.cid = wc.cid
      WHERE DATE(FROM_UNIXTIME(ws.completed)) = DATE_SUB(DATE(NOW()), INTERVAL 1 day)
      AND wsd.data = {$cid} AND ws.nid IN ({$validNids})
      GROUP BY ws.sid";
        
    return CRM_Core_DAO::executeQuery($sql);
  }

  function getLabels($where, $separator, $row) {
    $newArray = $webform = array();
    $cacheKey = CRM_Utils_String::munge($where);
    if (empty(self::$_fieldLabels[$cacheKey])) {
      $sql = "SELECT nid, extra, name
          FROM {$this->_drupalDatabase}.webform_component
          WHERE $where AND type = 'select'";
      $dao = CRM_Core_DAO::executeQuery($sql);
      while ($dao->fetch()) {
        $items = unserialize($dao->extra);
        if (CRM_Utils_Array::value('items', $items)) {
          $webform[$dao->name] = array_filter(explode("\n", $items['items']));
        }
      }
      self::$_fieldLabels[$cacheKey] = array();
      foreach ($webform as $key => $d) {
        foreach ($d as $data) {
          list($k, $v) = explode('|', $data);
          self::$_fieldLabels[$cacheKey][$k] = array($key, $v);
        }
      }
    }
    $op = array_filter(explode($separator, $row));
    foreach($op as $values) {
      $values = trim($values, CRM_Core_DAO::VALUE_SEPARATOR);
      if (isset(self::$_fieldLabels[$cacheKey][$values])) {
        $newArray[] = self::$_fieldLabels[$cacheKey][$values][0] . ': ' . self::$_fieldLabels[$cacheKey][$values][1];
      }
      else {
        $newArray[] = $values;
      }
    }
    return implode('<br/>', $newArray);
  }
  
  public static function getCounsellors() {
    $counsellors = array();
    $counsellorCount = civicrm_api3('Contact', 'getCount', array('contact_sub_type' => 'Counselors'));
    $counselorParams = array(
      'contact_sub_type' => 'Counselors',
      'return.email' => 1,
      'return.custom_' . TERRITORY_COUNSELOR => 1,
      'return.display_name' => 1,
      'rowCount' => $counsellorCount,
    );
    $counselors = civicrm_api3('Contact', 'get', $counselorParams);
    if ($counselors['count'] >= 1) {
      $counselors = $counselors['values'];
      foreach ($counselors as $key => $value) {
        if (!empty($value['custom_' . TERRITORY_COUNSELOR])) {
          $counsellors[$value['contact_id']] = $value['display_name'];
        }
      }
    }
    return $counsellors;
  }
  
  public static function getCustomFieldDataLabels($data) {
    if (empty($data)) {
      return $data;
    }
    $tempArray =  array();
    $data = explode('<br/>', $data);
    foreach ($data as $value) {
      if (empty($value)) {
        continue;
      }
      $select = explode('>>>>', $value);
      if (CRM_Utils_Array::value(1, $select)) {
        if ($select[1] == 38) {
          $extra = array(1 => 'Fall', 2 => 'Spring', 3 => 'Summer');
        }
        elseif ($select[1] == 39) {
          $extra = array(2 => 2016, 3 => 2017, 4 => 2018);
        }
        elseif (in_array($select[1], array(42,43))) {
          $extra = array(
            1 => 'Chowan Faculty',
            2 => 'Chowan Website',
            3 => 'Church',
            4 => 'CIAA',
            5 => 'Coach',
            6 => 'College Fair',
            7 => 'College Foundation of NC',
            8 => 'Direct Mail',
            9 => 'E-mail',
            10 => 'Friend',
            11 => 'High School Guidance Counselor',
            12 => 'High School Visit',
            13 => 'Internet Search',
            14 => 'Personal Website',
            15 => 'Postcard',
            16 => 'Relative',
            17 => 'Telephone Call',
            18 => 'Other',
          );
        }
        $text = explode(': ', $select[0]);
        $replaced = $extra[$text[1]];
        $tempArray[] = $text[0] . ': ' . $replaced;
      }
      else {
        $value = explode('::::', $value);
        if (CRM_Utils_Array::value(1, $value)) {
          if (empty(self::$_customFieldOptions[$value[1]])) {
            $result = civicrm_api3('CustomField', 'getsingle', array(
              'sequential' => 1,
              'id' => $value[1],
            ));
            $options[$value[1]] = array();
            $options[$value[1]] = CRM_Core_PseudoConstant::get('CRM_Core_BAO_CustomField', 'custom_' . $value[1], array(), 'get');
            self::$_customFieldOptions[$value[1]] = array($result['label'], $options);
          }
          $tempArray[] = self::$_customFieldOptions[$value[1]][0] . ': ' . CRM_Core_BAO_CustomField::displayValue($value[0], $value[1], self::$_customFieldOptions[$value[1]][1]);
        }
        elseif (!$replaced) {
          $tempArray[] = $value[0];
        }
      }
    }
    return implode('<br/>', $tempArray);
  }
}