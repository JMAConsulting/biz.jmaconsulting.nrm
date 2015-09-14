<?php

class CRM_Yoteup_Form_Report_ManagementSummary extends CRM_Report_Form {

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE; 

  function __construct() {
    $config = CRM_Core_Config::singleton();
    $dsnArray = DB::parseDSN($config->userFrameworkDSN);
    $this->_drupalDatabase = $dsnArray['database'];

    self::getWebforms();
    
    $this->_columns = array(
      'watchdog' => array(
        'fields' => array(
          'location' => array(
            'title' => ts('Location'),
            'required' => TRUE,
            'default' => TRUE,
            'no_repeat' => TRUE,
          ),
        ),
        'filters' => array(
          'webforms' => array(
            'title' => ts('Webforms'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->webForms,
            'default' => self::getDefaultWebforms(),
          ),
        ),
      ),
    );
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Management Summary Report'));
    parent::preProcess();
  }

  function select() {
    $select = $this->_columnHeaders = array();
    $urlWhere = self::createURLCondition();
    $urlSubWhere = self::createSubURLCondition();

    $this->_columnHeaders["description"]['title'] = " ";
    $this->_columnHeaders["perday_visitor_count"]['title'] = " ";
    $this->_select = "SELECT 'Total unique new visitors for the day' as description, SUM(perday_visitor) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1)))) as perday_visitor  
       FROM {$this->_drupalDatabase}.watchdog WHERE DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW())) as a
       UNION 
       SELECT 'Total unique new visitors for all time' as description, SUM(perday_visitor) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1)))) as perday_visitor  
       FROM {$this->_drupalDatabase}.watchdog) as b
       UNION
       SELECT 'Total starts for individual forms/surveys for the day' as description, SUM(perday_start) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(location)) as perday_start
       FROM {$this->_drupalDatabase}.watchdog WHERE DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW()) {$urlWhere}) as c
       UNION
       SELECT 'Total completed submissions for individual forms/surveys for the day' as description, IF(SUM(perday_completed) IS NULL, 0, SUM(perday_completed)) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(nid)) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE DATE(FROM_UNIXTIME(completed)) = DATE(NOW()) {$urlSubWhere}
       GROUP BY nid, remote_addr) as d
       UNION
       SELECT 'Percent completion rate of individual forms/surveys for the day' as description,
       IF(SUM(perday_completed) IS NULL, '0%', CONCAT_WS(ROUND((SUM(perday_completed)/SUM(DISTINCT(perday_start)))*100, 2), '', '%')) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(nid)) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE DATE(FROM_UNIXTIME(completed)) = DATE(NOW()) {$urlSubWhere}
       GROUP BY nid, remote_addr) as e
       INNER JOIN
       ( SELECT COUNT(DISTINCT(location)) as perday_start
       FROM {$this->_drupalDatabase}.watchdog WHERE DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW()) {$urlWhere}) as f
       UNION
       SELECT 'Number of cumulative form/survey completions for all time' as description, SUM(perday_completed) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(nid)) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE (1) {$urlSubWhere}
       GROUP BY nid, remote_addr) as g
       UNION
       SELECT 'Percent engagement rate for site' as description,
       CONCAT_WS(ROUND((SUM(perday_completed)/SUM(DISTINCT(perday_start)))*100, 2), '', '%') as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(location)) as perday_completed
       FROM {$this->_drupalDatabase}.watchdog WHERE (1) {$urlWhere}) as h
       INNER JOIN
       (SELECT COUNT(DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1)))) as perday_start 
       FROM {$this->_drupalDatabase}.watchdog) as i";
  }

  function from() {
    $this->_from = NULL;

  }

  function where() {
    $clauses = array();
    
    if (!empty($clauses)) {
      $this->_where = "WHERE " . implode(' AND ', $clauses);
    }
  }

  function groupBy() {
    return FALSE;
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
  
  function getWebforms() {
    $this->webForms = array();

    $sql = "SELECT w.nid, n.title
      FROM {$this->_drupalDatabase}.webform w 
      INNER JOIN {$this->_drupalDatabase}.node n ON n.nid = w.nid";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $this->webForms[$dao->nid] = $dao->title;
    }
  }
  
  function getDefaultWebforms() {
    $default = $urls = $this->urls = array();
    
    $sql = "SELECT w.nid, u.alias
      FROM {$this->_drupalDatabase}.webform w
      INNER JOIN {$this->_drupalDatabase}.node n ON n.nid = w.nid
      INNER JOIN {$this->_drupalDatabase}.url_alias u ON u.source = CONCAT_WS('/', 'node', w.nid)
      WHERE w.nid NOT IN (131, 132, 103, 198, 71, 75, 190, 97, 199)";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $default[] = $dao->nid;
      $urls[$dao->nid] = $dao->alias;
    }
    $this->urls = $urls;
    return $default;
  }
  
  function createURLCondition() {
    // First get submitted params from webform
    $webformOP = $this->_params['webforms_op'];
    $webformParams = $this->_params['webforms_value'];
    $urls = $this->urls;
    // Compute the intersection
    if ($webformOP == 'in') {
      $diff = array_flip(array_intersect(array_flip($urls), $webformParams));
    }
    else if ($webformOP == 'notin') {
      $diff = array_flip(array_diff(array_flip($urls), $webformParams));
    }
    if (empty($diff)) {
      return " AND (1)";
    } 
    $statement = implode("' OR location LIKE '%", $diff);
    $sql = " AND (location LIKE '%{$statement}')";
    return $sql;
  }
  
  function createSubURLCondition() {
    // First get submitted params from webform
    $webformOP = $this->_params['webforms_op'];
    $webformParams = $this->_params['webforms_value'];
    // Compute the intersection
    if ($webformOP == 'in') {
      $op = "IN";
    }
    else if ($webformOP == 'notin') {
      $op = "NOT IN";
    }
    $statement = '(' . implode(",", $webformParams) . ')';
    $sql = " AND nid {$op} {$statement}";
    return $sql;
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
