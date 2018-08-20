<?php

class CRM_Nrm_Form_Report_CuVisitDay19 extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('CU Visit Day Report for 2019'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'Submitted_Time' => array(
        'title' => 'Submitted Time',
        'ignore_group_concat' => TRUE,
        'columnName' => "DATE_FORMAT(FROM_UNIXTIME(ws.completed), '%m-%d-%Y %r')",
      ),
      'Chowan_ID' => array(
        'title' => 'Chowan ID',
        'ignore_group_concat' => TRUE,
        'columnName' => 'GROUP_CONCAT(contact_civireport.external_identifier)',
      ),
      'Which_CU_Visit_Day_will_you_be_attending?' => array(
        'title' => 'Which CU Visit Day will you be attending?',
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
      'Primary_Phone_Number' => array(
        'title' => 'Primary Phone Number',
      ),
      'Primary_Phone_Type' => array(
        'title' => 'Primary Phone Type',
        'columnName' => 'pt1.label',
      ),
      'Secondary_Phone_Number' => array(
        'title' => 'Secondary Phone Number',
      ),
      'Secondary_Phone_Type' => array(
        'title' => 'Secondary Phone Type',
        'columnName' => 'pt2.label',
      ),
      'Email' => array(
        'title' => 'Email',
      ),
      'Permanent_Address_Line_1' => array(
        'title' => 'Permanent Address Line 1',
      ),
      'Permanent_Address_Line_2' => array(
        'title' => 'Permanent Address Line 2',
      ),
      'City' => array(
        'title' => 'City',
      ),
      'State' => array(
        'title' => 'State',
      ),
      'Zip_Code' => array(
        'title' => 'Zip Code',
      ),
      'Country' => array(
        'title' => 'Country',
        'columnName' => 'c.name',
      ),
      'Date_of_Birth' => array(
        'title' => 'Date of Birth',
      ),
      'Gender' => array(
        'title' => 'Gender',
        'columnName' => 'g.label',
      ),
      'Anticipated_Academic_Enroll_Term' => array(
        'title' => 'Anticipated Academic Enroll Term',
        'columnName' => 'anticipated_academic_enroll_term_alias.name',
      ),
      'Anticipated_Academic_Enroll_Year' => array(
        'title' => 'Anticipated Academic Enroll Year',
        'columnName' => 'anticipated_academic_enroll_year_alias.name',
      ),
      'High_School_Attended' => array(
        'title' => 'High School Attended',
      ),
      'High_School_Graduation_Date' => array(
        'title' => 'High School Graduation Date',
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
      'How_did_you_hear_about_Chowan?' => array(
        'title' => 'How did you hear about Chowan?',
        'columnName' => 'how_did_you_hear_about_chowan_alias.name',
      ),
      'How_did_you_hear_about_CU_Visit_Day?' => array(
        'title' => 'How did you hear about CU Visit Day?',
        'columnName' => 'how_did_you_hear_about_cu_visit_day_alias.name',
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
    CRM_Nrm_BAO_Nrm::reportFromClause($this->_from, TRUE, array( 'how_did_you_hear_about_chowan', 
      'anticipated_academic_enroll_year', 'anticipated_academic_enroll_term',
      'how_did_you_hear_about_cu_visit_day', 'civicrm_1_participant_1_participant_event_id'), $custom);
  }

  function where() {
    CRM_Nrm_BAO_Nrm::reportWhereClause($this->_where, 428);
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
      'how_did_you_hear_about_chowan',
      'anticipated_academic_enroll_year',
      'anticipated_academic_enroll_term',
      'how_did_you_hear_about_cu_visit_day',
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
      $drupalDatabase = 'chowan2019_dru';
      $sql = "SELECT extra
        FROM {$drupalDatabase}.webform_component
        WHERE form_key = '{$formKey}' AND nid = 428";
      if (in_array($formKey, array('civicrm_1_participant_1_participant_event_id', 'how_did_you_hear_about_chowan', 'anticipated_academic_enroll_term', 'how_did_you_hear_about_cu_visit_day'))) {
        $result = CRM_Core_DAO::singleValueQuery($sql);
        $result = unserialize($result);
        $item = explode('|', $result['items']);
        if ($formKey == 'civicrm_1_participant_1_participant_event_id') {
          $newItems = explode(PHP_EOL, $result['items']);
          foreach ($newItems as $v) {
            list($k, $v) = explode('|', $v);
            $i[] = array($k, $v);
          }
          $item['dates'] = $i;
        }
      }
      if ($formKey == 'anticipated_academic_enroll_year') {
        $item = array(
          1 => 2015,
          2 => 2016,
          3 => 2017,
          4 => 2018,
          5 => 2019,
        );
        $flag = FALSE;
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

}
