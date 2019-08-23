<?php

class CRM_Nrm_Form_Report_VipApplicationUpike20 extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('VIP Application Report for Upike 2020'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'Submitted_on' => array(
        'title' => 'Submitted on',
        'ignore_group_concat' => TRUE,
        'columnName' => "DATE_FORMAT(FROM_UNIXTIME(ws.completed), '%m-%d-%Y %r')",
      ),
      'Chowan_ID' => array(
        'title' => 'Chowan ID',
        'ignore_group_concat' => TRUE,
        'columnName' => 'GROUP_CONCAT(contact_civireport.external_identifier)',
      ),
      'Social_Security_Number' => array(
        'title' => 'Social Security Number',
      ),
      'First_Name_1' => array(
        'title' => 'First Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 156,
      ),
      'Preferred_Name' => array(
        'title' => 'Preferred Name',
      ),
      'Middle_Name_1' => array(
        'title' => 'Middle Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 158,
      ),
      'Last_Name_1' => array(
        'title' => 'Last Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 159,
      ),
      'Gender' => array(
        'title' => 'Gender',
        'columnName' => 'g.label',
      ),
      'Permanent_Address' => array(
        'title' => 'Permanent Address',
      ),
      'Address_Line_2' => array(
        'title' => 'Address Line 2',
      ),
      'Address_Line_3' => array(
        'title' => 'Address Line 3',
      ),
      'City_1' => array(
        'title' => 'City',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 149,
      ),
      'Zip_1' => array(
        'title' => 'Zip',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 150,
      ),
      'State_1' => array(
        'title' => 'State',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 153,
      ),
      'Email_1' => array(
        'title' => 'Email Address',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 155,
      ),
      'Home_Phone' => array(
        'title' => 'Home Phone',
      ),
      'Birth_Date' => array(
        'title' => 'Birth Date',
      ),
      'Are_you_a_U_S__Citizen?' => array(
        'title' => 'USA Citizen?',
        'is_alias' => TRUE,
        'alias_new' => 'Are you a U.S. Citizen?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Enrollment_Status' => array(
        'title' => 'Enrollment Status',
        'columnName' => 'enrollment_status_alias.label',
      ),
      'Race' => array(
        'title' => 'Race',
        'is_alias' => TRUE,
        'alias_new' => 'Race',
        'columnName' => 'race_alias.label',
      ),
      'Marital_Status' => array(
        'title' => 'Marital Status',
        'is_alias' => TRUE,
        'alias_new' => 'Marital Status',
        'columnName' => 'marital_alias.label',
      ),
      'Maiden_Name' => array(
        'title' => 'Maiden Name',
      ),
      'Ethnicity' => array(
        'title' => 'Ethnicity',
        'is_alias' => TRUE,
        'alias_new' => 'Ethnicity',
        'columnName' => 'ethnicity_alias.label',
      ),
      'US_Military/Veteran_Status' => array(
        'title' => 'US Military/Veteran Status',
        'columnName' => 'veteran_alias.label',
      ),
      'Admit_Status' => array(
        'title' => 'Admit Status',
        'columnName' => 'admit_alias.label',
      ),
      'Beginning_Term' => array(
        'title' => 'Beginning Term',
        'columnName' => 'term_alias.label',
      ),
      'Do_you_plan_to_live_on_campus?' => array(
        'title' => 'Do you plan to live on campus?',
        'is_alias' => TRUE,
        'alias_new' => 'Do you plan to live on campus?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Have_you_ever_attended_UPIKE?' => array(
        'title' => 'Have you ever attended UPIKE?',
        'is_alias' => TRUE,
        'alias_new' => 'Have you ever attended UPIKE?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Have_you_taken_any_dual_credit_classes?' => array(
        'title' => 'Have you taken any dual credit classes?',
        'is_alias' => TRUE,
        'alias_new' => 'Have you taken any dual credit classes?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'How_many_dual_credit_hours_have_you_completed?' => array(
        'title' => 'How many dual credit hours have you completed?',
      ),
      'Birth_Country' => array(
        'title' => 'Birth Country',
      ),
      'Passport_was_issued_from_what_country?' => array(
        'title' => 'Passport was issued from what country?',
      ),
      'Family_(Last_Name)' => array(
        'title' => 'Family (Last Name)',
      ),
      'Birth_City' => array(
        'title' => 'Birth City',
      ),
      'Major_Course_of_Study' => array(
        'title' => 'Major Course of Study',
        'columnName' => 'majorcourse_alias.label',
      ),
      'Activity_1.' => array(
        'title' => 'Activity 1.',
        'columnName' => 'activity1_alias.label',
      ),
      'ACT_Composite' => array(
        'title' => 'ACT Composite',
      ),
      'SAT_Composite' => array(
        'title' => 'SAT Composite',
      ),
      'GED_Date_Passed' => array(
        'title' => 'GED Date Passed',
      ),
      'High_School_Name' => array(
        'title' => 'High School Name',
      ),
      'High_School_City_&_State' => array(
        'title' => 'High School City & State',
      ),
      'Activity_2.' => array(
        'title' => 'Activity 2.',
        'columnName' => 'activity1_alias.label',
      ),
      'Activity_3.' => array(
        'title' => 'Activity 3.',
        'columnName' => 'activity1_alias.label',
      ),
      'Activity_4.' => array(
        'title' => 'Activity 4.',
        'columnName' => 'activity1_alias.label',
      ),
      'Activity_5.' => array(
        'title' => 'Activity 5.',
        'columnName' => 'activity1_alias.label',
      ),
      'US_Social_Security_Number' => array(
        'title' => 'US Social Security Number',
      ),
      'Current_Mailing_Address_-_Street_and_Number' => array(
        'title' => 'Current Mailing Address - Street and Number',
      ),
      'Permanent_Foreign_Address_-_Street_and_Number' => array(
        'title' => 'Permanent Foreign Address - Street and Number',
      ),
      'Are_you_currently_studying_in_a_U.S._college_or_university?' => array(
        'title' => 'Are you currently studying in a U.S. college or university?',
        'is_alias' => TRUE,
        'alias_new' => 'Are you currently studying in a U.S. college or university?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'What_type_of_U.S._Visa_do_you_have?' => array(
        'title' => 'What type of U.S. Visa do you have?',
        'is_alias' => TRUE,
        'alias_new' => 'What type of U.S. Visa do you have?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Transfer_College_Name' => array(
        'title' => 'Transfer College Name',
      ),
      'Current_Street_Address_Line_2' => array(
        'title' => 'Current Street Address Line 2',
      ),
      'Current_Street_Address_Line_3' => array(
        'title' => 'Current Street Address Line 3',
      ),
      'City_2' => array(
        'title' => 'City',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 318,
      ),
      'Postal_Code' => array(
        'title' => 'Postal Code',
      ),
      'Province_or_State' => array(
        'title' => 'Province or State',
      ),
      'Phone_Number' => array(
        'title' => 'Phone Number',
      ),
      'High_School_GPA' => array(
        'title' => 'High School GPA',
      ),
      'High_School_Graduation_Year' => array(
        'title' => 'High School Graduation Year',
      ),
      'First_Name_2' => array(
        'title' => 'First Name',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 326,
      ),
      'Last_Name_2' => array(
        'title' => 'Last Name',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 327,
      ),
      'First_Name_3' => array(
        'title' => 'First Name',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 328,
      ),
      'Last_Name_3' => array(
        'title' => 'Last Name',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 329,
      ),
      '1._College_Attended' => array(
        'title' => '1. College Attended',
      ),
      'Begin_Date' => array(
        'title' => 'Begin Date',
      ),
      'End_Date' => array(
        'title' => 'End Date',
      ),
      'Graduated' => array(
        'title' => 'Graduated',
        'is_alias' => TRUE,
        'alias_new' => 'Graduated',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Dual_Credit_College' => array(
        'title' => 'Dual Credit College',
      ),
      '2._College_Attended' => array(
        'title' => '2. College Attended',
      ),
      'Begin_Date_1' => array(
        'title' => 'Begin Date',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 342,
      ),
      'End_Date_1' => array(
        'title' => 'End Date',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 343,
      ),
      '3._College_Attended' => array(
        'title' => '3. College Attended',
      ),
      'Begin_Date_2' => array(
        'title' => 'Begin Date',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 345,
      ),
      'End_Date_2' => array(
        'title' => 'End Date',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 346,
      ),
      'Graduated_1' => array(
        'title' => 'Graduated',
        'is_alias' => TRUE,
        'alias_new' => 'Graduated',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 347,
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Graduated_2' => array(
        'title' => 'Graduated',
        'is_alias' => TRUE,
        'alias_new' => 'Graduated',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 348,
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
    );

    CRM_Nrm_BAO_Nrm::reportSelectClause($this, $columns);
  }

  function from() {
    $custom = array(
      180 => 'ethnicity',
      181 => 'marital',
      202 => 'race',
      200 => 'enrollment_status',
      227 => 'veteran',
      160 => 'admit',
      161 => 'term',
      228 => 'majorcourse',
      535 => 'activity1',
    );
    CRM_Nrm_BAO_Nrm::reportFromClause($this->_from, TRUE, array(), $custom);
  }

  function where() {
    CRM_Nrm_BAO_Nrm::reportWhereClause($this->_where, 417);
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
  }
}
