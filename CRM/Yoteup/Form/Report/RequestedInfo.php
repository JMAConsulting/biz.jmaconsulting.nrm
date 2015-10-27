<?php

class CRM_Yoteup_Form_Report_RequestedInfo extends CRM_Report_Form {

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE; 

  function __construct() {
    $config = CRM_Core_Config::singleton();
    $dsnArray = DB::parseDSN($config->userFrameworkDSN);
    $this->_drupalDatabase = $dsnArray['database'];
    
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
    $this->assign('reportTitle', ts('Requested Information Report'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'Chowan_ID' => array(
        'title' => 'Chowan ID',
        'ignore_group_concat' => TRUE,
        'columnName' => 'contact_civireport.external_identifier',
      ),
      'Submitted_Date' => array(
        'title' => 'Submitted Date',
        'ignore_group_concat' => TRUE,
        'columnName' => 'DATE(FROM_UNIXTIME(ws.completed))',
      ),
      'Existing_Contact' => array(
        'title' => 'Existing Contact',
      ),
      'First_Name' => array(
        'title' => 'First Name',
      ),
      'Last_Name' => array(
        'title' => 'Last Name',
      ),
      'Middle_Name' => array(
        'title' => 'Middle Name',
      ),
      'Email_Address' => array(
        'title' => 'Email Address',
      ),
      'Permanent_Address_Line_1' => array(
        'title' => 'Permanent Address Line 1',
      ),
      'Permanent_Address_Line_2' => array(
        'title' => 'Permanent Address Line 2',
      ),
      'City' => array(
        'title' => 'City'
      ),
      'Zip_Code' => array(
        'title' => 'Zip Code',
      ),
      'State_Abbr' => array(
        'title' => 'State Abbr',
      ),
      'State' => array(
        'title' => 'State',
      ),
      'Country' => array(
        'title' => 'Country',
        'columnName' => 'c.name',
      ),
      'Country_Code' => array(
        'title' => 'Country Code',
      ),
      'Gender' => array(
        'title' => 'Gender',
        'columnName' => 'g.label',
      ),
      'High_School_Graduation_Date' => array(
        'title' => 'High School Graduation Date',
      ),
      'Primary_Phone_Number' => array(
        'title' => 'Primary Phone Number',
      ),
      'Primary_Phone_Type' => array(
        'title' => 'Primary Phone Type',
        'columnName' => 'pt1.label',
      ),
      'High_School_Attended' => array(
        'title' => 'High School Attended',
      ),
      'Secondary_Phone_Number' => array(
        'title' => 'Secondary Phone Number',
      ),
      'Secondary_Phone_Type' => array(
        'title' => 'Secondary Phone Type',
        'columnName' => 'pt2.label',
      ),
      'Type_of_Inquiry' => array(
        'title' => 'Type of Inquiry',
        'columnName' => 'i.label',
      ),
      'College_Attended_(if_any)' => array(
        'title' => 'College Attended (if any)',
      ),
      'Date_of_Birth' => array(
        'title' => 'Date of Birth',
      ),
    );
    CRM_Yoteup_BAO_Yoteup::reportSelectClause($this, $columns);
  }

  function from() { 
    CRM_Yoteup_BAO_Yoteup::reportFromClause($this->_from, $this->_drupalDatabase);
  }

  function where() {
    CRM_Yoteup_BAO_Yoteup::reportWhereClause($this->_where, 72);
  }

  function groupBy() {
    $this->_groupBy = "GROUP BY wsd.sid) as sq 
      LEFT JOIN civicrm_state_province sp ON sq.`State Abbr` COLLATE utf8_unicode_ci = sp.abbreviation AND sq.`Country Code` = sp.country_id";
  }

  function orderBy() {
    return FALSE;
  }

  function postProcess() {

    $this->beginPostProcess();
    self::createInquiry();

    $sql = $this->buildQuery(FALSE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }
  
  function createInquiry() {
    $sql = "SELECT extra
      FROM {$this->_drupalDatabase}.webform_component
      WHERE form_key = 'type_of_inquiry' AND nid = 72";
    $result = CRM_Core_DAO::singleValueQuery($sql);
    $result = unserialize($result);
    $inquiry = explode('|', $result['items']);
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS inquiry");
    CRM_Core_DAO::executeQuery("CREATE TEMPORARY TABLE IF NOT EXISTS inquiry (
      value int(50) NOT NULL,
      name varchar(64) NOT NULL)");
    $sql = "INSERT INTO inquiry VALUES";
    foreach ($inquiry as $key => &$items) {
      $items = trim(preg_replace('/[0-9]+/', NULL, $items));
      if ($key != 0) {
        $vals[] = " ({$key}, '{$items}')";
      }
    }
    $sql .= implode(',', $vals);
    CRM_Core_DAO::executeQuery($sql);
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
