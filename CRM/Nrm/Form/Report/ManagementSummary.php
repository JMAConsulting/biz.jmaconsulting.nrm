<?php

require_once 'nrm_constants.php';

class CRM_Nrm_Form_Report_ManagementSummary extends CRM_Report_Form {

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
            'title' => ts('Daily Activity Statistics'),
            'required' => TRUE,
            'default' => TRUE,
            'no_repeat' => FALSE,
          ),
        ),
        'filters' => array(
          'webforms_visits' => array(
            'title' => ts('Webform(s) to Request or Register for a Visit'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->webForms,
          ),
          'webforms_applications' => array(
            'title' => ts('Webform(s) to Submit an Application'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->webForms,
          ),
          'webforms_engagement' => array(
            'title' => ts('Webform(s) Indicating Engagement'),
            'type' => CRM_Utils_Type::T_STRING,
            'operatorType' => CRM_Report_Form::OP_MULTISELECT,
            'options' => $this->webForms,
          ),
        ),
      ),
    );
    $this->_groupFilter = FALSE;
    $this->_tagFilter = FALSE;
    parent::__construct();
  }

  function preProcess() {
    $this->assign('reportTitle', ts('Microsite Summary Report'));
    parent::preProcess();
  }

  function select() {
    $select = $this->_columnHeaders = array();
    self::getDefaultWebforms();
    $urlWhere = self::createURLCondition();
    $appWhere = self::getWhereCondition('webforms_applications');
    $engageWhere = self::getWhereCondition('webforms_engagement', 'w.');
    $urlVisitSubWhere = self::getWhereCondition('webforms_visits');

    $this->_columnHeaders["description"]['title'] = "Daily Activity Statistics";
    $this->_columnHeaders["perday_visitor_count"]['title'] = " ";
    $this->_select = "
       SELECT CONCAT('For ', DAYNAME(DATE_ADD(CURDATE(),INTERVAL -1 DAY)), ', ', DATE_FORMAT(DATE_ADD(CURDATE(),INTERVAL -1 DAY), '%m/%d/%Y')) as description, '' as perday_visitor_count
       UNION
       SELECT 'Total unique visitors for the day' as description, perday_visitor as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1)))) as perday_visitor  
       FROM {$this->_drupalDatabase}.watchdog WHERE DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW() - INTERVAL 1 DAY)) as a
       UNION
       SELECT 'Total unique new visitors for the day' as description, perday_visitor as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1)))) as perday_visitor  
       FROM {$this->_drupalDatabase}.watchdog WHERE DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW() - INTERVAL 1 DAY)
       AND (SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1)) NOT IN (SELECT DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1)))
       FROM {$this->_drupalDatabase}.watchdog WHERE DATE(FROM_UNIXTIME(timestamp)) < DATE(NOW() - INTERVAL 1 DAY))) as b
       UNION
       SELECT 'Cumulative unique visitors to date' as description, perday_visitor as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1)))) as perday_visitor  
       FROM {$this->_drupalDatabase}.watchdog) as c
       UNION
       SELECT 'Applications started - yesterday' as description, perday_start as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(location)) as perday_start
       FROM {$this->_drupalDatabase}.watchdog WHERE DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW() - INTERVAL 1 DAY) {$urlWhere}) as d
       UNION
       SELECT 'Applications submitted - yesterday' as description, IF(SUM(perday_completed) IS NULL, 0, SUM(perday_completed)) as perday_visitor_count FROM
       ( SELECT COUNT(nid) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE DATE(FROM_UNIXTIME(completed)) = DATE(NOW() - INTERVAL 1 DAY) {$appWhere}) as e
       UNION
       SELECT 'Cumulative applications started to date' as description, SUM(perday_completed) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(location)) as perday_completed
       FROM {$this->_drupalDatabase}.watchdog WHERE (1) {$urlWhere}) as x
       UNION
       SELECT 'Cumulative applications submitted to date' as description, SUM(perday_completed) as perday_visitor_count FROM
       ( SELECT COUNT(nid) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE (1) {$appWhere}) as f
       UNION
       SELECT 'Total visit registrations - yesterday' as description, IF(SUM(perday_completed) IS NULL, 0, SUM(perday_completed)) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(nid)) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE DATE(FROM_UNIXTIME(completed)) = DATE(NOW() - INTERVAL 1 DAY) {$urlVisitSubWhere}
       GROUP BY nid) as g
       UNION
       SELECT 'Cumulative visit registrations submitted to date' as description, SUM(perday_completed) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(nid)) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE (1) {$urlVisitSubWhere}
       GROUP BY nid) as h
       UNION
       SELECT 'Unique visitors engaging for the day' as description, num.ecount as perday_visitor_count FROM
       (SELECT COUNT(*) as ecount FROM 
       (SELECT location FROM 
       (SELECT CONCAT(p.purl_145,'.brevard2016.com') as location from {$this->_drupalDatabase}.webform_submitted_data w 
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid 
       LEFT JOIN ". PURLS ." p on c.cid=p.entity_id
       INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = w.nid      
       WHERE (1) {$engageWhere}
       AND data IS NOT NULL and data <> '' group by w.cid
       AND DATE(FROM_UNIXTIME(ws.completed)) = DATE(NOW() - INTERVAL 1 DAY)
       UNION
       SELECT DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1))) COLLATE utf8_unicode_ci as download 
       FROM {$this->_drupalDatabase}.watchdog WHERE location LIKE '%files/%' AND DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW() - INTERVAL 1 DAY)) as e GROUP BY location) as ue
       ) AS num
       UNION
       SELECT 'Daily engagement rate' as description, IF(denom.visit IS NULL OR denom.visit = 0, '0%', CONCAT(ROUND(num.ecount * 100/denom.visit, 2),'%')) as perday_visitor_count FROM
       (SELECT COUNT(DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1)))) AS visit
       FROM {$this->_drupalDatabase}.watchdog WHERE DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW() - INTERVAL 1 DAY)) AS denom
       JOIN 
       (SELECT COUNT(*) as ecount FROM 
       (SELECT location FROM 
       (SELECT CONCAT(p.purl_145,'.brevard2016.com') as location from {$this->_drupalDatabase}.webform_submitted_data w 
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid 
       LEFT JOIN ". PURLS ." p on c.cid=p.entity_id
       INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = w.nid      
       WHERE (1) {$engageWhere}
       AND data IS NOT NULL and data <> '' group by w.cid
       AND DATE(FROM_UNIXTIME(ws.completed)) = DATE(NOW() - INTERVAL 1 DAY)
       UNION
       SELECT DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1))) COLLATE utf8_unicode_ci as download 
       FROM {$this->_drupalDatabase}.watchdog WHERE location LIKE '%files/%' AND DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW() - INTERVAL 1 DAY)) as e GROUP BY location) as ue
       ) AS num
       UNION
       SELECT 'Cumulative unique visitors that have engaged' as description, num.ecount as perday_visitor_count FROM
       (SELECT COUNT(*) as ecount FROM 
       (SELECT location FROM 
       (SELECT CONCAT(p.purl_145,'.brevard2016.com') as location from {$this->_drupalDatabase}.webform_submitted_data w  
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid 
       LEFT JOIN ". PURLS ." p on c.cid=p.entity_id
       INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = w.nid      
       WHERE (1) {$engageWhere}
       AND data IS NOT NULL and data <> '' group by w.cid
       UNION
       SELECT DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1))) COLLATE utf8_unicode_ci as download 
       FROM {$this->_drupalDatabase}.watchdog WHERE location LIKE '%files/%') as e GROUP BY location) as ue
       ) AS num
       UNION
       SELECT 'Cumulative engagement rate' as description, IF(denom.visit IS NULL OR denom.visit = 0, '0%', CONCAT(ROUND(num.ecount * 100/denom.visit, 2),'%')) as perday_visitor_count FROM
       (SELECT COUNT(DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1)))) AS visit FROM {$this->_drupalDatabase}.watchdog) AS denom
       JOIN 
       (SELECT COUNT(*) as ecount FROM 
       (SELECT location FROM 
       (SELECT CONCAT(p.purl_145,'.brevard2016.com') as location from {$this->_drupalDatabase}.webform_submitted_data w
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid 
       LEFT JOIN ". PURLS ." p on c.cid=p.entity_id       
       WHERE (1) {$engageWhere}
       AND data IS NOT NULL and data <> '' group by w.cid
       UNION
       SELECT DISTINCT((SUBSTRING_INDEX(SUBSTRING_INDEX(location, '://', -1), '/', 1))) COLLATE utf8_unicode_ci as download 
       FROM {$this->_drupalDatabase}.watchdog WHERE location LIKE '%files/%') as e GROUP BY location) as ue
       ) AS num";
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
    $appWhere = self::getWhereCondition('webforms_applications', 'w.');
    $defaults = $urls = $this->urls = array();

    $sql = "SELECT w.nid, u.alias
      FROM {$this->_drupalDatabase}.webform w
      INNER JOIN {$this->_drupalDatabase}.node n ON n.nid = w.nid
      INNER JOIN {$this->_drupalDatabase}.url_alias u ON u.source = CONCAT_WS('/', 'node', w.nid)
      WHERE (1) {$appWhere}";
     $dao = CRM_Core_DAO::executeQuery($sql);
     while ($dao->fetch()) {
       $default[] = $dao->nid;
       $urls[$dao->nid] = $dao->alias;
     }
     $this->urls = $urls;
   }

  function getWhereCondition($fieldName, $alias = '') {
    // First get submitted params from webform
    $webformOP = $this->_params["{$fieldName}_op"];
    $webformParams = $this->_params["{$fieldName}_value"];
    if (empty($webformParams)) {
      return '';
    }
    // Compute the intersection
    if ($webformOP == 'in') {
      $op = "IN";
    }
    else if ($webformOP == 'notin') {
      $op = "NOT IN";
    }
    $statement = '(' . implode(",", $webformParams) . ')';
    $sql = " AND {$alias}nid {$op} {$statement}";
    return $sql;
  }
  
  function createURLCondition() {
    // First get submitted params from webform
    $webformOP = $this->_params['webforms_applications_op'];
    $webformParams = $this->_params['webforms_applications_value'];
    if (empty($webformParams)) {
      return '';
    }
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
