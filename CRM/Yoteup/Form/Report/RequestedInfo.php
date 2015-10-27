<?php

class CRM_Yoteup_Form_Report_RequestedInfo extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('Requested Information Report'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'Submitted_Date' => array(
        'title' => 'Submitted Date',
        'ignore_group_concat' => TRUE,
        'columnName' => 'DATE(FROM_UNIXTIME(ws.completed))',
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
      'Name_Suffix' => array(
        'title' => 'Name Suffix',
      ),
      'Nick_Name' => array(
        'title' => 'Nickname',
      ),
      'Street Address' => array(
        'title' => 'Street Address',
      ),
      'Street_Address_Line_2' => array(
        'title' => 'Street Address Line 2',
      ),
      'City' => array(
        'title' => 'City'
      ),
      'State_Abbr' => array(
        'title' => 'State Abbr',
      ),
      'State' => array(
        'title' => 'State',
      ),
      'Zip_Code' => array(
        'title' => 'Zip Code',
      ),
      'Phone_Number' => array(
        'title' => 'Phone Number',
      ),
      'Email' => array(
        'title' => 'Email',
      ),
      'High_School_Name' => array(
        'title' => 'High School Name',
      ),
      'High_School_Graduation_Date' => array(
        'title' => 'High School Graduation Date',
      ),
      'Major_or_Academic_Interests' => array(
        'title' => 'Major_or_Academic_Interests',
      ),
    );
    
    $this->_select = "SELECT sq.*, sp.name AS 'State' FROM ( ";
    CRM_Yoteup_BAO_Yoteup::reportSelectClause($this, $columns, TRUE);
  }

  function from() { 
    CRM_Yoteup_BAO_Yoteup::reportFromClause($this->_from);
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
