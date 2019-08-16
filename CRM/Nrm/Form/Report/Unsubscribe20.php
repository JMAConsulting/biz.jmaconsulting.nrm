<?php

class CRM_Nrm_Form_Report_Unsubscribe20 extends CRM_Report_Form {

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
        'filters' => array(
          'grad_year' => array(
            'title' => ts('HS Grad Year'),
            'type' => CRM_Utils_Type::T_INT,
            'operatorType' => CRM_Report_Form::OP_SELECT,
            'options' => [2019 => 2019, 2020 => 2020],
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
    $this->assign('reportTitle', ts('Daily Unsubscribes for 2020'));
    parent::preProcess();
  }

  function select() {
    $columns =  array(
      'Chowan_ID' => array(
        'title' => 'Chowan ID',
        'ignore_group_concat' => TRUE,
        'columnName' => 'GROUP_CONCAT(contact_civireport.external_identifier)',
      ),
      'First_Name' => array(
        'title' => 'First Name',
      ),
      'Last_Name' => array(
        'title' => 'Last Name',
      ),
      'Email' => array(
        'title' => 'Email',
      ),
      'HS_Grad_Year' => array(
        'title' => 'HS Grad Year',
      ),
      'Unsubscribe' => array(
        'title' => 'Unsubscribe',
      ),
    );
    CRM_Nrm_BAO_Nrm::reportSelectClause($this, $columns);
  }

  function from() {
    $custom = array();
    CRM_Nrm_BAO_Nrm::reportFromClause($this->_from, FALSE, array(), $custom);
  }

  function where() {
    CRM_Nrm_BAO_Nrm::reportWhereClause($this->_where, 703);
  }

  function groupBy() {
    $this->_groupBy = "GROUP BY wsd.sid";
  }

  function orderBy() {
    return FALSE;
  }

  function postProcess() {
    $grad = 2020;
    if (!empty($this->_submitValues['grad_year_value'])) {
      $grad = $this->_submitValues['grad_year_value'];
    }
    $this->beginPostProcess();

    $sql = $this->buildQuery(FALSE);
    $sql = "SELECT * FROM (" . $sql . ") as a GROUP BY a.sid HAVING a.HS_Grad_Year={$grad}";

    $rows = array();
    $this->buildRows($sql, $rows);

    $this->formatDisplay($rows);
    $this->doTemplateAssignment($rows);
    $this->endPostProcess($rows);
  }

  function alterDisplay(&$rows) {
    if (!empty($rows)) {
      foreach ($rows as &$row) {
        if (!empty($row['Unsubscribe'])) {
          $row['Unsubscribe'] = "Yes";
        }
        else {
          $row['Unsubscribe'] = "No";
        }
      }
    }
  }

}
