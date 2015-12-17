<?php

class CRM_Nrm_Form_Report_ScholarshipDay extends CRM_Report_Form {

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE; 

  function __construct() { 
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
        'grouping' => 'contact-fields',
      ),
    );
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Chowan Scholarship Day Report'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'Which_Scholarship_Day_will_you_be_attending?' => array(
        'title' => 'Which Scholarship Day will you be attending?',
        'columnName' => 'civicrm_1_participant_1_participant_event_id_alias.name',
      ),
      'First_Name' => array(
        'title' => 'First Name',
      ),
      'Middle_Name' => array(
        'title' => 'Middle Name',
      ),
      'Last_Name' => array(
        'title' => 'Last Name',
      ),
      'Preferred_Name' => array(
        'title' => 'Preferred Name',
      ),
      'Email' => array(
        'title' => 'Email',
      ),
      'Primary_Phone_Number' => array(
        'title' => 'Primary Phone Number',
      ),
      'Primary_Phone_Type' => array(
        'title' => 'Primary Phone Type',
        'columnName' => 'pt1.label',
      ),
      'Secondary_Phone_Number' => array(
        'title' => 'Secondary Phone',
      ),
      'Secondary_Phone_Type' => array(
        'title' => 'Secondary Phone Type',
        'columnName' => 'pt2.label',
      ),
      'Street_Address' => array(
        'title' => 'Street Address',
      ),
      'Street_Address_Line_2' => array(
        'title' => 'Street Address Line 2',
      ),
      'City' => array(
        'title' => 'City',
      ),
      'State/Province' => array(
        'title' => 'State/Province',
      ),
      'Postal_Code' => array(
        'title' => 'Postal Code',
      ),
      'Date_of_Birth' => array(
        'title' => 'Date of Birth',
      ),
      'Gender' => array(
        'title' => 'Gender',
        'columnName' => 'g.label',
      ),
      'High_School_Name' => array(
        'title' => 'High School Name',
      ),
      'HS_Grad_Date' => array(
        'title' => 'HS Grad Date',
      ),
      'Academics' => array(
        'title' => 'Academics',
        'columnName' => 'academics_alias.label',
      ),
      'Athletics' => array(
        'title' => 'Athletics',
        'columnName' => 'athletics_alias.label',
      ),
      'Extra-Curricular' => array(
        'title' => 'Extra-Curricular',
        'columnName' => 'extra_alias.label',
      ),
      'Leadership_Qualities' => array(
        'title' => 'Leadership Qualities',
        'is_alias' => TRUE,
        'alias_new' => 'Leadership_Qualities',
      ),
      'How_will_your_experiences_as_a_leader_help_you_fulfill_the_mission_statement_of_Chowan_University?' => array(
        'title' => 'How will your experiences as a leader help you fulfill the mission statement of Chowan University?',
      ),
    );

    CRM_Nrm_BAO_Nrm::reportSelectClause($this, $columns, TRUE);
  }

  function from() {
    $custom = array(
      171 => 'academics',
      159 => 'athletics',
      158 => 'extra',
    );
    CRM_Nrm_BAO_Nrm::reportFromClause($this->_from, TRUE, array('civicrm_1_participant_1_participant_event_id'), $custom);
  }

  function where() {
    CRM_Nrm_BAO_Nrm::reportWhereClause($this->_where, 277);
  }

  function groupBy() {
    $this->_groupBy = "GROUP BY wsd.sid";
  }

  function orderBy() {
    return FALSE;
  }

  function postProcess() {

    $this->beginPostProcess();
    
    $formKeys = array(
      'civicrm_1_participant_1_participant_event_id',
    );
    self::createWebformTemp($formKeys);

    $sql = $this->buildQuery(FALSE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  } 

  function createWebformTemp($formKeys) {
    foreach ($formKeys as $formKey) {
      $item = $vals = array();
      $config = CRM_Core_Config::singleton();
      $dsnArray = DB::parseDSN($config->userFrameworkDSN);
      $drupalDatabase = $dsnArray['database'];
      $sql = "SELECT extra
        FROM {$drupalDatabase}.webform_component
        WHERE form_key = '{$formKey}' AND nid = 277";
      if (in_array($formKey, array('civicrm_1_participant_1_participant_event_id'))) {
        $result = CRM_Core_DAO::singleValueQuery($sql);
        $result = unserialize($result);
        $item = explode('|', $result['items']);
        $flag = TRUE;
        if ($formKey == 'civicrm_1_participant_1_participant_event_id') {
          $flag = FALSE;
          $date = TRUE;
          $temp = $item;
          $item = array();
          $item['dates'] = array_chunk($temp, 2);
        }
      }
      CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS {$formKey}");
      CRM_Core_DAO::executeQuery("CREATE TEMPORARY TABLE IF NOT EXISTS {$formKey} (
        value int(50) NOT NULL,
        name varchar(64) NOT NULL)");
      $sql = "INSERT INTO {$formKey} VALUES";
      foreach ($item as $key => &$items) {
        if ($flag) {
          $items = trim(preg_replace('/[0-9]+/', NULL, $items));
        }
        if ($key != 'dates' && $key != 0) {
          $vals[] = " ('{$key}', '{$items}')";
        }
        if ($key == 'dates') {
          foreach ($items as $k => $v) {
            $vals[] = " ('{$v[0]}', '{$v[1]}')";
          } 
        }
      }
      $sql .= implode(',', $vals);
      CRM_Core_DAO::executeQuery($sql);
    }
  }

  function alterDisplay(&$rows) {
  }
}
