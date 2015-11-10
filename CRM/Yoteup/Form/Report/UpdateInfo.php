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
    $columns =  array(
      'Email' => array(
        'title' => 'Email',
        'columnTitle' => 'Email',
      ),
      'First_Name' => array(
        'title' => 'First Name',
        'columnTitle' => 'First_Name',
      ),
      'Middle_Name' => array(
        'title' => 'Middle Name',
        'columnTitle' => 'Middle_Name',
      ),
      'Last_Name' => array(
        'title' => 'Last Name',
        'columnTitle' => 'Last_Name',
      ),
      'Name_Suffix' => array(
        'title' => 'Name Suffix',
        'columnName' => 'suffix_alias.label',
        'columnTitle' => 'name_suffix',
      ),
      'Nickname' => array(
        'title' => 'Nickname',
        'columnTitle' => 'Nick_name',
      ),
      'Gender' => array(
        'title' => 'Gender',
        'columnName' => 'g.label',
        'columnTitle' => 'Gender',
      ),
      'Birth_Date' => array(
        'columnTitle' => 'Birth_Date',
        'title' => 'Birth Date',
      ),
      'Street_Address' => array(
        'title' => 'Street Address',
        'columnTitle' => 'Street_Address',
      ),
      'Street_Address_Line_2' => array(
        'title' => 'Street Address Line 2',
        'columnTitle' => 'Address_Line_2',
      ),
      'City' => array(
        'columnTitle' => 'City',
        'title' => 'City'
      ),
      'County' => array(
        'title' => 'County',
        'columnTitle' => 'County',
      ),
      'State/Province' => array(
        'title' => 'State/Province',
        'columnTitle' => 'State',
      ),
      'Postal_Code' => array(
        'title' => 'Postal Code',
        'columnTitle' => 'Postal_Code',
      ),
      'Postal_Code_Suffix' => array(
        'title' => 'Postal Code Suffix',
      ),
      'Phone_Number' => array(
        'title' => 'Phone Number',
        'columnTitle' => 'Phone',
      ),
      'Phone_Type' => array(
        'title' => 'Phone Type',
        'columnName' => 'pt1.label',
      ),
    );
    CRM_Yoteup_BAO_Yoteup::reportSelectClause($this, $columns);
  }

  function from() { 
    $custom = array(
      7 => 'suffix',
    );
    CRM_Yoteup_BAO_Yoteup::reportFromClause($this->_from, FALSE, array(), $custom);
  }

  function where() {
    CRM_Yoteup_BAO_Yoteup::reportWhereClause($this->_where, 67);
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
