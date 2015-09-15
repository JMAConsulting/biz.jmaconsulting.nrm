<?php

require_once 'yoteup_constants.php';

class CRM_Yoteup_Form_Report_IndividualCounseller extends CRM_Report_Form {

  protected $_addressField = FALSE;

  protected $_emailField = FALSE;

  protected $_phoneField = FALSE;

  protected $_logField = FALSE;

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE;
 
  function __construct() {
    $config = CRM_Core_Config::singleton();
    $dsnArray = DB::parseDSN($config->userFrameworkDSN);
    $this->_drupalDatabase = $dsnArray['database'];
    self::getWebforms();

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
          'id' => array(
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
    );
    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Individual Counsellor Report'));
    parent::preProcess();
  }

  function select() {
    $select = $this->_columnHeaders = array();

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
              if ($fieldName == 'postal_code') {
                $p = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT(', ', {$field['dbAlias']}))";
              }
              if (isset($s) && isset($c) && isset($p)) {
                $select[] = "CONCAT($s, '<br/>', $c, $p)";
                $select[] = "'<br/>'";
              }
            }
            elseif ($tableName == 'civicrm_phone') {
              $this->_phoneField = TRUE;
              $select[] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT('Home: ', {$field['dbAlias']}))";
              $select[] = "'<br/>'";
            }
            elseif ($tableName == NRM_PRO) {
              $this->_customNRMField = TRUE;
              $select[$field['dbAlias']] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', CONCAT('{$field['title']}: ', {$field['dbAlias']}))";
              $select[] = "'<br/>'";
            }
            elseif ($tableName == 'civicrm_email') {
              $this->_emailField = TRUE;
              $select[] = "IF({$field['dbAlias']} IS NULL or {$field['dbAlias']} = '', '', {$field['dbAlias']})";
              $select[] = "'<br/>'";
            }
            elseif ($tableName == 'civicrm_log') {
              $this->_logField = TRUE;
              $logSelect = "MAX(DATE_FORMAT({$field['dbAlias']}, '%m/%d/%Y'))";
            }
            else {
              $select[] = "{$field['dbAlias']}";
              $select[] = "'<br/>'";
            }
          }
        }
      }
    }

    $this->_select = "SELECT CONCAT(" . implode(', ', $select) . ") as civicrm_contact_display_name,
      t.first_visit as civicrm_contact_first_visit,
      {$logSelect} as civicrm_contact_last_update,
      {$this->customSurveyField} as civicrm_contact_survey_response,
      {$this->customNRMField} as civicrm_contact_info_request,
      ct.brochures as civicrm_contact_brochure_request";
    $this->_columnHeaders["civicrm_contact_display_name"]['title'] = $this->_columns["civicrm_contact"]['fields']['display_name']['title'];
    $this->_columnHeaders["civicrm_contact_first_visit"]['title'] = ts('First Visit');
    $this->_columnHeaders["civicrm_contact_last_update"]['title'] = ts('Last Update');
    $this->_columnHeaders["civicrm_contact_survey_response"]['title'] = ts('Survey Responses');
    $this->_columnHeaders["civicrm_contact_info_request"]['title'] = ts('Information Requests and Downloads');
    $this->_columnHeaders["civicrm_contact_brochure_request"]['title'] = ts('Brochure Request');
  }

  function from() {
    $this->_from = NULL;

    $this->_from = "
         FROM  civicrm_contact {$this->_aliases['civicrm_contact']} ";

    // For first visit times
    $this->_from .= "
             LEFT JOIN civicrm_watchdog_temp_b t
                       ON t.contact_id = {$this->_aliases['civicrm_contact']}.id\n";
    
    $this->_from .= "
             LEFT JOIN civicrm_watchdog_temp_c ct
                       ON ct.contact_id = {$this->_aliases['civicrm_contact']}.id\n";

    $this->_from .= "{$this->surveyTables}";

    $this->_from .= "{$this->nrmTables}";

    //used when address field is selected
    if ($this->_addressField) {
      $this->_from .= "
             LEFT JOIN civicrm_address {$this->_aliases['civicrm_address']}
                       ON {$this->_aliases['civicrm_contact']}.id =
                          {$this->_aliases['civicrm_address']}.contact_id AND
                          {$this->_aliases['civicrm_address']}.is_primary = 1\n";
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
            $op = CRM_Utils_Array::value("{$fieldName}_op", $this->_params);
            if ($op) {
              if ($field['name'] == 'webforms') {
                $field['dbAlias'] = "{$this->_drupalDatabase}.webform_submitted_data";
              }
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
    self::createTemp();
    self::createSurveyResponse();
    self::createInfoRequest();
    $sql = $this->buildQuery(TRUE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }
  
  function createTemp() {
    $sql = "CREATE TEMPORARY TABLE civicrm_watchdog_temp_a AS
      SELECT wid, SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '.', 1) as purl, MIN(DATE_FORMAT(DATE(FROM_UNIXTIME(timestamp)),'%m/%d/%Y')) as first_visit
      FROM {$this->_drupalDatabase}.watchdog
      GROUP BY SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '.', 1)";
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

    $sql = "CREATE TEMPORARY TABLE civicrm_watchdog_temp_c AS
      SELECT id AS contact_id, GROUP_CONCAT(brochure ORDER BY brochure SEPARATOR ', ') as brochures FROM (SELECT cc.id, wsd22.data as brochure
      FROM civicrm_contact cc
      LEFT JOIN {$this->_drupalDatabase}.webform_submitted_data wsd ON wsd.data = cc.id AND wsd.cid = 2
      LEFT JOIN {$this->_drupalDatabase}.webform_submitted_data wsd22 ON wsd22.sid = wsd.sid 
      LEFT JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.sid = wsd.sid
      WHERE ws.nid = 72 AND wsd22.data IS NOT NULL and wsd22.data <> '' AND wsd22.cid IN (22,23,24)) as s 
      GROUP BY s.id";
    $dao = CRM_Core_DAO::executeQuery($sql);
    $sql = "ALTER TABLE civicrm_watchdog_temp_c ADD INDEX idx_c_id (contact_id) USING HASH";
    $dao = CRM_Core_DAO::executeQuery($sql);

  }
  
  function createSurveyResponse() {
    $sql = "SELECT g.id as group_id, g.table_name, c.column_name
      FROM civicrm_custom_group g 
      LEFT JOIN civicrm_custom_field c ON c.custom_group_id = g.id 
      WHERE title LIKE '%Survey%'";
    $dao = CRM_Core_DAO::executeQuery($sql);
    
    while ($dao->fetch()) {
      $fieldAlias = 'group_' . $dao->group_id;
      $field =  $fieldAlias . '.' . $dao->column_name;
      $tables[$dao->group_id] = " LEFT JOIN {$dao->table_name} {$fieldAlias} ON {$fieldAlias}.entity_id = {$this->_aliases['civicrm_contact']}.id ";
      $customFields[] = "IF({$field} IS NULL or {$field} = '', '', {$field})";
      $customFields[] = "'<br/>'";
    }
    $this->customSurveyField = "CONCAT(" . implode(', ', $customFields) . ")";
    $this->surveyTables = implode(' ', $tables);
  }

  function createInfoRequest() {
    $sql = "SELECT g.id as group_id, g.table_name, c.column_name
      FROM civicrm_custom_group g 
      LEFT JOIN civicrm_custom_field c ON c.custom_group_id = g.id 
      WHERE title LIKE '%NRM%'";
    $dao = CRM_Core_DAO::executeQuery($sql);
    
    while ($dao->fetch()) {
      $fieldAlias = 'group_' . $dao->group_id;
      $field =  $fieldAlias . '.' . $dao->column_name;
      $tables[$dao->group_id] = " LEFT JOIN {$dao->table_name} {$fieldAlias} ON {$fieldAlias}.entity_id = {$this->_aliases['civicrm_contact']}.id ";
      $customFields[] = "IF({$field} IS NULL or {$field} = '', '', {$field})";
      $customFields[] = "'<br/>'";
    }
    $this->customNRMField = "CONCAT(" . implode(', ', $customFields) . ")";
    $this->nrmTables = implode(' ', $tables);
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
  
  function getDefaultWebforms() {
    $default = array();
    
    $sql = "SELECT w.nid
      FROM {$this->_drupalDatabase}.webform w
      INNER JOIN {$this->_drupalDatabase}.node n ON n.nid = w.nid
      WHERE w.nid IN (103, 128, 131, 132, 183, 75, 198)"; // Only surveys
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $default[] = $dao->nid;
    }
    return $default;
  }

  function getLabels($sql, $separator, $row) {
    $items = $newArray = $web = array();
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $items[$dao->nid] = unserialize($dao->extra);
      $webform[] = array_filter(explode("\n", $items[$dao->nid]['items']));
    }
    foreach ($webform as $d) {
      foreach ($d as $data) {
        list($k, $v) = explode('|', $data);
        $web[$k] = $v;
      }
    }
    $op = array_filter(explode($separator, $row));
    $count = 1;
    foreach($op as $values) {
      if (isset($web[$values])) {
        $newArray[] = $count . '. ' . $web[$values];
        $count++;
      }
    }
    return implode('<br/>', $newArray);
  }

  function alterDisplay(&$rows) {
    // custom code to alter rows
    $entryFound = FALSE;
    $checkList = array();
    foreach ($rows as $rowNum => $row) {

      if (!empty($this->_noRepeats) && $this->_outputMode != 'csv') {
        // not repeat contact display names if it matches with the one
        // in previous row
        $repeatFound = FALSE;
        foreach ($row as $colName => $colVal) {
          if (CRM_Utils_Array::value($colName, $checkList) &&
            is_array($checkList[$colName]) &&
            in_array($colVal, $checkList[$colName])
          ) {
            $rows[$rowNum][$colName] = "";
            $repeatFound = TRUE;
          }
          if (in_array($colName, $this->_noRepeats)) {
            $checkList[$colName][] = $colVal;
          }
        }
      }

      if (array_key_exists('civicrm_contact_display_name', $row) &&
        $rows[$rowNum]['civicrm_contact_display_name'] &&
        array_key_exists('civicrm_contact_id', $row)
      ) {
        $url = CRM_Utils_System::url("civicrm/contact/view",
          'reset=1&cid=' . $row['civicrm_contact_id'],
          $this->_absoluteUrl
        );
        $rows[$rowNum]['civicrm_contact_display_name_link'] = $url;
        $rows[$rowNum]['civicrm_contact_display_name_hover'] = ts("View Contact Summary for this Contact.");
        $entryFound = TRUE;
      }
      
      if (array_key_exists('civicrm_contact_survey_response', $row)) {
        // First retrieve all the components used for surveys
        $sql = "SELECT nid, extra
          FROM {$this->_drupalDatabase}.webform_component
          WHERE form_key LIKE '%cg20%' AND type = 'select'";
        $rows[$rowNum]['civicrm_contact_survey_response'] = self::getLabels($sql, $separator = '<br/>', $row['civicrm_contact_survey_response']);
      }

      if (array_key_exists('civicrm_contact_brochure_request', $row)) {
        // First retrieve all the components used for brochures
        $sql = "SELECT nid, extra
          FROM {$this->_drupalDatabase}.webform_component
          WHERE form_key LIKE '%cg6%' AND nid = 72 AND type = 'select' AND cid IN (22,23,24)";
        $rows[$rowNum]['civicrm_contact_brochure_request'] = self::getLabels($sql, $separator = ', ', $row['civicrm_contact_brochure_request']);
      }

      if (!$entryFound) {
        break;
      }
    }
  }
}
