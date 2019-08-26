<?php

class CRM_Nrm_Form_Report_RequestedInfoUpike20 extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('Requested Information Report for Upike 2020'));
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
      'Anticipated_Academic_Enroll_Year' => array(
        'title' => 'Anticipated Academic Enroll Year',
        'columnName' => 'entry_alias.label',
      ),
      'Parent/Guardian_First_Name' => array(
        'title' => 'Parent/Guardian First Name',
      ),
      'Parent/Guardian_First_Name_1' => array(
        'title' => 'Parent/Guardian First Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 13,
      ),
      'Mailing_Address' => array(
        'title' => 'Mailing Address',
      ),
      'City' => array(
        'title' => 'City',
      ),
      'Zip_Code' => array(
        'title' => 'Zip Code',
      ),
      'State' => array(
        'title' => 'State',
      ),
      'Phone_Number' => array(
        'title' => 'Phone Number',
      ),
      'Email' => array(
        'title' => 'Email',
      ),
      'Birth_Date' => array(
        'title' => 'Birth Date',
      ),
      'Parent/Guardian_Last_Name' => array(
        'title' => 'Parent/Guardian Last Name',
      ),
      'Parent/Guardian_Last_Name_1' => array(
        'title' => 'Parent/Guardian Last Name',
        'same_alias' => TRUE,
        'alias' => 1,
        'cid' => 30,
      ),
      'Major_Course_of_Study' => array(
        'title' => 'Major Course of Study',
        'columnName' => 'major_alias.label',
      ),
      'Would_you_like_to_add_another_Parent/Guardian?' => array(
        'title' => 'Would you like to add another Parent/Guardian?',
      ),
      'Country' => array(
        'title' => 'Country',
        'columnName' => 'c.name',
      ),
    );

    CRM_Nrm_BAO_Nrm::reportSelectClause($this, $columns, TRUE, TRUE, 551);
  }

  function from() {
    $custom = array(
      170 => 'entry',
      228 => 'major',
    );
    CRM_Nrm_BAO_Nrm::reportFromClause($this->_from, TRUE, array(), $custom);
  }

  function where() {
    CRM_Nrm_BAO_Nrm::reportWhereClause($this->_where, 702);
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
