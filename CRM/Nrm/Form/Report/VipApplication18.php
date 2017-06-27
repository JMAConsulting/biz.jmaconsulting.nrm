<?php

class CRM_Nrm_Form_Report_VipApplication18 extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('VIP Application Report for 2018'));
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
      'Name_Prefix' => array(
        'title' => 'Prefix',
        'is_alias' => TRUE,
        'alias_new' => 'Name Prefix',
        'columnName' => 'prefixes_alias.label',
      ),
      'First_Name_1' => array(
        'title' => 'First Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 156,
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
      'Preferred_Name' => array(
        'title' => 'Preferred Name',
      ),
      'Enrollment_Year' => array(
        'title' => 'Enrollment Year',
      ),
      'Enrollment_Term' => array(
        'title' => 'Enrollment Term',
      ),
      'Enrollment_Classification' => array(
        'title' => 'Enrollment Classification',
      ),
      'Enrollment_Status' => array(
        'title' => 'Enrollment Status',
        'columnName' => 'enrollment_status_alias.label',
      ),
      'Intended_Major' => array(
        'title' => 'Intended Major',
        'columnName' => 'major_alias.label',
      ),
      'Permanent_Address' => array(
        'title' => 'Permanent Address',
      ),
      'Permanent_Address_Line_2' => array(
        'title' => 'Permanent Address Line 2',
      ),
      'City_3' => array(
        'title' => 'City',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 149,
      ),
      'State_3' => array(
        'title' => 'State',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 153,
      ),
      'Zip_3' => array(
        'title' => 'Zip',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 150,
      ),
      'Email_1' => array(
        'title' => 'Email',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 155,
      ),
      'Primary_Phone_Number' => array(
        'title' => 'Primary Phone',
        'is_alias' => TRUE,
        'alias_new' => 'Primary Phone Number',
      ),
      'Primary_Phone_Type' => array(
        'title' => 'Primary Phone Type',
        'columnName' => 'pt1.label',
      ),
      'Secondary_Phone_Number' => array(
        'title' => 'Secondary Phone',
        'is_alias' => TRUE,
        'alias_new' => 'Secondary Phone Number',
      ),
      'Secondary_Phone_Type' => array(
        'title' => 'Secondary Phone Type',
        'columnName' => 'pt2.label',
      ),
      'Date_of_Birth' => array(
        'title' => 'Date of Birth',
      ),
      'Age' => array(
        'title' => 'Age',
      ),
      'City_and_State_of_Birth' => array(
        'title' => 'City and State of Birth',
      ),
      'Gender' => array(
        'title' => 'Gender',
        'columnName' => 'g.label',
      ),
      'Marital_Status' => array(
        'title' => 'Marital Status',
      ),
      'Social_Security_Number' => array(
        'title' => 'Social Security Number',
      ),
      'Are_you_a_U_S__Citizen?' => array(
        'title' => 'USA Citizen?',
        'is_alias' => TRUE,
        'alias_new' => 'Are you a U.S. Citizen?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'State_of_Legal_Residence' => array(
        'title' => 'State of Legal Residence',
      ),
      'Country_of_Citizenship' => array(
        'title' => 'If Not, Citizen of',
        'is_alias' => TRUE,
        'alias_new' => 'Country of Citizenship',
        'columnName' => 'c.name',
      ),
      'Visa_Classification' => array(
        'title' => 'Current US Visa Status',
        'is_alias' => TRUE,
        'alias_new' => 'Visa Classification',
      ),
      'School_Name_1' => array(
        'title' => 'School Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 42,
      ),
      'School_City_&_State_1' => array(
        'title' => 'School City & State',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 43,
      ),
      'Dates_Attended_1' => array(
        'title' => 'Dates Attended',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 44,
      ),
      'Graduation_Date_1' => array(
        'title' => 'Graduation Date',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 45,
      ),
      'Degree(s)_Earned_1' => array(
        'title' => 'Degree(s) Earned',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 46,
      ),
      'School_Name_2' => array(
        'title' => 'School Name',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 57,
      ),
      'School_City_&_State_2' => array(
        'title' => 'School City & State',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 58,
      ),
      'Dates_Attended_2' => array(
        'title' => 'Dates Attended',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 59,
      ),
      'Graduation_Date_2' => array(
        'title' => 'Graduation Date',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 60,
      ),
      'Degree(s)_Earned_2' => array(
        'title' => 'Degree(s) Earned',
        'alias_new' => 'Degree(s) Earned',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 61,
      ),
      'School_Name_3' => array(
        'title' => 'School Name',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 52,
      ),
      'School_City_&_State_3' => array(
        'title' => 'School City & State',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 53,
      ),
      'Dates_Attended_3' => array(
        'title' => 'Dates Attended',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 54,
      ),
      'Graduation_Date_3' => array(
        'title' => 'Graduation Date',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 55,
      ),
      'Degrees_Earned_3' => array(
        'title' => 'Degrees Earned',
        'alias_new' => 'Degree(s) Earned',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 56,
      ),
      'School_Name_4' => array(
        'title' => 'School Name',
        'same_alias' => TRUE,
        'alias' => 4,
        'cid' => 47,
      ),
      'School_City_&_State_4' => array(
        'title' => 'School City & State',
        'same_alias' => TRUE,
        'alias' => 4,
        'cid' => 48,
      ),
      'Dates_Attended_4' => array(
        'title' => 'Dates Attended',
        'same_alias' => TRUE,
        'alias' => 4,
        'cid' => 49,
      ),
      'Graduation_Date_4' => array(
        'title' => 'Graduation Date',
        'same_alias' => TRUE,
        'alias' => 4,
        'cid' => 50,
      ),
      'Degrees_Earned_4' => array(
        'title' => 'Degrees Earned',
        'alias_new' => 'Degree(s) Earned',
        'same_alias' => TRUE,
        'alias' => 4,
        'cid' => 51,
      ),
      'SAT_1_Date_Taken_(or_planned)' => array(
        'title' => 'SAT Test Date',
        'is_alias' => TRUE,
        'alias_new' => 'SAT 1 Date Taken (or planned)',
      ),
      'Critical_Reading_Score' => array(
        'title' => 'SAT CR',
        'is_alias' => TRUE,
        'alias_new' => 'Critical Reading Score',
      ),
      'Math_Score' => array(
        'title' => 'SAT M',
        'is_alias' => TRUE,
        'alias_new' => 'Math Score',
      ),
      'Writing_Score' => array(
        'title' => 'SAT W',
        'is_alias' => TRUE,
        'alias_new' => 'Writing Score',
      ),
      'ACT_Date_Taken_(or_planned)' => array(
        'title' => 'ACT Test Date',
        'is_alias' => TRUE,
        'alias_new' => 'ACT Date Taken (or planned)',
      ),
      'Composite_Score' => array(
        'title' => 'ACT Composite',
        'is_alias' => TRUE,
        'alias_new' => 'Composite Score',
      ),
      'TOEFL_Date' => array(
        'title' => 'TOEFL Date',
      ),
      'Score' => array(
        'title' => 'TOEFL Score',
        'is_alias' => TRUE,
        'alias_new' => 'Score',
      ),
      'GPA' => array(
        'title' => 'GPA',
      ),
      'Are_you_enrolled_in_a_Teacher_Cadet_Program?' => array(
        'title' => 'Are you enrolled in Teacher Cadet?',
        'is_alias' => TRUE,
        'alias_new' => 'Are you enrolled in a Teacher Cadet Program?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Have_you_visited_Chowan_University?' => array(
        'title' => 'Visited Chowan?',
        'is_alias' => TRUE,
        'alias_new' => 'Have you visited Chowan University?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'How_did_you_become_interested_in_Chowan_University_and_why_are_you_applying_for_admission?' => array(
        'title' => 'How did you become interested into Chowan University and why are you applying for admission?',
        'is_alias' => TRUE,
        'alias_new' => 'How did you become interested in Chowan University and why are you applying for admission?',
      ),
      'Have_you_ever_been_convicted_of_a_crime,_other_than_a_minor_traffic_violation?' => array(
        'title' => 'Have you ever been convicted of a crime other than a minor traffic violation?',
        'is_alias' => TRUE,
        'alias_new' => 'Have you ever been convicted of a crime, other than a minor traffic violation?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Explanation_1' => array(
        'title' => 'Explanation',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 143,
        'is_alias' => TRUE,
        'alias_new' => 'If yes, please explain:',
      ),
      'Have_you_ever_been_suspended_or_expelled?' => array(
        'title' => 'Have you ever been dismissed for academic or disciplinary reasons from a secondary school or college?',
        'is_alias' => TRUE,
        'alias_new' => 'Have you ever been suspended or expelled?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Explanation_2' => array(
        'title' => 'Explanation',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 142,
        'is_alias' => TRUE,
        'alias_new' => 'If yes, please explain:',
      ),
      'Relationship' => array(
        'title' => 'Relationship',
      ),
      'First_Name_2' => array(
        'title' => 'First Name',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 124,
      ),
      'Last_Name_2' => array(
        'title' => 'Last Name',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 180,
      ),
      'Address_1' => array(
        'title' => 'Address',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 217,
      ),
      'City_1' => array(
        'title' => 'City',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 218,
      ),
      'State_1' => array(
        'title' => 'State',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 240,
      ),
      'Home_Phone_1' => array(
        'title' => 'Home Phone',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 125,
      ),
      'Email_2' => array(
        'title' => 'Email',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 126,
      ),
      'Work_Phone_1' => array(
        'title' => 'Work Phone',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 225,
      ),
      'Employer_1' => array(
        'title' => 'Employer',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 220,
      ),
      'Position_1' => array(
        'title' => 'Position',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 221,
      ),
      'College_Attended_1' => array(
        'title' => 'College Attended',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 222,
        'is_alias' => TRUE,
        'alias_new' => 'College(s) Attended',
      ),
      'Degree(s)_Earned_5' => array(
        'title' => 'Degree(s) Earned',
        'is_alias' => TRUE,
        'alias_new' => 'Degree(s) Earned',
        'same_alias' => TRUE,
        'alias' => 5,
        'cid' => 223,
      ),
      'Relationship_to_Applicant' => array(
        'title' => 'Relationship to Applicant',
      ),
      'First_Name_3' => array(
        'title' => 'First Name',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 128,
      ),
      'Last_Name_3' => array(
        'title' => 'Last Name',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 187,
      ),
      'Address_2' => array(
        'title' => 'Address',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 210,
      ),
      'City_2' => array(
        'title' => 'City',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 211,
      ),
      'State_2' => array(
        'title' => 'State',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 239,
      ),
      'Home_Phone_2' => array(
        'title' => 'Home Phone',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 129,
      ),
      'Work_Phone_2' => array(
        'title' => 'Work Phone',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 224,
      ),
      'Email_3' => array(
        'title' => 'Email',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 130,
      ),
      'Employer_3' => array(
        'title' => 'Employer',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 213,
      ),
      'Position_3' => array(
        'title' => 'Position',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 214,
      ),
      'College_Attended_3' => array(
        'title' => 'College Attended',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 215,
        'is_alias' => TRUE,
        'alias_new' => 'College(s) Attended',
      ),
      'Degree(s_)Earned_6' => array(
        'title' => 'Degree(s )Earned',
        'same_alias' => TRUE,
        'alias' => 6,
        'cid' => 216,
        'is_alias' => TRUE,
        'alias_new' => 'Degree(s )Earned',
      ),
      'hear_about_Chowan_University' => array(
        'title' => 'How did you hear about Chowan University?',
        'columnName' => 'hear_chowan_alias.label',
      ),
      'To_what_other_colleges/universities_are_you_applying?' => array(
        'title' => 'What other colleges or universities are you applying to?',
        'is_alias' => TRUE,
        'alias_new' => 'To what other colleges/universities are you applying?',
      ),
      'Race' => array(
        'title' => 'Ethnicity',
        'is_alias' => TRUE,
        'alias_new' => 'Race',
        'columnName' => 'race_alias.label',
      ),
      'If_other,_please_specify:' => array(
        'title' => 'If other, please specify',
        'is_alias' => TRUE,
        'alias_new' => 'If other, please specify:',
      ),
      'Are_you_of_Hispanic_or_Latino_descent' => array(
        'title' => 'Hispanic or Latino descent?',
        'is_alias' => TRUE,
        'alias_new' => 'Are you of Hispanic or Latino descent',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Church_Affiliation/Denomination' => array(
        'title' => 'Church Affiliation / Denomination',
        'is_alias' => TRUE,
        'alias_new' => 'Church Affiliation/Denomination',
      ),
      'Church_City' => array(
        'title' => 'Church City',
      ),
      'Church_State' => array(
        'title' => 'Church State',
      ),
      'Church_Zip' => array(
        'title' => 'Church Zip',
      ),
      'Are_you_an_active_member?' => array(
        'title' => 'Active member?',
        'is_alias' => TRUE,
        'alias_new' => 'Are you an active member?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'If_any_of_your_relatives_have_attended_Chowan_University,_please_give_their_names_and_relationship:' => array(
        'title' => 'If any of your relatives have attended Chowan University, please give their names and relationship',
        'is_alias' => TRUE,
        'alias_new' => 'If any of your relatives have attended Chowan University, please give their names and relationship:',
      ),
      'Condition_of_Admission' => array(
        'title' => 'Condition of Admission Initials',
        'is_alias' => TRUE,
        'alias_new' => 'Condition of Admission',
      ),
      'Completed_Time' => array(
        'title' => 'Time Completed',
        'is_alias' => TRUE,
        'alias_new' => 'Completed Time',
      ),
    );

    CRM_Nrm_BAO_Nrm::reportSelectClause($this, $columns);
  }

  function from() {
    $custom = array(
      6 => 'prefixes',
      171 => 'major',
      202 => 'race',
      200 => 'enrollment_status',
      201 => 'hear_chowan',
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
