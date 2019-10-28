<?php

require_once 'nrm_constants.php';

class CRM_Nrm_Form_Report_ManagementSummary20 extends CRM_Report_Form {

  protected $_summary = NULL;

  protected $_customGroupGroupBy = FALSE;

  function __construct() {
    $this->_drupalDatabase = 'upikebears2020_dru';

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
          'date' => array(
            'title' => ts('Date'),
            'operatorType' => CRM_Report_Form::OP_DATE,
          ),
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
    $this->assign('reportTitle', ts('Microsite Summary Report for 2020'));
    parent::preProcess();
  }

  function select() {
    $select = $this->_columnHeaders = array();
    $microsite = "upikebears2020.com";
    $micrositeold = "chowan2019.com";
    // Process Date
    $relative = CRM_Utils_Array::value("date_relative", $this->_params);
    $from = CRM_Utils_Array::value("date_from", $this->_params);
    $to = CRM_Utils_Array::value("date_to", $this->_params);
    if (empty($relative) && empty($from) && empty($to)) {
      // Set date to yesterday if no filter selected.
      $from = $to = date('Y-m-d', strtotime("-1 days"));
    }
    else {
      list($from, $to) = CRM_Report_Form::getFromTo($relative, $from, $to);
      $from = date('Y-m-d', strtotime($from));
      $to = date('Y-m-d', strtotime($to));
    }
    $dateName = "For " . date('l, m/d/Y', strtotime($from));
    if ($from != $to) {
      $dateName .= " to " . date('l, m/d/Y', strtotime($to));
    }

    self::getDefaultWebforms();
    $urlWhere = self::createURLCondition();
    $appWhere = self::getWhereCondition('webforms_applications', 'w.');
    $engageWhere = self::getWhereCondition('webforms_engagement', 'w.');
    $urlVisitSubWhere = self::getWhereCondition('webforms_visits');

    $this->_columnHeaders["description"]['title'] = "Daily Activity Statistics";
    $this->_columnHeaders["perday_visitor_count"]['title'] = " ";

    $visitCountDaily = 0; //$this->getVisitCount('yesterday', NULL, $from, $to);
    $visitCountUnique = 0; //$this->getVisitCount('unique_yesterday', NULL, $from, $to);
    $visitCountCumulative = 0; //$this->getVisitCount('cumulative', NULL, $from, $to);
    $applicationCountDaily = 0; //$this->getVisitCount('yesterday', $appWhere, $from, $to);
    $applicationCountCumulative = 0; //$this->getVisitCount('cumulative', $appWhere, $from, $to);
    $surveyCountCumulative = self::getSurveyCount($from, $to, FALSE);
    $surveyCountDaily = self::getSurveyCount($from, $to, TRUE);
    /* $sql = CRM_Core_DAO::executeQuery("SELECT DISTINCT(purl) as purl_perday_visitor
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE DATE(FROM_UNIXTIME(timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
       AND purl <> '{$microsite}'");
    $visitors = array();
    while ($sql->fetch()) {
      $visitors[] = $sql->purl_perday_visitor;
    }
    CRM_Core_Error::debug('af', $visitors);
    exit; */
    // Create Temporary table for visits. This will be used to calculate daily as well as cumulative visits.
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS civicrm_micro_visit");
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS civicrm_micro_log");
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS civicrm_survey_log");
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS civicrm_webform_visit");
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS civicrm_download_log");
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS civicrm_visitor_log");
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS civicrm_survey_count");
    $tempSql = "
      CREATE TEMPORARY TABLE civicrm_micro_visit AS SELECT p.purl_145 AS purl, DATE(FROM_UNIXTIME(timestamp)) AS timestamp
      FROM {$this->_drupalDatabase}.watchdog_nrm w
      INNER JOIN civicrm_value_nrmpurls_5 p ON p.purl_145 = w.purl_clean
      WHERE w.purl LIKE '%{$microsite}' AND p.reporting_502 = 1
      AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
    ";
    CRM_Core_DAO::executeQuery($tempSql);

    // Create Visit log temporary table.
    $visitSql = "CREATE TEMPORARY TABLE civicrm_micro_log AS SELECT p.purl_145 AS purl, p.entity_id as contact_id, DATE(FROM_UNIXTIME(timestamp)) AS timestamp
      FROM {$this->_drupalDatabase}.nrm_visit_log n
      INNER JOIN civicrm_value_nrmpurls_5 p ON p.purl_145 = n.purl_clean
      WHERE DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
      AND p.reporting_502 = 1";
    CRM_Core_DAO::executeQuery($visitSql);

    // Create extra survey table.
    $surveySql = "CREATE TEMPORARY TABLE civicrm_survey_log AS SELECT p.entity_id as contact_id, DATE(FROM_UNIXTIME(w.completed)) AS timestamp
       FROM {$this->_drupalDatabase}.webform_submissions w
       INNER JOIN {$this->_drupalDatabase}.watchdog_nrm n ON n.hostname = w.remote_addr
       INNER JOIN civicrm_value_nrmpurls_5 p ON p.purl_145 = n.purl_clean
       WHERE w.nid = 564 AND p.reporting_502 = 1
       AND DATE(FROM_UNIXTIME(w.completed)) <= '{$to}'
       GROUP BY n.hostname";
    CRM_Core_DAO::executeQuery($surveySql);

    // Create webform submission table.
    $webformSql = "CREATE TEMPORARY TABLE civicrm_webform_visit AS SELECT w.data as contact_id, ws.completed as timestamp, w.nid as nid
       FROM {$this->_drupalDatabase}.webform_submitted_data w
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid
       INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = w.nid AND w.sid = ws.sid
       WHERE w.data IS NOT NULL and w.data <> ''
       AND DATE(FROM_UNIXTIME(ws.completed)) <= '{$to}'
       GROUP BY w.sid";
    CRM_Core_DAO::executeQuery($webformSql);

    // Create downloads table.
    $downloadSql = "CREATE TEMPORARY TABLE civicrm_download_log AS SELECT p.entity_id as contact_id, DATE(FROM_UNIXTIME(timestamp)) AS timestamp
       FROM {$this->_drupalDatabase}.watchdog_nrm wn
       LEFT JOIN civicrm_value_nrmpurls_5 p
       ON wn.purl_clean = p.purl_145
       WHERE p.reporting_502 = 1 AND wn.location LIKE '%files/%' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'";
    CRM_Core_DAO::executeQuery($downloadSql);

    // Number of visitors in a day - TODO.
    $visitorSql = "CREATE TEMPORARY TABLE civicrm_visitor_log AS SELECT ue.contact_id, ue.timestamp FROM
       (SELECT e.contact_id, e.timestamp FROM
       (SELECT contact_id, timestamp FROM civicrm_webform_visit w
       WHERE {$engageWhere}
       UNION
       SELECT contact_id, timestamp FROM civicrm_micro_log
       UNION
       SELECT contact_id, timestamp FROM civicrm_survey_log
       UNION
       SELECT contact_id, timestamp FROM civicrm_download_log
       ) as e
       INNER JOIN civicrm_value_nrmpurls_5 p ON p.entity_id = e.contact_id
       WHERE p.reporting_502 = 1 AND p.purl_145 IN (SELECT purl_clean AS visit FROM {$this->_drupalDatabase}.watchdog_nrm
	     WHERE DATE(FROM_UNIXTIME(timestamp)) <= '{$to}' AND purl_clean <> '' AND purl_clean IS NOT NULL AND purl LIKE '%{$microsite}')
       GROUP BY e.contact_id
       ) as ue";
    CRM_Core_DAO::executeQuery($visitorSql);

    $this->_select = "
       SELECT '{$dateName}' as description, '' as perday_visitor_count
       UNION
       SELECT 'Total unique visitors for the day' as description, (a.purl_perday_visitor + {$visitCountDaily}) as perday_visitor_count FROM
       (SELECT COUNT(DISTINCT(purl)) as purl_perday_visitor
        FROM civicrm_micro_visit
        WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'
       ) as a
       UNION
       SELECT 'Total unique new visitors for the day' as description, (c.purl_perday_visitor + {$visitCountUnique}) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(purl)) as purl_perday_visitor
       FROM civicrm_micro_visit
       WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'
       AND purl NOT IN (SELECT DISTINCT(purl)
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE DATE(FROM_UNIXTIME(timestamp)) < '{$to}')
       ) as c
       UNION
       SELECT 'Cumulative unique visitors to date' as description, (e.purl_perday_visitor + {$visitCountCumulative}) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(purl)) as purl_perday_visitor
       FROM civicrm_micro_visit
       ) as e
       UNION
       SELECT 'Application page visits - yesterday' as description, COUNT(DISTINCT(g.purl)) as perday_visitor_count FROM
       (
         SELECT purl FROM civicrm_micro_log
         WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'
         AND location LIKE '%apply.upike.edu%'
       ) as g
       UNION
       SELECT 'Cumulative application page visits to date' as description, COUNT(DISTINCT(j.purl)) as perday_visitor_count FROM
       (
        SELECT purl FROM civicrm_micro_log
         WHERE location LIKE '%apply.upike.edu%'
       ) as j
       UNION
       SELECT 'Total Schedule a Visit page visits - yesterday' as description, COUNT(DISTINCT(m.purl)) as perday_visitor_count FROM
       (
         SELECT purl FROM civicrm_micro_log
         WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'
         AND location LIKE '%upike-uga.edu%'
       ) as m
       UNION
       SELECT 'Cumulative Schedule a Visit page visits to date' as description, COUNT(DISTINCT(n.purl)) as perday_visitor_count FROM
       (
         SELECT purl FROM civicrm_micro_log
         WHERE location LIKE '%upike-uga.edu%'
       ) as n
       UNION
       SELECT 'Unique visitors engaging for the day' as description, num.ecount + {$surveyCountDaily} as perday_visitor_count FROM
       (SELECT COUNT(*) as ecount FROM
       (SELECT contact_id FROM
       (SELECT contact_id
       FROM civicrm_webform_visit w 
       WHERE {$engageWhere}
       AND w.timestamp >= '{$from}' AND w.timestamp <= '{$to}'
       UNION
       SELECT p.entity_id as contact_id FROM civicrm_micro_log
            WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'
       UNION
        SELECT contact_id FROM civicrm_survey_log
            WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'
       UNION
        SELECT contact_id FROM civicrm_download_log
            WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'
       ) as e
       INNER JOIN civicrm_value_nrmpurls_5 p ON p.entity_id = e.contact_id
	     WHERE p.reporting_502 = 1 AND p.purl_145 IN (SELECT purl_clean AS visit FROM {$this->_drupalDatabase}.watchdog_nrm
	     WHERE DATE(FROM_UNIXTIME(timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}' AND purl_clean <> '' AND purl_clean IS NOT NULL
	     AND purl LIKE '%{$microsite}')
       GROUP BY contact_id
       ) as ue
       ) AS num
       UNION
       SELECT 'Daily engagement rate' as description,
       IF(denom.visit IS NULL OR denom.visit = 0, '0%', CONCAT(ROUND((num.ecount + {$surveyCountDaily}) * 100/denom.visit, 2),'%')) as perday_visitor_count
       FROM
       (SELECT (COUNT(DISTINCT(purl)) + {$visitCountDaily}) AS visit
       FROM civicrm_micro_visit
       WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'
       ) AS denom
       JOIN
       (SELECT COUNT(*) as ecount FROM
       (SELECT contact_id FROM
       (SELECT contact_id FROM civicrm_webform_visit w
       WHERE {$engageWhere}
       AND w.timestamp >= '{$from}' AND w.timestamp <= '{$to}'
       UNION
       SELECT contact_id FROM civicrm_micro_log
            WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'
       UNION
       SELECT contact_id FROM civicrm_survey_log
            WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'
       UNION
       SELECT contact_id FROM civicrm_download_log
            WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'
       ) as e
       INNER JOIN civicrm_value_nrmpurls_5 p ON p.entity_id = e.contact_id
	     WHERE p.reporting_502 = 1 AND p.purl_145 IN (SELECT purl_clean AS visit FROM {$this->_drupalDatabase}.watchdog_nrm
	     WHERE DATE(FROM_UNIXTIME(timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}' AND purl_clean <> '' AND purl_clean IS NOT NULL
	     AND purl LIKE '%{$microsite}')
       GROUP BY contact_id
       ) as ue
       ) AS num
       UNION
       SELECT 'Cumulative unique visitors that have engaged' as description, num.ecount + {$surveyCountCumulative} as perday_visitor_count FROM
       (SELECT COUNT(*) as ecount FROM
       (SELECT contact_id FROM civicrm_visitor_sql
       ) as ue
       ) AS num
       UNION
       SELECT 'Cumulative engagement rate' as description, IF(denom.visit IS NULL OR denom.visit = 0, '0%', CONCAT(ROUND((num.ecount + {$surveyCountCumulative}) * 100/denom.visit, 2),'%')) as perday_visitor_count FROM
       (SELECT (COUNT(DISTINCT(purl)) + {$visitCountCumulative}) AS visit FROM {$this->_drupalDatabase}.watchdog_nrm
       WHERE purl_clean <> '' AND purl_clean IS NOT NULL AND purl LIKE '%{$microsite}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
       ) AS denom
       JOIN
       (SELECT COUNT(*) as ecount FROM
       (SELECT contact_id FROM civicrm_visitor_sql
       ) as ue
       ) AS num
       UNION
       SELECT 'Cumulative Unsubscribes' as description, COUNT(num.contact_id) as perday_visitor_count FROM
       ( SELECT 1 as contact_id FROM
       {$this->_drupalDatabase}.webform_submitted_data wsd WHERE wsd.cid = 2 AND wsd.sid IN
       (SELECT sid FROM {$this->_drupalDatabase}.webform_submitted_data WHERE nid = 703 AND cid = 6 AND data = 2020)
       GROUP BY wsd.data) as num";
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
    // Build watchdog table
    $wdNrm = "CREATE TABLE IF NOT EXISTS {$this->_drupalDatabase}.watchdog_nrm (
      `wid` int(11) COMMENT 'Unique watchdog event ID.',
      `location` text NOT NULL COMMENT 'URL of the origin of the event.',
      `timestamp` int(11) NOT NULL DEFAULT '0' COMMENT 'Unix timestamp of when event occurred.',
      `purl` varchar(255) NOT NULL COMMENT 'NRM PURL.',
      KEY `wid` (`wid`),
      KEY `purl` (`purl`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table that contains purls of all system events.'";
    CRM_Core_DAO::executeQuery($wdNrm);

    //CRM_Nrm_BAO_Nrm::filterIP();

    //CRM_Nrm_BAO_Nrm::updateWatchdog_nrm();

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

  function getSurveyCount($from, $to, $isDaily) {
    if ($isDaily) {
      return CRM_Core_DAO::singleValueQuery("SELECT COUNT(sid) FROM civicrm_survey_count WHERE timestamp >= '{$from}' AND timestamp <= '{$to}'");
    }
    else {
      $dateClause = "DATE(FROM_UNIXTIME(w.completed)) <= '{$to}'";
    }
    CRM_Core_DAO::singleValueQuery("CREATE TEMPORARY TABLE civicrm_survey_count AS 
     SELECT DISTINCT(w.sid) as sid, w.completed as timestamp FROM {$this->_drupalDatabase}.webform_submissions w
     WHERE w.nid = 564 AND {$dateClause} AND w.sid NOT IN (SELECT w.sid
     FROM {$this->_drupalDatabase}.webform_submissions w
     INNER JOIN {$this->_drupalDatabase}.watchdog_nrm n ON n.hostname = w.remote_addr
     INNER JOIN civicrm_value_nrmpurls_5 p ON p.purl_145 = n.purl_clean
     WHERE w.nid = 564
     AND {$dateClause}
     GROUP BY n.hostname)");
    return CRM_Core_DAO::singleValueQuery("SELECT COUNT(sid) FROM civicrm_survey_count");
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
    $statement = implode("%' OR location LIKE '%", $diff);
    $sql = " AND (location LIKE '%{$statement}%')";
    return $sql;
  }

  function getVisitCount($dateWhere, $appWhere = NULL, $from, $to) {
    $subQuery = NULL;
    switch ($dateWhere) {
      case 'yesterday':
        $date = " DATE(FROM_UNIXTIME(ws.completed)) >= '{$from}' AND DATE(FROM_UNIXTIME(ws.completed)) <= '{$to}' ";
        break;
      case 'unique_yesterday':
        $date = " DATE(FROM_UNIXTIME(ws.completed)) >= '{$from}' AND DATE(FROM_UNIXTIME(ws.completed)) <= '{$to}' ";
        $subQuery = " AND w.data NOT IN (SELECT DISTINCT(wsub.data) from {$this->_drupalDatabase}.webform_submitted_data wsub
          INNER JOIN {$this->_drupalDatabase}.webform_component csub ON csub.cid = wsub.cid AND csub.name = 'Contact ID' AND wsub.nid = csub.nid
          INNER JOIN {$this->_drupalDatabase}.webform_submissions wssub ON wssub.nid = wsub.nid AND wsub.sid = wssub.sid
          WHERE wsub.data IS NOT NULL and wsub.data <> '' AND DATE(FROM_UNIXTIME(wssub.completed)) < '{$from}'
          GROUP BY wsub.sid) ";
        break;
      case 'cumulative':
        $date = " DATE(FROM_UNIXTIME(ws.completed)) <= '{$from}' ";
        break;

    default:
        break;
    }
    // Number of non purl visits
    $sql = "SELECT COUNT(*) as visitcount FROM
       (SELECT contact_id FROM
       (SELECT w.data as contact_id from {$this->_drupalDatabase}.webform_submitted_data w
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid
       INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = w.nid AND w.sid = ws.sid
       WHERE (1)
       AND w.data IS NOT NULL and w.data <> '' AND {$date}
       {$subQuery}
       {$appWhere}
       GROUP BY w.sid
       ) as e
       LEFT JOIN civicrm_value_nrmpurls_5 cp ON cp.entity_id = contact_id
       WHERE cp.entity_id IS NULL
       GROUP BY contact_id
       ) as ue";
    return CRM_Core_DAO::singleValueQuery($sql);
  }

  function alterDisplay(&$rows) {
  }

}
