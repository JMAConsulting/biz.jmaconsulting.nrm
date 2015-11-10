<?php

class CRM_Yoteup_Form_Report_Admission extends CRM_Report_Form {

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE; 
  protected $_optionGroups = array(); 

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
    $this->_optionGroups = array(
      'Primary_Academic_Interest' => array(133, 167),
      'reflects_your_ethnic_background' => array(89, 132),
      'Relationship_1' => array(136, 239),
      'Relationship_2' => array(136, 240),
      'paren_reside' => array(136, 199),
      'plan_to_live' => array(138, 213),
      'hear_about_Brevard' => array(141, 242),
      'Entry_Year' => array(134, 204),
      'Application_Type' => array(135, 146),
    );
    $this->_otherOptions= array(
     'civicrm_1_contact_1_contact_suffix_id' => 70,
     'civicrm_1_contact_1_cg7_custom_419' => 70,
     'civicrm_1_contact_1_cg7_custom_420' => 70,
    );
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
        'columnTitle' => 'submitted_date',
        'columnName' => 'DATE(FROM_UNIXTIME(ws.completed))',
      ),
      'Entry_Year' => array(
        'title' => 'Entry Year',
        'columnTitle' => 'Entry_Year',
        'columnName' => 'Entry_Year.name', 
      ),
      'Application_Type' => array(
        'title' => 'Application Type',
        'columnTitle' => 'application_type',
        'columnName' => 'Application_Type.name', 
      ),
      'First_Name_1' => array(
        'title' => 'First Name',
        'columnTitle' => 'First_Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 156,
      ),
      'Middle_Name' => array(
        'title' => 'Middle Name',
        'columnTitle' => 'Middle_Name',
      ),
      'Last_Name_1' => array(
        'title' => 'Last Name',
        'columnTitle' => 'Last_Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 159,
      ),
      'Name_Suffix' => array(
        'title' => 'Name Suffix',
        'columnTitle' => 'name_suffix',
        'columnName' => 'civicrm_1_contact_1_contact_suffix_id.name', 
      ),
      'Social_Security_Number' => array(
        'title' => 'Social Security Number',
        'columnTitle' => 'ssn',
      ),
      'Preferred_Nickname' => array(
        'title' => 'Preferred/Nickname',
        'columnTitle' => 'Nick_name',
      ),
      'Gender' => array(
        'title' => 'Gender',
        'columnName' => 'g.label',
        'columnTitle' => 'Gender',
      ),
      'Birth_Date' => array(
        'title' => 'Birth Date',
        'columnTitle' => 'Birth_Date',
      ),
      'Street_Address' => array(
        'title' => 'Street Address',
        'columnTitle' => 'Street_Address',
      ),
      'Street_Address_Line_2' => array(
        'title' => 'Address Line 2',
        'columnTitle' => 'Address_Line_2',
      ),
      'City' => array(
        'title' => 'City',
        'columnTitle' => 'City',
      ),
      'County' => array(
        'title' => 'County',
        'columnTitle' => 'County',
      ),
      'State_Province' => array(
        'title' => 'State/Province',
        'columnTitle' => 'State',
      ),
      'Postal_Code' => array(
        'title' => 'Postal Code',
        'columnTitle' => 'Postal_Code',
      ),
      'Country' => array(
        'title' => 'Country',
        'columnName' => 'c.name',
        'columnTitle' => 'Country',
      ),
      'Home_Phone' => array(
        'title' => 'Home Phone',
        'columnTitle' => 'Phone',
      ),
      'Mobile_Phone' => array(
        'title' => 'Mobile Phone',
        'columnTitle' => 'Mobile_Phone',
      ),
      'Email_1' => array(
        'title' => 'Email',
        'same_alias' => TRUE,
        'columnTitle' => 'Email',
        'alias' => 1,
        'cid' => 155,
      ),
      'Primary_Academic_Interest' => array(
        'title' => 'Primary Academic Interest',
        'columnTitle' => 'Academic_Interest',
        'columnName' => 'Primary_Academic_Interest.name',        
      ),
      'Do_you_plan_to_play_an_intercollegiate_sport_at_Brevard' => array(
        'title' => 'Do you plan to play an intercollegiate sport at Brevard?',
        'columnTitle' => 'Intercol_sport',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'If_yes_what_sport' => array(
        'title' => 'If yes, what sport?',
        'columnTitle' => 'what_sport',
      ),
      'Are_you_being_recruited_by_a_Brevard_College_coach' => array(
        'title' => 'Are you being recruited by a Brevard College coach?',
        'columnTitle' => 'coach',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Outdoor_Interests' => array(
        'title' => 'Outdoor Interests',
        'columnTitle' => 'Outdoors_Interests',
      ),
      'Performance_Instrument' => array(
        'title' => 'Performance Instrument',
        'columnTitle' => 'Performance_Instrument',
      ),
      'Voice_Type' => array(
        'title' => 'Voice Type',
        'columnTitle' => 'Voice_Type',
      ),
      'Are_you_a_U_S_Citizen' => array(
        'title' => 'Are you a U.S. Citizen?',
        'columnTitle' => 'Us_citizen',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'If_not_a_U_S_Citizen_of_what_country_are_you_officially_a_citizen' => array(
        'title' => 'If not a U.S. Citizen, of what country are you officially a citizen?',
        'columnTitle' => 'citizen_country',
      ),
      'Country_of_Birth' => array(
        'title' => 'Country of Birth',
        'columnTitle' => 'birth_country',
      ),
      'Current_Visa_Status_in_the_U_S' => array(
        'title' => 'Current Visa Status in the U.S.',
        'columnTitle' => 'visa',
      ),
      'Number_of_years_residing_in_the_USA' => array(
        'title' => 'Number of years residing in the USA',
        'columnTitle' => 'years_residing',
      ),
      'Are_you_a_Veteran' => array(
        'title' => 'Are you a Veteran?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
        'columnTitle' => 'veteran',
      ),
      'Are_you_of_Hispanic_or_Latino_descent' => array(
        'title' => 'Are you of Hispanic or Latino descent',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
        'columnTitle' => 'hisp_y',
      ),
      'Please_select_the_category_that_best_reflects_your_ethnic_background' => array(
        'title' => 'Please select the category that best reflects your ethnic background',
        'columnName' => 'reflects_your_ethnic_background.name', 
        'columnTitle' => 'ethnicity',
      ),
      'Religious_Preference' => array(
        'title' => 'Religious Preference',
        'columnTitle' => 'religion',
      ),
      'First_Name_2' => array(
        'title' => 'First Name',
        'columnTitle' => 'Parent1_first',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 124,
      ),
      'Last_Name_2' => array(
        'title' => 'Last Name',
        'columnTitle' => 'Parent1_last',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 180,
      ),
      'Phone_1' => array(
        'title' => 'Phone',
        'columnTitle' => 'Parent1_phone',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 125,
      ),
      'Email_2' => array(
        'title' => 'Email',
        'columnTitle' => 'parent1_email',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 126,
      ),
      'Education_Level_1' => array(
        'title' => 'Education Level',
        'same_alias' => TRUE,
        'columnTitle' => 'parent1_education',
        'alias' => 1,
        'cid' => 127,
        'columnName' => 'civicrm_1_contact_1_cg7_custom_419.name', 
      ),
      'Relationship_1' => array(
        'title' => 'Relationship',
        'columnTitle' => 'parent1_relationship',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 239,
        'columnName' => 'Relationship_1.name', 
      ),
      'First_Name_3' => array(
        'title' => 'First Name',
        'columnTitle' => 'parent2_first',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 128,
      ),
      'Last_Name_3' => array(
        'title' => 'Last Name',
        'columnTitle' => 'parent2_last',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 187,
      ),
      'Phone_2' => array(
        'title' => 'Phone',
        'columnTitle' => 'parent2_phone',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 129,
      ),
      'Email_3' => array(
        'title' => 'Email',
        'columnTitle' => 'parent2_email',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 130,
      ),
      'Education_Level_2' => array(
        'title' => 'Education Level',
        'columnTitle' => 'parent2_education',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 131,
        'columnName' => 'civicrm_1_contact_1_cg7_custom_420.name', 
      ),
      'Relationship_2' => array(
        'title' => 'Relationship',
        'same_alias' => TRUE,
        'columnTitle' => 'parent2_relationship',
        'alias' => 2,
        'cid' => 240,
        'columnName' => 'Relationship_2.name', 
      ),
      'With_which_parent_do_you_reside' => array(
        'title' => 'With which parent do you reside?',
        'columnTitle' => 'which_parent',
        'columnName' => 'paren_reside.name', 
      ),
      'School_Name_1' => array(
        'title' => 'School Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'columnTitle' => 'school_1',
        'cid' => 42,
      ),
      'School_City_State_1' => array(
        'title' => 'School City & State',
        'same_alias' => TRUE,
        'alias' => 1,
        'columnTitle' => 'location_1',
        'cid' => 43,
      ),
      'Dates_Attended_1' => array(
        'title' => 'Dates Attended',
        'same_alias' => TRUE,
        'alias' => 1,
        'columnTitle' => 'dates_1',
        'cid' => 44,
      ),
      'Graduation_Date_1' => array(
        'title' => 'Graduation Date',
        'same_alias' => TRUE,
        'alias' => 1,
        'columnTitle' => 'grad_1',
        'cid' => 45,
      ),
      'Degree_Earned_1' => array(
        'title' => 'Degree(s) Earned',
        'same_alias' => TRUE,
        'alias' => 1,
        'columnTitle' => 'degree_1',
        'cid' => 46,
      ),
      'School_Name_2' => array(
        'title' => 'School Name',
        'same_alias' => TRUE,
        'alias' => 2,
        'columnTitle' => 'school_2',
        'cid' => 57,
      ),
      'School_City_State_2' => array(
        'title' => 'School City & State',
        'same_alias' => TRUE,
        'alias' => 2,
        'columnTitle' => 'location_2',
        'cid' => 58,
      ),
      'Dates_Attended_2' => array(
        'title' => 'Dates Attended',
        'same_alias' => TRUE,
        'alias' => 2,
        'columnTitle' => 'dates_2',
        'cid' => 59,
      ),
      'Graduation_Date_2' => array(
        'title' => 'Graduation Date',
        'same_alias' => TRUE,
        'alias' => 2,
        'columnTitle' => 'grad_2',
        'cid' => 60,
      ),
      'Degree_Earned_2' => array(
        'title' => 'Degree(s) Earned',
        'same_alias' => TRUE,
        'alias' => 2,
        'columnTitle' => 'degree_2',
        'cid' => 61,
      ),
      'Have_you_ever_taken_a_college_level_course' => array(
        'title' => 'Have you ever taken a college level course?',
        'columnTitle' => 'college_class',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Did_you_Do_you_participate_in_early_college' => array(
        'columnTitle' => 'Did you/Do you participate in early college?',
        'title' => 'Did you/Do you participate in early college?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Have_you_attended_another_college' => array(
        'title' => 'Have you attended another college?',
        'columnTitle' => 'other_college',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'School_Name_3' => array(
        'title' => 'School Name',
        'columnTitle' => 'school_3',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 52,
      ),
      'School_City_State_3' => array(
        'title' => 'School City & State',
        'columnTitle' => 'location_3',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 53,
      ),
      'Dates_Attended_3' => array(
        'title' => 'Dates Attended',
        'columnTitle' => 'dates_3',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 54,
      ),
      'Graduation_Date_3' => array(
        'title' => 'Graduation Date',
        'same_alias' => TRUE,
        'columnTitle' => 'grad_3',
        'alias' => 3,
        'cid' => 55,
      ),
      'Degree_Earned_3' => array(
        'title' => 'Degrees Earned',
        'same_alias' => TRUE,
        'columnTitle' => 'degree_3',
        'alias' => 3,
        'cid' => 56,
      ),
      'School_Name_4' => array(
        'title' => 'School Name',
        'columnTitle' => 'school_4',
        'same_alias' => TRUE,
        'alias' => 4,
        'cid' => 47,
      ),
      'School_City_State_4' => array(
        'title' => 'School City & State',
        'columnTitle' => 'location_4',
        'same_alias' => TRUE,
        'alias' => 4,
        'cid' => 48,
      ),
      'Dates_Attended_4' => array(
        'title' => 'Dates Attended',
        'columnTitle' => 'dates_4',
        'same_alias' => TRUE,
        'alias' => 4,
        'cid' => 49,
      ),
      'Graduation_Date_4' => array(
        'title' => 'Graduation Date',
        'columnTitle' => 'grad_4',
        'same_alias' => TRUE,
        'alias' => 4,
        'cid' => 50,
      ),
      'Degree_Earned_4' => array(
        'title' => 'Degrees Earned',
        'columnTitle' => 'degree_4',
        'same_alias' => TRUE,
        'alias' => 4,
        'cid' => 51,
      ),
      'Unweighted_GPA' => array(
        'title' => 'Unweighted GPA',
      ),
      'I_would_like_to_apply_Test_Optional' => array(
        'title' => 'I would like to apply Test Optional',
        'columnTitle' => 'gpa test_optional',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Answer_these_questions' => array(
        'title' => 'Answer these questions',
      ),
      'Potential_Indication' => array(
        'title' => 'Potential Indication',
        'columnTitle' => 'Potential_Indication',
      ),
      'Experiential_Learning' => array(
        'title' => 'Experiential Learning',
        'columnTitle' => 'Experiential_Learning',
      ),
      'Significant_Experience' => array(
        'title' => 'Significant Experience',
        'columnTitle' => 'Significant_Experience',
      ),
      'Learning_Community' => array(
        'title' => 'Learning Community',
        'columnTitle' => 'Learning_Community',
      ),
      'Describe_Yourself' => array(
        'title' => 'Describe Yourself',
        'columnTitle' => 'Describe_Yourself',
      ),
      'Date_Taken' => array(
        'title' => 'Date Taken (or planned)',
        'columnTitle' => 'sat_date',
      ),
      'Critical_Reading_Score' => array(
        'title' => 'Critical Reading Score',
        'columnTitle' => 'sat_reading',
      ),
      'Math_Score' => array(
        'title' => 'Math Score',
        'columnTitle' => 'sat_math',
      ),
      'Writing_Score' => array(
        'title' => 'Writing Score',
        'columnTitle' => 'sat_writing',
      ),
      'ACT_Date_Taken_or_planned' => array(
        'title' => 'ACT Date Taken (or planned)',
        'columnTitle' => 'act_date',
      ),
      'ACT_Composite_Score' => array(
        'title' => 'ACT Composite Score',
        'columnTitle' => 'act_score',
      ),
      'Have_you_ever_been_dismissed_for_academic_or_disciplinary_reasons_from_a_secondary_school_or_college' => array(
        'title' => 'Have you ever been dismissed for academic or disciplinary reasons from a secondary school or college?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
        'columnTitle' => 'been_dismissed_y',
      ),
      'If_yes_please_explain' => array(
        'title' => 'If yes, please explain',
        'columnTitle' => 'dismissed_explain',
      ),
      'Have_you_ever_been_convicted_of_a_crime_other_than_a_minor_traffic_violation' => array(
        'title' => 'Have you ever been convicted of a crime, other than a minor traffic violation?',
        'columnTitle' => 'been_convicted_y',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'If_yes_please_explain_2' => array(
        'title' => 'If yes, please explain 2',
        'columnTitle' => 'convict_explain',
      ),
      'Do_you_intend_to_Graduate_from_Brevard' => array(
        'title' => 'Do you intend to Graduate from Brevard?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
        'columnTitle' => 'graduate',
      ),
      'How_you_hear_about_Brevard' => array(
        'title' => 'How did you hear about Brevard?',
        'columnName' => 'hear_about_Brevard.name', 
        'columnTitle' => 'hear_about',
      ),
      'What_other_colleges_or_universities_are_you_applying_to' => array(
        'title' => 'What other colleges or universities are you applying to?',
        'columnTitle' => 'other_apps',
      ),
      'Where_do_you_plan_to_live' => array(
        'title' => 'Where do you plan to live?',
        'columnName' => 'plan_to_live.name', 
        'columnTitle' => 'plan_to_live',
      ),
    );
    CRM_Yoteup_BAO_Yoteup::reportSelectClause($this, $columns);
  }

  function from() {
    $temptables = array_merge($this->_optionGroups, $this->_otherOptions);
    CRM_Yoteup_BAO_Yoteup::reportFromClause($this->_from, TRUE, array_keys($temptables), array(), 117);
  }

  function where() {
    CRM_Yoteup_BAO_Yoteup::reportWhereClause($this->_where, 70, 117);
  }

  function groupBy() {
    $this->_groupBy = "GROUP BY wsd.sid";
  }

  function orderBy() {
    return FALSE;
  }

  function postProcess() {

    $this->beginPostProcess();
    self::createTemp($this->_optionGroups);
    $config = CRM_Core_Config::singleton();
    $dsnArray = DB::parseDSN($config->userFrameworkDSN);
    $drupalDb = $dsnArray['database'];
    self::createTemp($this->_otherOptions, FALSE, $drupalDb);
    $sql = $this->buildQuery(FALSE);

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  
  function createTemp($tempTables, $isOptionGroup = TRUE, $drupalDb = NULL) {
    foreach ($tempTables as $tableName => $optId) {
      $result = $vals = array();
      if ($isOptionGroup) {
        $sql = "SELECT label, value FROM civicrm_option_value WHERE option_group_id = {$optId[0]}";
        $cid = $optId[1];
      }
      else {
        $sql = "SELECT extra, cid
          FROM {$drupalDb}.webform_component
          WHERE form_key = '$tableName' AND nid = $optId";
      }
      $dao = CRM_Core_DAO::executeQuery($sql);
      while ($dao->fetch()) {
        if ($isOptionGroup) {
          $result[$dao->value] = $dao->label;
        }
        else {
          $result = unserialize($dao->extra);
          $result = explode("\n", $result['items']);
          $cid = $dao->cid;
        }
      }
      CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS {$tableName}");
      CRM_Core_DAO::executeQuery("CREATE TEMPORARY TABLE IF NOT EXISTS {$tableName} (
        cid int(10) NOT NULL,
        value varchar(64) NOT NULL,
        name varchar(64) NOT NULL)"
      );
      $sql = "INSERT INTO {$tableName} VALUES";
      foreach ($result as $key => $items) {
        if ($items) {
          if (!$isOptionGroup) {
            list($key, $items) = explode('|', $items);
          }
          $items = addslashes($items);
          $vals[] = " ($cid, '{$key}', '{$items}')";
        }
      }
      $sql .= implode(',', $vals);
      CRM_Core_DAO::executeQuery($sql);
    }
  }

  function alterDisplay(&$rows) {
  }
}
