<?php

class CRM_Yoteup_Form_Report_VipApplication extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('VIP Application Report'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'Contact_ID' => array(
        'title' => 'Contact ID',
      ),
      'Enrollment_Term' => array(
        'title' => 'Enrollment Term',
      ),
      'Enrollment_Status' => array(
        'title' => 'Enrollment Status',
        'columnName' => 'enroll_alias.label',
      ),
      'Intended_Major' => array(
        'title' => 'Intended Major',
      ),
      'Legal_First_Name' => array(
        'title' => 'Legal First Name',
      ),
      'Legal_Middle_Name' => array(
        'title' => 'Legal Middle Name',
      ),
      'Legal_Last_Name' => array(
        'title' => 'Legal Last Name',
      ),
      'Nickname' => array(
        'title' => 'Nickname',
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
      'Address_Line_2' => array(
        'title' => 'Address Line 2',
      ),
      'Address_Line_3' => array(
        'title' => 'Address Line 3',
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
      'State_of_Official_Residence' => array(
        'title' => 'State of Official Residence',
      ),
      'Phone_Number' => array(
        'title' => 'Phone Number',
      ),
      'Please_send_occasional_admissions_related_news_and_updates_to_my_phone_as_text_messages' => array(
        'title' => 'Please send occasional admissions related news and updates to my phone as text messages',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'How_did_you_become_interested_in_The_College_of_Idaho_and_why_are_you_applying_for_admission?' => array(
        'title' => 'How did you become interested in The College of Idaho and why are you applying for admission?',
      ),
      'Are_you_a_U.S._Citizen?' => array(
        'title' => 'Are you a U.S. Citizen?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Citizen_of' => array(
        'title' => 'Citizen of',
      ),
      'What_is_your_current_Visa_Status_in_the_US?' => array(
        'title' => 'What is your current Visa Status in the US?',
      ),
      'Number_of_years_residing_in_the_USA' => array(
        'title' => 'Number of years residing in the USA',
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
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 61,
      ),
      'Have_you_attended_another_college?' => array(
        'title' => 'Have you attended another college?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
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
      'Degrees_Earned_1' => array(
        'title' => 'Degrees Earned',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 55,
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
      'Degrees_Earned_2' => array(
        'title' => 'Degrees Earned',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 51,
      ),
      'High_School_GPA' => array(
        'title' => 'High School GPA',
      ),
      'Have_you_ever_been_dismissed_for_academic_or_disciplinary_reasons_from_a_secondary_school_or_college?' => array(
        'title' => 'Have you ever been dismissed for academic or disciplinary reasons from a secondary school or college?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'If_yes,_please_explain' => array(
        'title' => 'If yes, please explain',
      ),
      'Have_you_ever_been_convicted_of_a_crime,_other_than_a_minor_traffic_violation?' => array(
        'title' => 'Have you ever been convicted of a crime, other than a minor traffic violation?',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'If_yes,_please_explain_2' => array(
        'title' => 'If yes, please explain 2',
      ),
      'Date_Taken_(or_planned)' => array(
        'title' => 'Date Taken (or planned)',
      ),
      'Critical_Reading_Score' => array(
        'title' => 'Critical Reading Score',
      ),
      'Math_Score' => array(
        'title' => 'Math Score',
      ),
      'Writing_Score' => array(
        'title' => 'Writing Score',
      ),
      'ACT_Date_Taken_(or_planned)' => array(
        'title' => 'ACT Date Taken (or planned)',
      ),
      'ACT_Score' => array(
        'title' => 'ACT Score',
      ),
      'TOEFL_Date' => array(
        'title' => 'TOEFL Date',
      ),
      'TOEFL_Score' => array(
        'title' => 'TOEFL Score',
      ),
      'First_Name_1' => array(
        'title' => 'First Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 124,
      ),
      'Last_Name_1' => array(
        'title' => 'Last Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 180,
      ),
      'Phone_1' => array(
        'title' => 'Phone',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 125,
      ),
      'Email_1' => array(
        'title' => 'Email',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 126,
      ),
      'Relationship_1' => array(
        'title' => 'Relationship',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 181,
      ),
      'Education_Level_1' => array(
        'title' => 'Education Level',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 127,
        'columnName' => 'edu_1_alias.label',
      ),
      'Institution(s)_attended_1' => array(
        'title' => 'Institution(s) attended',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 182,
      ),
      'First_Name_2' => array(
        'title' => 'First Name',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 128,
      ),
      'Last_Name_2' => array(
        'title' => 'Last Name',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 187,
      ),
      'Phone_2' => array(
        'title' => 'Phone',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 129,
      ),
      'Email_2' => array(
        'title' => 'Email',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 130,
      ),
      'Relationship_2' => array(
        'title' => 'Relationship',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 183,
      ),
      'Education_Level_2' => array(
        'title' => 'Education Level',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 131,
        'columnName' => 'edu_2_alias.label',
      ),
      'Institution(s)_attended_2' => array(
        'title' => 'Institution(s) attended',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 184,
      ),
      'Are_you_of_Hispanic_or_Latino_descent' => array(
        'title' => 'Are you of Hispanic or Latino descent',
        'columnName' => "IF(wsd.data=0, 'No', 'Yes')",
      ),
      'Please_select_the_category_that_best_reflects_your_ethnic_background' => array(
        'title' => 'Please select the category that best reflects your ethnic background',
        'columnName' => 'category_alias.label',
      ),
      'Athletic_Interests' => array(
        'title' => 'Athletic Interests',
        'columnName' => 'athletic_alias.label',
      ),
      'Activity_Interests' => array(
        'title' => 'Activity Interests',
        'columnName' => 'activity_alias.label',
      ),
    );

    CRM_Yoteup_BAO_Yoteup::reportSelectClause($this, $columns);
  }

  function from() { 
    $custom = array(
      167 => 'category',
      159 => 'athletic',
      158 => 'activity',
      195 => 'edu_1',
      196 => 'edu_2',
      160 => 'enroll',
    );
    CRM_Yoteup_BAO_Yoteup::reportFromClause($this->_from, FALSE, array(), $custom);
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
