<?php

class CRM_Nrm_Form_Report_PersonalVisit extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('Personal Visit Day Report'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'Chowan_ID' => array(
        'title' => 'Chowan ID',
        'ignore_group_concat' => TRUE,
        'columnName' => 'GROUP_CONCAT(contact_civireport.external_identifier)',
      ),
      'Preferred_Visit_Date' => array(
        'title' => 'Preferred Visit Date',
      ),
      'Preferred_Visit_Time' => array(
        'title' => 'Preferred Visit Time',
        'columnName' => 'visit_alias.label',
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
      'Nickname' => array(
        'title' => 'Nickname',
      ),
      'Home_Phone' => array(
        'title' => 'Home Phone',
      ),
      'Mobile_Phone' => array(
        'title' => 'Mobile Phone',
      ),
      'Email' => array(
        'title' => 'Email',
      ),
      'Street_Address' => array(
        'title' => 'Street Address',
      ),
      'Address_Line_2' => array(
        'title' => 'Address Line 2',
      ),
      'Address_Line_3' => array(
        'title' => 'Address Line 3',
      ),
      'City' => array(
        'title' => 'City'
      ),
      'State/Province' => array(
        'title' => 'State/Province'
      ),
      'Postal_Code' => array(
        'title' => 'Postal Code',
      ),
      'Country' => array(
        'title' => 'Country',
        'columnName' => 'c.name',
      ),
      'High_School_Name' => array(
        'title' => 'High School Name',
      ),
      'Please_list_any_colleges_attended_or_currently_attending?' => array(
        'title' => 'Please list any colleges attended or currently attending?',
      ),
      'Attend_a_class' => array(
        'title' => 'Attend a class',
      ),
      'Which_subject?_1' => array(
        'title' => 'Which subject?',
        'same_alias' => TRUE,
        'cid' => 37,
        'alias' => 1,
      ),
      'Tour_Campus' => array(
        'title' => 'Tour Campus',
      ),
      'Visit_with_faculty' => array(
        'title' => 'Visit with faculty',
      ),
      'Which_subject?_2' => array(
        'title' => 'Which subject?',
        'same_alias' => TRUE,
        'cid' => 40,
        'alias' => 2,
      ),
      'Visit_with_Fine_Arts_faculty' => array(
        'title' => 'Visit with Fine Arts faculty',
      ),
      'Which_subject?_3' => array(
        'title' => 'Which subject?',
        'same_alias' => TRUE,
        'cid' => 42,
        'alias' => 3,
      ),
      'Visit_with_a_coach' => array(
        'title' => 'Visit with a coach',
      ),
      'Which_sport(s)?' => array(
        'title' => 'Which sport(s)?',
      ),
      'Connect_with_Campus_Ministries' => array(
        'title' => 'Connect with Campus Ministries',
      ),
      'Academics' => array(
        'title' => 'Academics',
        'columnName' => 'academic_alias.label',
      ),
      'Athletics' => array(
        'title' => 'Athletics',
        'columnName' => 'athletics_alias.label',
      ),
      'Extra-Curricular' => array(
        'title' => 'Extra-Curricular',
        'columnName' => 'extra_alias.label',
      ),
      'Comments/Requests' => array(
        'title' => 'Comments/Requests',
      ),
    );
    CRM_Nrm_BAO_Nrm::reportSelectClause($this, $columns);
  }

  function from() {
    $custom = array(
      159 => 'athletics',
      171 => 'academic',
      158 => 'extra',
      174 => 'visit',
    );
    CRM_Nrm_BAO_Nrm::reportFromClause($this->_from, FALSE, array(), $custom);
  }

  function where() {
    CRM_Nrm_BAO_Nrm::reportWhereClause($this->_where, 89);
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
