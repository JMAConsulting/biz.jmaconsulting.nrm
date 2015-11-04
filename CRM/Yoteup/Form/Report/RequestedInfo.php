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
      'Chowan_ID' => array(
        'title' => 'Chowan ID',
        'ignore_group_concat' => TRUE,
        'columnName' => 'GROUP_CONCAT(contact_civireport.external_identifier)',
      ),
      'Submitted_Time' => array(
        'title' => 'Submitted Time',
        'ignore_group_concat' => TRUE,
        'columnName' => "DATE_FORMAT(FROM_UNIXTIME(ws.completed), '%m-%d-%Y %r')",
      ),
      'Type_of_Inquiry' => array(
        'title' => 'Type of Inquiry',
        'columnName' => 'inquiry_alias.name',
      ),
      'Existing_Contact' => array(
        'title' => 'Existing Contact',
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
      'Permanent_Address_Line_1' => array(
        'title' => 'Permanent Address Line 1',
      ),
      'Permanent_Address_Line_2' => array(
        'title' => 'Permanent Address Line 2',
      ),
      'City' => array(
        'title' => 'City',
      ),
      'State' => array(
        'title' => 'State',
      ),
      'Zip_Code' => array(
        'title' => 'Zip Code',
      ),
      'Country' => array(
        'title' => 'Country',
        'columnName' => 'c.name',
      ),
      'Email_Address' => array(
        'title' => 'Email Address',
      ),
      'Primary_Phone_Number' => array(
        'title' => 'Primary Phone Number',
      ),
      'Primary_Phone_Type' => array(
        'title' => 'Primary Phone Type',
        'columnName' => 'pt1.label',
      ),
      'Secondary_Phone_Number' => array(
        'title' => 'Secondary Phone Number',
      ),
      'Secondary_Phone_Type' => array(
        'title' => 'Secondary Phone Type',
        'columnName' => 'pt2.label',
      ),
      'Date_of_Birth' => array(
        'title' => 'Date of Birth',
      ),
      'Gender' => array(
        'title' => 'Gender',
        'columnName' => 'g.label',
      ),
      'High_School_Attended' => array(
        'title' => 'High School Attended',
      ),
      'High_School_Graduation_Date' => array(
        'title' => 'High School Graduation Date',
      ),
      'College_Attended_(if_any)' => array(
        'title' => 'College Attended (if any)',
      ),
    );

    CRM_Yoteup_BAO_Yoteup::reportSelectClause($this, $columns, TRUE);
  }

  function from() { 
    CRM_Yoteup_BAO_Yoteup::reportFromClause($this->_from, TRUE, array('inquiry'));
  }

  function where() {
    CRM_Yoteup_BAO_Yoteup::reportWhereClause($this->_where, 72);
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
