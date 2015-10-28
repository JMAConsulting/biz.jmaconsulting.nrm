<?php

class CRM_Yoteup_Form_Report_SurveyDaily extends CRM_Report_Form {

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
    $this->_groupFilter = TRUE;
    $this->_tagFilter = TRUE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Survey Daily Report'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'Submitted_Time' => array(
        'title' => 'Submitted Time',
        'ignore_group_concat' => TRUE,
        'columnName' => "DATE_FORMAT(FROM_UNIXTIME(ws.completed), '%m-%d-%Y %r')",
      ),
      'First_Name' => array(
        'title' => 'First Name',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_contact.first_name',
      ),
      'Middle_Name' => array(
        'title' => 'Middle Name',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_contact.middle_name',
      ),
      'Last_Name' => array(
        'title' => 'Last Name',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_contact.last_name',
      ),
      'Nick_Name' => array(
        'title' => 'Nickname',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_contact.nick_name',
      ),
      'Birth_Date' => array(
        'title' => 'Birth Date',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_contact.birth_date',
      ),
      'Gender' => array(
        'title' => 'Gender',
        'ignore_group_concat' => TRUE,
        'columnName' => 'g.label',
      ),
      'Street_Address' => array(
        'title' => 'Street Address',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_address.street_address',
      ),
      'Street_Address_Line_2' => array(
        'title' => 'Street Address Line 2',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_address.supplemental_address_1',
      ),
      'City' => array(
        'title' => 'City',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_address.city',
      ),
      'State' => array(
        'title' => 'State',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_state_province.name',
      ),
      'Postal_Code' => array(
        'title' => 'Postal Code',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_address.postal_code',
      ),
      'Phone' => array(
        'title' => 'Phone',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_phone.phone',
      ),
      'Email' => array(
        'title' => 'Email',
        'ignore_group_concat' => TRUE,
        'columnName' => 'civicrm_email.email',
      ),
      'What_is_the_main_reason_you_are_interested' => array(
        'title' => 'What is your biggest apprehension about going to college?',
      ),
      'What_is_your_biggest_apprehension' => array(
        'title' => 'What is your biggest apprehension about going to college?',
      ),
    );
    CRM_Yoteup_BAO_Yoteup::reportSelectClause($this, $columns);
  }

  function from() { 
    $config = CRM_Core_Config::singleton();
    $dsnArray = DB::parseDSN($config->userFrameworkDSN);
    $drupalDb = $dsnArray['database'];
    $this->_from = "FROM {$drupalDb}.webform_submitted_data wsd 
      LEFT JOIN civicrm_contact ON wsd.data = civicrm_contact.id AND wsd.cid = 2
      LEFT JOIN civicrm_address ON civicrm_address.contact_id = civicrm_contact.id AND is_primary = 1
      LEFT JOIN civicrm_state_province ON civicrm_state_province.id = civicrm_address.state_province_id
      LEFT JOIN civicrm_phone ON civicrm_phone.contact_id = civicrm_contact.id AND is_primary = 1
      LEFT JOIN civicrm_email ON civicrm_email.contact_id = civicrm_contact.id AND is_primary = 1
      LEFT JOIN civicrm_value g ON civicrm_contact.gender_id = g.value AND g.option_group_id = 3
      LEFT JOIN {$drupalDb}.webform_component wc ON wc.cid = wsd.cid 
      LEFT JOIN {$drupalDb}.webform_submissions ws ON ws.sid = wsd.sid ";
  }

  function where() {
    CRM_Yoteup_BAO_Yoteup::reportWhereClause($this->_where, '128, 131');
  }

  function groupBy() {
    $this->_groupBy = "GROUP BY wsd.sid";
  }

  function orderBy() {
    return FALSE;
  }

  function postProcess() {

    $this->beginPostProcess();

    $sql = $this->buildQuery(FALSE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
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

      if (!$entryFound) {
        break;
      }
    }    
  }
}
