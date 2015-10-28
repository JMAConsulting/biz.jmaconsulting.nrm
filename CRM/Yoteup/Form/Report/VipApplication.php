<?php

class CRM_Yoteup_Form_Report_VipApplication extends CRM_Report_Form {

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
    $this->assign('reportTitle', ts('VIP Application Report'));
    parent::preProcess();
  }

  function select() {
    $this->_select = "SELECT CONCAT('Submitted Time: ', DATE_FORMAT(FROM_UNIXTIME(ws.completed), '%m-%d-%Y %r')) AS sub,
      CONCAT('Chowan ID: ', contact_civireport.external_identifier) AS cho, GROUP_CONCAT(CONCAT( wc.name, ': ', wsd.data), '<br/>') as applications";
    $this->_columnHeaders['applications']['title'] = ts('Applications');
    $this->_columnHeaders['sub']['title'] = ts('Submitted Time');
    $this->_columnHeaders['cho']['title'] = ts('Chowan ID');
  }

  function from() {
    $config = CRM_Core_Config::singleton();
    $dsnArray = DB::parseDSN($config->userFrameworkDSN);
    $drupalDb = $dsnArray['database'];
    $this->_from = "FROM {$drupalDb}.webform_submitted_data wsd 
      LEFT JOIN civicrm_contact contact_civireport  ON wsd.data = contact_civireport.id AND wsd.cid = 2
      LEFT JOIN {$drupalDb}.webform_component wc ON wc.cid = wsd.cid
      LEFT JOIN {$drupalDb}.webform_submissions ws ON ws.sid = wsd.sid";
  }

  function where() {
    CRM_Yoteup_BAO_Yoteup::reportWhereClause($this->_where, 70);
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
    foreach ($rows as $rowNum => &$row) {
      $row = str_replace('<br/>,', '<br/>', $row);
      $row['applications'] = $row['cho'] . '<br/>' . $row['applications'];
      $row['applications'] = $row['sub'] . '<br/>' . $row['applications'];
      unset($row['sub']);
      unset($row['cho']);
    }
  }
}
