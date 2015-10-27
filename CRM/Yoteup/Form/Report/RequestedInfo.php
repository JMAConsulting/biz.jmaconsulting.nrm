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
    $select = $this->_columnHeaders = array();

    $this->_columnHeaders['Chowan_ID']['title'] = ts("Chowan ID");
    $this->_columnHeaders['Submitted_Date']['title'] = ts("Submitted Date");
    $this->_columnHeaders['Existing_Contact']['title'] = ts("Existing Contact");
    $this->_columnHeaders['First_Name']['title'] = ts("First Name");
    $this->_columnHeaders['Last_Name']['title'] = ts("Last Name");
    $this->_columnHeaders['Middle_Name']['title'] = ts("Middle Name");
    $this->_columnHeaders['Email_Address']['title'] = ts("Email Address");
    $this->_columnHeaders['Permanent_Address_Line_1']['title'] = ts("Permanent Address Line 1");
    $this->_columnHeaders['Permanent_Address_Line_2']['title'] = ts("Permanent Address Line 2");
    $this->_columnHeaders['City']['title'] = ts("City");
    $this->_columnHeaders['Zip_Code']['title'] = ts("Zip Code");
    $this->_columnHeaders['State_Abbr']['title'] = ts("State Abbr");
    $this->_columnHeaders['State']['title'] = ts("State");
    $this->_columnHeaders['Country']['title'] = ts("Country");
    $this->_columnHeaders['Country_Code']['title'] = ts("Country Code");
    $this->_columnHeaders['Gender']['title'] = ts("Gender");
    $this->_columnHeaders['High_School_Graduation_Date']['title'] = ts("High School Graduation Date");
    $this->_columnHeaders['Primary_Phone_Number']['title'] = ts("Primary Phone Number");
    $this->_columnHeaders['Primary_Phone_Type']['title'] = ts("Primary Phone Type");
    $this->_columnHeaders['High_School_Attended']['title'] = ts("High School Attended");
    $this->_columnHeaders['Secondary_Phone_Number']['title'] = ts("Secondary Phone Number");
    $this->_columnHeaders['Secondary_Phone_Type']['title'] = ts("Secondary Phone Type");
    $this->_columnHeaders['Secondary_Phone_Type']['title'] = ts("Secondary Phone Type");
    $this->_columnHeaders['Type_of_Inquiry']['title'] = ts("Type of Inquiry");
    $this->_columnHeaders['College_Attended_(if_any)']['title'] = ts("College Attended (if any)");
    $this->_columnHeaders['Date_of_Birth']['title'] = ts("Date of Birth");

    $this->_select = "
      SELECT sq.*, sp.name AS 'State' FROM 
      (SELECT wsd.sid, DATE(FROM_UNIXTIME(ws.completed)) AS 'Submitted Date', contact_civireport.external_identifier AS 'Chowan ID',
      GROUP_CONCAT(if(wc.name='Existing Contact', wsd.data, NULL)) AS 'Existing Contact',
      GROUP_CONCAT(if(wc.name='First Name', wsd.data, NULL)) AS 'First Name',
      GROUP_CONCAT(if(wc.name='Last Name', wsd.data, NULL)) AS 'Last Name',
      GROUP_CONCAT(if(wc.name='Middle Name', wsd.data, NULL)) AS 'Middle Name',
      GROUP_CONCAT(if(wc.name='Email Address', wsd.data, NULL)) AS 'Email Address',
      GROUP_CONCAT(if(wc.name='Permanent Address Line 1', wsd.data, NULL)) AS 'Permanent Address Line 1',
      GROUP_CONCAT(if(wc.name='Permanent Address Line 2', wsd.data, NULL)) AS 'Permanent Address Line 2',
      GROUP_CONCAT(if(wc.name='City', wsd.data, NULL)) AS 'City',
      GROUP_CONCAT(if(wc.name='Zip Code', wsd.data, NULL)) AS 'Zip Code',
      GROUP_CONCAT(if(wc.name='State', wsd.data, NULL)) AS 'State Abbr',
      GROUP_CONCAT(if(wc.name='Country', c.name, NULL)) AS 'Country',
      GROUP_CONCAT(if(wc.name='Country', wsd.data, NULL)) AS 'Country Code',
      GROUP_CONCAT(if(wc.name='Gender', g.label, NULL)) AS 'Gender',
      GROUP_CONCAT(if(wc.name='High School Graduation Date', wsd.data, NULL)) AS 'High School Graduation Date',
      GROUP_CONCAT(if(wc.name='Primary Phone Number', wsd.data, NULL)) AS 'Primary Phone Number',
      GROUP_CONCAT(if(wc.name='Primary Phone Type', pt1.label, NULL)) AS 'Primary Phone Type',                    
      GROUP_CONCAT(if(wc.name='High School Attended', wsd.data, NULL)) AS 'High School Attended',
      GROUP_CONCAT(if(wc.name='Secondary Phone Number', wsd.data, NULL)) AS 'Secondary Phone Number',
      GROUP_CONCAT(if(wc.name='Secondary Phone Type', pt2.label, NULL)) AS 'Secondary Phone Type',
      GROUP_CONCAT(if(wc.name='Type of Inquiry', i.label, NULL)) AS 'Type of Inquiry',
      GROUP_CONCAT(if(wc.name='College Attended (if any)', wsd.data, NULL)) AS 'College Attended (if any)',
      GROUP_CONCAT(if(wc.name='Date of Birth', wsd.data, NULL)) AS 'Date of Birth'";
  }

  function from() { 
    $this->_from = "FROM {$this->_drupalDatabase}.webform_submitted_data wsd 
      LEFT JOIN civicrm_contact contact_civireport ON wsd.data = contact_civireport.id AND wsd.cid = 2
      LEFT JOIN {$this->_drupalDatabase}.webform_component wc ON wc.cid = wsd.cid 
      LEFT JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.sid = wsd.sid 
      LEFT JOIN civicrm_option_value g ON wsd.data COLLATE utf8_unicode_ci = g.value AND g.option_group_id = 3
      LEFT JOIN civicrm_option_value pt1 ON wsd.data COLLATE utf8_unicode_ci = pt1.value AND pt1.option_group_id = 35
      LEFT JOIN civicrm_option_value pt2 ON wsd.data COLLATE utf8_unicode_ci = pt2.value AND pt2.option_group_id = 35
      LEFT JOIN civicrm_country c ON wsd.data = c.id
      LEFT JOIN civicrm_option_value i ON wsd.data COLLATE utf8_unicode_ci = i.value AND i.option_group_id = 173";
  }

  function where() {
    $clauses = array();
    $clauses[] = " wc.nid = 72 AND wsd.nid = 72";
    $clauses[] = " DATE(FROM_UNIXTIME(ws.completed)) = DATE(NOW() - INTERVAL 1 DAY)";
    
    if (!empty($clauses)) {
      $this->_where = "WHERE " . implode(' AND ', $clauses);
    }
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
