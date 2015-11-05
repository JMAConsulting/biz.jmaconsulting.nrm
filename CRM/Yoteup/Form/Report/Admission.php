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
        'columnName' => 'DATE(FROM_UNIXTIME(ws.completed))',
      ),
      'Entry_Year' => array(
        'title' => 'Entry Year',
        'columnName' => 'Entry_Year.name', 
      ),
      'Application_Type' => array(
        'title' => 'Application Type',
        'columnName' => 'Application_Type.name', 
      ),
      'First_Name_1' => array(
        'title' => 'First Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 156,
      ),
      'Middle_Name' => array(
        'title' => 'Middle Name',
      ),
      'Last_Name_1' => array(
        'title' => 'Last Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 159,
      ),
      'Name_Suffix' => array(
        'title' => 'Name Suffix',
        'columnName' => 'civicrm_1_contact_1_contact_suffix_id.name', 
      ),
      'Social_Security_Number' => array(
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
        'title' => 'Address Line 2',
      ),
      'City' => array(
        'title' => 'City'
      ),
      'County' => array(
        'title' => 'County',
      ),
      'State_Province' => array(
        'title' => 'State/Province',
      ),
      'Postal_Code' => array(
        'title' => 'Postal Code',
      ),
      'Country' => array(
        'title' => 'Country',
        'columnName' => 'c.name',
      ),
      'Home_Phone' => array(
        'title' => 'Home Phone',
      ),
      'Mobile_Phone' => array(
        'title' => 'Mobile Phone',
      ),
      'Email_1' => array(
        'title' => 'Email',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 155,
      ),
      'Primary_Academic_Interest' => array(
        'title' => 'Primary Academic Interest',
        'columnName' => 'Primary_Academic_Interest.name',        
      ),
      'Do_you_plan_to_play_an_intercollegiate_sport_at_Brevard' => array(
        'title' => 'Do you plan to play an intercollegiate sport at Brevard?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'If_yes_what_sport' => array(
        'title' => 'If yes, what sport?',
      ),
      'Outdoor_Interests' => array(
        'title' => 'Outdoor Interests',
      ),
      'Are_you_being_recruited_by_a_Brevard_College_coach' => array(
        'title' => 'Are you being recruited by a Brevard College coach?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Performance_Instrument' => array(
        'title' => 'Performance Instrument',
      ),
      'Voice_Type' => array(
        'title' => 'Voice Type',
      ),
      'Are_you_a_U_S_Citizen' => array(
        'title' => 'Are you a U.S. Citizen?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'If_not_a_U_S_Citizen_of_what_country_are_you_officially_a_citizen' => array(
        'title' => 'If not a U.S. Citizen, of what country are you officially a citizen?',
      ),
      'Country_of_Birth' => array(
        'title' => 'Country of Birth',
      ),
      'Current_Visa_Status_in_the_U_S' => array(
        'title' => 'Current Visa Status in the U.S.',
      ),
      'Number_of_years_residing_in_the_USA' => array(
        'title' => 'Number of years residing in the USA',
      ),
      'Are_you_a_Veteran' => array(
        'title' => 'Are you a Veteran?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Are_you_of_Hispanic_or_Latino_descent' => array(
        'title' => 'Are you of Hispanic or Latino descent',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Please_select_the_category_that_best_reflects_your_ethnic_background' => array(
        'title' => 'Please select the category that best reflects your ethnic background',
        'columnName' => 'reflects_your_ethnic_background.name', 
      ),
      'Religious_Preference' => array(
        'title' => 'Religious Preference',
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
      'Phone_1' => array(
        'title' => 'Phone',
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
      'Education_Level_1' => array(
        'title' => 'Education Level',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 127,
        'columnName' => 'civicrm_1_contact_1_cg7_custom_419.name', 
      ),
      'Relationship_1' => array(
        'title' => 'Relationship',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 239,
        'columnName' => 'Relationship_1.name', 
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
      'Phone_2' => array(
        'title' => 'Phone',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 129,
      ),
      'Email_3' => array(
        'title' => 'Email',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 130,
      ),
      'Education_Level_2' => array(
        'title' => 'Education Level',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 131,
        'columnName' => 'civicrm_1_contact_1_cg7_custom_420.name', 
      ),
      'Relationship_2' => array(
        'title' => 'Relationship',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 240,
        'columnName' => 'Relationship_2.name', 
      ),
      'With_which_parent_do_you_reside' => array(
        'title' => 'With which parent do you reside?',
        'columnName' => 'paren_reside.name', 
      ),
      'School_Name_1' => array(
        'title' => 'School Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 42,
      ),
      'School_City_State_1' => array(
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
      'Degree_Earned_1' => array(
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
      'School_City_State_2' => array(
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
      'Degree_Earned_2' => array(
        'title' => 'Degree(s) Earned',
        'same_alias' => TRUE,
        'alias' => 2,
        'cid' => 61,
      ),
      'Have_you_ever_taken_a_college_level_course' => array(
        'title' => 'Have you ever taken a college level course?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Did_you_Do_you_participate_in_early_college' => array(
        'title' => 'Did you/Do you participate in early college?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Have_you_attended_another_college' => array(
        'title' => 'Have you attended another college?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'School_Name_3' => array(
        'title' => 'School Name',
        'same_alias' => TRUE,
        'alias' => 3,
        'cid' => 52,
      ),
      'School_City_State_3' => array(
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
      'Degree_Earned_3' => array(
        'title' => 'Degrees Earned',
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
      'School_City_State_4' => array(
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
      'Degree_Earned_4' => array(
        'title' => 'Degrees Earned',
        'same_alias' => TRUE,
        'alias' => 4,
        'cid' => 51,
      ),
      'Unweighted_GPA' => array(
        'title' => 'Unweighted GPA',
      ),
      'I_would_like_to_apply_Test_Optional' => array(
        'title' => 'I would like to apply Test Optional',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'Answer_these_questions' => array(
        'title' => 'Answer these questions',
      ),
      'Potential_Indication' => array(
        'title' => 'Potential Indication',
      ),
      'Experiential_Learning' => array(
        'title' => 'Experiential Learning',
      ),
      'Significant_Experience' => array(
        'title' => 'Significant Experience',
      ),
      'Learning_Community' => array(
        'title' => 'Learning Community',
      ),
      'Describe_Yourself' => array(
        'title' => 'Describe Yourself',
      ),
      'Date_Taken' => array(
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
      'ACT_Date_Taken_or_planned' => array(
        'title' => 'ACT Date Taken (or planned)',
      ),
      'ACT_Composite_Score' => array(
        'title' => 'ACT Composite Score',
      ),
      'Have_you_ever_been_dismissed_for_academic_or_disciplinary_reasons_from_a_secondary_school_or_college' => array(
        'title' => 'Have you ever been dismissed for academic or disciplinary reasons from a secondary school or college?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'If_yes_please_explain' => array(
        'title' => 'If yes, please explain',
      ),
      'Have_you_ever_been_convicted_of_a_crime_other_than_a_minor_traffic_violation' => array(
        'title' => 'Have you ever been convicted of a crime, other than a minor traffic violation?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'If_yes_please_explain_2' => array(
        'title' => 'If yes, please explain 2',
      ),
      'Do_you_intend_to_Graduate_from_Brevard' => array(
        'title' => 'Do you intend to Graduate from Brevard?',
        'columnName' => "IF(wsd.data=1, 'Yes', 'No')",
      ),
      'What_other_colleges_or_universities_are_you_applying_to' => array(
        'title' => 'What other colleges or universities are you applying to?',
      ),
      'Where_do_you_plan_to_live' => array(
        'title' => 'Where do you plan to live?',
        'columnName' => 'plan_to_live.name', 
      ),
      'How_you_hear_about_Brevard' => array(
        'title' => 'How did you hear about Brevard?',
        'columnName' => 'hear_about_Brevard.name', 
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
  
  /**
   * @param int $rowCount
   * @return array
   */
  
  public function limit($rowCount = self::ROW_COUNT_LIMIT) {
    // lets do the pager if in html mode
    $this->_limit = NULL;

    // CRM-14115, over-ride row count if rowCount is specified in URL
    if ($this->_dashBoardRowCount) {
      $rowCount = $this->_dashBoardRowCount;
    }
    if ($this->_outputMode == 'html' || $this->_outputMode == 'group') {
      $this->_select = str_replace('SELECT ', 'SELECT SQL_CALC_FOUND_ROWS ', $this->_select);

      $pageId = CRM_Utils_Request::retrieve('crmPID', 'Integer', CRM_Core_DAO::$_nullObject);

      // @todo all http vars should be extracted in the preProcess
      // - not randomly in the class
      if (!$pageId && !empty($_POST)) {
        if (isset($_POST['PagerBottomButton']) && isset($_POST['crmPID_B'])) {
          $pageId = max((int) $_POST['crmPID_B'], 1);
        }
        elseif (isset($_POST['PagerTopButton']) && isset($_POST['crmPID'])) {
          $pageId = max((int) $_POST['crmPID'], 1);
        }
        unset($_POST['crmPID_B'], $_POST['crmPID']);
      }

      $pageId = $pageId ? $pageId : 1;
      $this->set(CRM_Utils_Pager::PAGE_ID, $pageId);
      $offset = ($pageId - 1) * $rowCount;

      $offset = CRM_Utils_Type::escape($offset, 'Int');
      $rowCount = CRM_Utils_Type::escape($rowCount, 'Int');

      $this->_limit = " LIMIT $offset, $rowCount";
      return array($offset, $rowCount);
    }
    if ($this->_limitValue) {
      if ($this->_offsetValue) {
        $this->_limit = " LIMIT {$this->_offsetValue}, {$this->_limitValue} ";
      }
      else {
        $this->_limit = " LIMIT " . $this->_limitValue;
      }
    }
  }

  function postProcess() {

    $this->beginPostProcess();
    self::createTemp($this->_optionGroups);
    $config = CRM_Core_Config::singleton();
    $dsnArray = DB::parseDSN($config->userFrameworkDSN);
    $drupalDb = $dsnArray['database'];
    self::createTemp($this->_otherOptions, FALSE, $drupalDb);
    $sql = $this->buildQuery(TRUE);

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
