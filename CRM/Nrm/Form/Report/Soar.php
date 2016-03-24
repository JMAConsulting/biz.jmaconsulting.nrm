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
      'Preferred_Name' => array(
        'title' => 'Preferred Name',
      ),
      'Middle_Name' => array(
        'title' => 'Middle Name',
      ),
      'Last_Name' => array(
        'title' => 'Last Name',
      ),
      'Email' => array(
        'title' => 'Email',
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
      'Street_Address' => array(
        'title' => 'Permanent Address',
      ),
      'Address_Line_2' => array(
        'title' => 'Address Line 2',
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
      'Gender' => array(
        'title' => 'Gender',
        'columnName' => 'g.label',
      ),
      'Date_of_Birth' => array(
        'title' => 'Date of Birth',
      ),
      'Academics' => array(
        'title' => 'Academics',
        'columnName' => 'academics_alias.label',
      ),
      'Athletics' => array(
        'title' => 'Athletics',
        'columnName' => 'athletics_alias.label',
      ),
      'Extra-Curricular' => array(
        'title' => 'Extra-Curricular',
        'columnName' => 'extra_alias.label',
      ),
    );

    CRM_Nrm_BAO_Nrm::reportSelectClause($this, $columns, TRUE);
  }

  function from() {
    $custom = array(
      171 => 'academics',
      159 => 'athletics',
      158 => 'extra',
    );
    CRM_Nrm_BAO_Nrm::reportFromClause($this->_from, TRUE, array(), $custom);
  }

  function where() {
    CRM_Nrm_BAO_Nrm::reportWhereClause($this->_where, 278, 7);
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
