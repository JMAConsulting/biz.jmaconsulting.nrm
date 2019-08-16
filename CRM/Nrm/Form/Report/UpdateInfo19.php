<?php

class CRM_Nrm_Form_Report_UpdateInfo19 extends CRM_Report_Form {

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE; 

  function __construct() {
    $this->_drupalDatabase = 'chowan2019_dru';
    
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
    $this->assign('reportTitle', ts('Update Information Report for 2019'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'Chowan_ID' => array(
        'title' => 'Chowan ID',
        'ignore_group_concat' => TRUE,
        'columnName' => 'GROUP_CONCAT(contact_civireport.external_identifier)',
      ),
      'Submitted_Date' => array(
        'title' => 'Submitted Date',
        'ignore_group_concat' => TRUE,
        'columnName' => 'DATE(FROM_UNIXTIME(ws.completed))',
      ),
      'First_Name_1' => array(
        'title' => 'First Name',
        'cid' => 3,
        'same_alias' => TRUE,
        'alias' => 1,
      ),
      'Middle_Name' => array(
        'title' => 'Middle Name',
      ),
      'Last_Name_1' => array(
        'title' => 'Last Name',
        'cid' => 5,
        'same_alias' => TRUE,
        'alias' => 1,
      ),
      'Name_Suffix' => array(
        'title' => 'Name Suffix',
        'columnName' => 'suffixes_alias.label',
      ),
      'Nickname' => array(
        'title' => 'Nickname',
      ),
      'Gender' => array(
        'title' => 'Gender',
        'columnName' => 'g.label',
      ),
      'Birth_Date' => array(
        'title' => 'Birth Date',
      ),
      'Email_1' => array(
        'title' => 'Email',
        'cid' => 13,
        'same_alias' => TRUE,
        'alias' => 1,
      ),
      'Street_Address' => array(
        'title' => 'Street Address',
      ),
      'Street_Address_Line_2' => array(
        'title' => 'Street Address Line 2',
      ),
      'City' => array(
        'title' => 'City'
      ),
      'State/Province' => array(
        'title' => 'State/Province',
      ),
      'Postal_Code' => array(
        'title' => 'Postal Code',
      ),
      'Postal_Code_Suffix' => array(
        'title' => 'Postal Code Suffix',
      ),
      'Phone_Number' => array(
        'title' => 'Phone Number',
      ),
      'Phone_Type' => array(
        'title' => 'Phone Type',
        'columnName' => 'pt1.label',
      ),
      'First_Name_2' => array(
        'title' => 'First Name',
        'cid' => 27,
        'same_alias' => TRUE,
        'alias' => 2,
      ),
      'Phone_2' => array(
        'title' => 'Phone',
        'cid' => 28,
        'same_alias' => TRUE,
        'alias' => 2,
      ),
      'Email_2' => array(
        'title' => 'Email',
        'cid' => 29,
        'same_alias' => TRUE,
        'alias' => 2,
      ),
      'Last_Name_2' => array(
        'title' => 'Last Name',
        'cid' => 36,
        'same_alias' => TRUE,
        'alias' => 2,
      ),
      'Address_2' => array(
        'title' => 'Address',
        'cid' => 37,
        'same_alias' => TRUE,
        'alias' => 2,
      ),
      'City_2' => array(
        'title' => 'City',
        'cid' => 38,
        'same_alias' => TRUE,
        'alias' => 2,
      ),
      'Zip_2' => array(
        'title' => 'Zip',
        'cid' => 41,
        'same_alias' => TRUE,
        'alias' => 2,
      ),
      'HS_Grad_Year' => array(
        'title' => 'HS Grad Year',
        'cid' => 43,
      ),
    );
    CRM_Nrm_BAO_Nrm::reportSelectClause($this, $columns);
  }

  function from() {
    $custom = array(
      7 => 'suffixes',
    );
    CRM_Nrm_BAO_Nrm::reportFromClause($this->_from, TRUE, array(), $custom);
  }

  function where() {
    CRM_Nrm_BAO_Nrm::reportWhereClause($this->_where, 552);
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
