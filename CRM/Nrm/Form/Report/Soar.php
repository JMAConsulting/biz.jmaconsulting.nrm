<?php

class CRM_Nrm_Form_Report_Soar extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('SOAR Report'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'First_Name' => array(
        'title' => 'First Name',
      ),
      'Middle_Name' => array(
        'title' => 'Middle Name',
      ),
      'Last_Name' => array(
        'title' => 'Last Name',
      ),
      'Nickname' => array(
        'title' => 'Nickname',
      ),
      'Email' => array(
        'title' => 'Email',
      ),
      'Phone_Number' => array(
        'title' => 'Phone Number',
      ),
      'Street_Address' => array(
        'title' => 'Street Address',
      ),
      'Address_Line_2' => array(
        'title' => 'Street Address Line 2',
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
    );

    CRM_Nrm_BAO_Nrm::reportSelectClause($this, $columns);
  }

  function from() {
    CRM_Nrm_BAO_Nrm::reportFromClause($this->_from);
  }

  function where() {
    CRM_Nrm_BAO_Nrm::reportWhereClause($this->_where, 248, 14);
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
