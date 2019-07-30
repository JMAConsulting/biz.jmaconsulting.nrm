<?php

class CRM_Nrm_Form_Report_RequestedInfo19 extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('Requested Information Report for 2019'));
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
      'Permanent_Address' => array(
        'title' => 'Permanent Address',
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
      'Secondary_Phone_Type' => array(
        'title' => 'Secondary Phone Type',
        'columnName' => 'pt2.label',
      ),
      'Secondary_Phone_Number' => array(
        'title' => 'Secondary Phone Number',
      ),
      'Date_of_Birth' => array(
        'title' => 'Date of Birth',
      ),
      'Gender' => array(
        'title' => 'Gender',
        'columnName' => 'g.label',
      ),
      'Intended_Major' => array(
        'title' => 'Intended Major',
        'columnName' => 'major_alias.label',
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
      'Comments/Questions' => array(
        'title' => 'Comments/Questions',
      ),
      'HS_Grad_Year' => array(
        'title' => 'HS Grad Year',
      ),
    );

    CRM_Nrm_BAO_Nrm::reportSelectClause($this, $columns, TRUE, TRUE, 551);
  }

  function from() {
    $custom = array(
      //0 => 'inquiry',
      171 => 'major',
    );
    CRM_Nrm_BAO_Nrm::reportFromClause($this->_from, TRUE, array('inquiry'), $custom);
  }

  function where() {
    CRM_Nrm_BAO_Nrm::reportWhereClause($this->_where, 551);
  }

  function groupBy() {
    $this->_groupBy = "GROUP BY wsd.sid";
  }

  function orderBy() {
    return FALSE;
  }

  function postProcess() {

    $this->beginPostProcess();
    CRM_Nrm_BAO_Nrm::createInquiry(551);

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
