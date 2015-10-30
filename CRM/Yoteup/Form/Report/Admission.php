<?php

class CRM_Yoteup_Form_Report_Admission extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('Admission Daily Report'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'Submitted_Date' => array(
        'title' => 'Submitted Date',
        'ignore_group_concat' => TRUE,
        'columnName' => 'DATE(FROM_UNIXTIME(ws.completed))',
      ),
      'Entry_Year' => array(
        'title' => 'Entry Year',
      ),
      'Application_Type' => array(
        'title' => 'Application Type',
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
      'Social Security Number' => array(
        'title' => 'Social Security Number',
      ),
      'Preferred_Nickname' => array(
        'title' => 'Preferred/Nickname',
      ),
      'Gender' => array(
                        'title' => 'Gender',
        'columnName' => 'g.label',
      ),
      'Birth_Date' => array(
        'title' => 'Birth Date',
      ),
      'Street_Address' => array(
        'title' => 'Street Address',
      ),
      'Street_Address_Line_2' => array(
        'title' => 'Street Address Line 2',
      ),
      'City' => array(
        'title' => 'City'
      ),
      'County' => array(
        'title' => 'County',
      ),
      'State/Province' => array(
        'title' => 'State/Province',
      ),
      'Postal_Code' => array(
        'title' => 'Postal Code',
      ),
      'Country' => array(
        'title' => 'Country',
      ),
      'Home_Phone' => array(
        'title' => 'Home Phone',
      ),
      'Mobile Phone' => array(
        'title' => 'Mobile Phone',
      ),
      'Email' => array(
        'title' => 'Email',
      ),
      'Primary Academic Interest' => array(
        'title' => 'Primary Academic Interest',
      ),
      'Do you plan to play an intercollegiate sport at Brevard?' => array(
        'title' => 'Do you plan to play an intercollegiate sport at Brevard?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'If yes, what sport?' => array(
        'title' => 'If yes, what sport?',
      ),
      'Outdoor Interests' => array(
        'title' => 'Outdoor Interests',
      ),
      'Are you being recruited by a Brevard College coach?' => array(
        'title' => 'Are you being recruited by a Brevard College coach?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Performance Instrument' => array(
        'title' => 'Performance Instrument',
      ),
      'Voice Type' => array(
        'title' => 'Voice Type',
      ),
      'Are you a U.S. Citizen?' => array(
        'title' => 'Are you a U.S. Citizen?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'If not a U.S. Citizen, of what country are you officially a citizen?' => array(
        'title' => 'If not a U.S. Citizen, of what country are you officially a citizen?',
      ),
      'Country of Birth' => array(
        'title' => 'Country of Birth',
      ),
      'Current Visa Status in the U.S.' => array(
        'title' => 'Current Visa Status in the U.S.',
      ),
      'Number of years residing in the USA' => array(
        'title' => 'Number of years residing in the USA',
      ),
      'Are you a Veteran?' => array(
        'title' => 'Are you a Veteran?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Are you of Hispanic or Latino descent' => array(
        'title' => 'Are you of Hispanic or Latino descent',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Please select the category that best reflects your ethnic background' => array(
        'title' => 'Please select the category that best reflects your ethnic background',
      ),
      'Religious Preference' => array(
        'title' => 'Religious Preference',
      ),
      'First Name' => array(
        'title' => 'First Name',
      ),
      'Last Name' => array(
        'title' => 'Last Name',
      ),
      'Phone' => array(
        'title' => 'Phone',
      ),
      'Email' => array(
        'title' => 'Email',
      ),
      'Education Level' => array(
        'title' => 'Education Level',
      ),
      'Relationship' => array(
        'title' => 'Relationship',
      ),
      'First Name' => array(
        'title' => 'First Name',
      ),
      'Last Name' => array(
        'title' => 'Last Name',
      ),
      'Phone' => array(
        'title' => 'Phone',
      ),
      'Email' => array(
        'title' => 'Email',
      ),
      'Education Level' => array(
        'title' => 'Education Level',
      ),
      'Relationship' => array(
        'title' => 'Relationship',
      ),
      'With which parent do you reside?' => array(
        'title' => 'With which parent do you reside?',
      ),
      'School_Name' => array(
        'title' => 'School Name',
      ),
      'School_City_State' => array(
        'title' => 'School City & State',
      ),
      'Dates_Attended' => array(
        'title' => 'Dates Attended',
      ),
      'Graduation_Date' => array(
        'title' => 'Graduation Date',
      ),
      'Degree_Earned' => array(
        'title' => 'Degree(s) Earned',
      ),
      'School_Name' => array(
        'title' => 'School Name',
      ),
      'School_City_State' => array(
        'title' => 'School City & State',
      ),
      'Dates_Attended' => array(
        'title' => 'Dates Attended',
      ),
      'Graduation_Date' => array(
        'title' => 'Graduation Date',
      ),
      'Degree_Earned' => array(
        'title' => 'Degree(s) Earned',
      ),
      'Have you ever taken a college level course?' => array(
        'title' => 'Have you ever taken a college level course?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Did you/Do you participate in early college?' => array(
        'title' => 'Did you/Do you participate in early college?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Have you attended another college?' => array(
        'title' => 'Have you attended another college?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'School_Name' => array(
        'title' => 'School Name',
      ),
      'School_City_State' => array(
        'title' => 'School City & State',
      ),
      'Dates_Attended' => array(
        'title' => 'Dates Attended',
      ),
      'Graduation_Date' => array(
        'title' => 'Graduation Date',
      ),
      'Degree_Earned' => array(
        'title' => 'Degree(s) Earned',
      ),
      'School_Name' => array(
        'title' => 'School Name',
      ),
      'School_City_State' => array(
        'title' => 'School City & State',
      ),
      'Dates_Attended' => array(
        'title' => 'Dates Attended',
      ),
      'Graduation_Date' => array(
        'title' => 'Graduation Date',
      ),
      'Degree_Earned' => array(
        'title' => 'Degree(s) Earned',
      ),
      'Unweighted GPA' => array(
        'title' => 'Unweighted GPA',
      ),
      'I would like to apply Test Optional' => array(
        'title' => 'I would like to apply Test Optional',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Answer these questions' => array(
        'title' => 'Answer these questions',
      ),
      'Potential Indication' => array(
        'title' => 'Potential Indication',
      ),
      'Experiential Learning' => array(
        'title' => 'Experiential Learning',
      ),
      'Significant Experience' => array(
        'title' => 'Significant Experience',
      ),
      'Learning Community' => array(
        'title' => 'Learning Community',
      ),
      'Describe Yourself' => array(
        'title' => 'Describe Yourself',
      ),
      'Date Taken' => array(
        'title' => 'Date Taken (or planned)',
      ),
      'Critical Reading Score' => array(
        'title' => 'Critical Reading Score',
      ),
      'Math Score' => array(
        'title' => 'Math Score',
      ),
      'Writing Score' => array(
        'title' => 'Writing Score',
      ),
      'ACT Date Taken (or planned)' => array(
        'title' => 'ACT Date Taken (or planned)',
      ),
      'ACT Composite Score' => array(
        'title' => 'ACT Composite Score',
      ),
      'Have you ever been dismissed for academic or disciplinary reasons from a secondary school or college?' => array(
        'title' => 'Have you ever been dismissed for academic or disciplinary reasons from a secondary school or college?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'If yes, please explain' => array(
        'title' => 'If yes, please explain',
      ),
      'Have you ever been convicted of a crime, other than a minor traffic violation?' => array(
        'title' => 'Have you ever been convicted of a crime, other than a minor traffic violation?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'If yes, please explain 2' => array(
        'title' => 'If yes, please explain 2',
      ),
      'Do you intend to Graduate from Brevard?' => array(
        'title' => 'Do you intend to Graduate from Brevard?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'What other colleges or universities are you applying to?' => array(
        'title' => 'What other colleges or universities are you applying to?',
      ),
      'Where do you plan to live?' => array(
        'title' => 'Where do you plan to live?',
      ),
      'How did you hear about Brevard?' => array(
        'title' => 'How did you hear about Brevard?',
      ),
    );
    CRM_Yoteup_BAO_Yoteup::reportSelectClause($this, $columns);
  }

  function from() {
    CRM_Yoteup_BAO_Yoteup::reportFromClause($this->_from);
  }

  function where() {
    CRM_Yoteup_BAO_Yoteup::reportWhereClause($this->_where, 70);
  }

  function groupBy() {
    $this->_groupBy = "GROUP BY wsd.sid";
  }

  function orderBy() {
    return FALSE;
  }

  function postProcess() {

    $this->beginPostProcess();
    $sql = $this->buildQuery(TRUE);

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
