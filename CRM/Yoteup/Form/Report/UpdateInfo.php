<?php

class CRM_Yoteup_Form_Report_UpdateInfo extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('Update Information Report'));
    parent::preProcess();
  }

  function select() {
    $select = $this->_columnHeaders = array();

    $this->_columnHeaders['Chowan_ID']['title'] = ts("Chowan ID");
    $this->_columnHeaders['Submitted_Date']['title'] = ts("Submitted Date");
    $this->_columnHeaders['First_Name']['title'] = ts("First Name");
    $this->_columnHeaders['Middle_Name']['title'] = ts("Middle Name");
    $this->_columnHeaders['Last_Name']['title'] = ts("Last Name");
    $this->_columnHeaders['Name_Suffix']['title'] = ts("Name Suffix");
    $this->_columnHeaders['Nickname']['title'] = ts("Nickname");
    $this->_columnHeaders['Gender']['title'] = ts("Gender");
    $this->_columnHeaders['Birth_Date']['title'] = ts("Birth Date");
    $this->_columnHeaders['Email']['title'] = ts("Email");
    $this->_columnHeaders['Street_Address']['title'] = ts("Street Address");
    $this->_columnHeaders['Street_Address_Line_2']['title'] = ts("Street Address Line 2");
    $this->_columnHeaders['City']['title'] = ts("City");
    $this->_columnHeaders['State']['title'] = ts("State");
    $this->_columnHeaders['Postal_Code']['title'] = ts("Postal Code");
    $this->_columnHeaders['Postal_Code_Suffix']['title'] = ts("Postal Code Suffix");
    $this->_columnHeaders['Phone_Number']['title'] = ts("Phone Number");
    $this->_columnHeaders['Phone_Type']['title'] = ts("Phone Type");

    $this->_select = "
      SELECT wsd.sid, DATE(FROM_UNIXTIME(ws.completed)) AS 'Submitted Date', contact_civireport.external_identifier AS 'Chowan ID',
      GROUP_CONCAT(if(wc.name='First Name', wsd.data, NULL)) AS 'First Name',
      GROUP_CONCAT(if(wc.name='Last Name', wsd.data, NULL)) AS 'Last Name',
      GROUP_CONCAT(if(wc.name='Middle Name', wsd.data, NULL)) AS 'Middle Name',
      GROUP_CONCAT(if(wc.name='Nickname', wsd.data, NULL)) AS 'Nickname',
      GROUP_CONCAT(if(wc.name='Name Suffix', wsd.data, NULL)) AS 'Name Suffix',
      GROUP_CONCAT(if(wc.name='Gender', g.label, NULL)) AS 'Gender',
      GROUP_CONCAT(if(wc.name='Birth Date', wsd.data, NULL)) AS 'Birth Date',
      GROUP_CONCAT(if(wc.name='Email', wsd.data, NULL)) AS 'Email',
      GROUP_CONCAT(if(wc.name='Street Address', wsd.data, NULL)) AS 'Street Address',
      GROUP_CONCAT(if(wc.name='Street Address Line 2', wsd.data, NULL)) AS 'Street Address Line 2',
      GROUP_CONCAT(if(wc.name='City', wsd.data, NULL)) AS 'City',
      GROUP_CONCAT(if(wc.name='State/Province', wsd.data, NULL)) AS 'State',
      GROUP_CONCAT(if(wc.name='Postal Code', wsd.data, NULL)) AS 'Postal Code',
      GROUP_CONCAT(if(wc.name='Postal Code Suffix', wsd.data, NULL)) AS 'Postal Code Suffix',
      GROUP_CONCAT(if(wc.name='Phone Number', wsd.data, NULL)) AS 'Phone Number',
      GROUP_CONCAT(if(wc.name='Phone Type', pt1.label, NULL)) AS 'Phone Type'";
  }

  function from() { 
    $this->_from = "FROM {$this->_drupalDatabase}.webform_submitted_data wsd 
      LEFT JOIN civicrm_contact contact_civireport ON wsd.data = contact_civireport.id AND wsd.cid = 2
      LEFT JOIN {$this->_drupalDatabase}.webform_component wc ON wc.cid = wsd.cid 
      LEFT JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.sid = wsd.sid 
      LEFT JOIN civicrm_option_value g ON wsd.data COLLATE utf8_unicode_ci = g.value AND g.option_group_id = 3
      LEFT JOIN civicrm_option_value pt1 ON wsd.data COLLATE utf8_unicode_ci = pt1.value AND pt1.option_group_id = 35
      LEFT JOIN civicrm_country c ON wsd.data = c.id";
  }

  function where() {
    $clauses = array();
    $clauses[] = " wc.nid = 67 AND wsd.nid = 67";
    $clauses[] = " DATE(FROM_UNIXTIME(ws.completed)) = DATE(NOW() - INTERVAL 1 DAY)";
    
    if (!empty($clauses)) {
      $this->_where = "WHERE " . implode(' AND ', $clauses);
    }
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
