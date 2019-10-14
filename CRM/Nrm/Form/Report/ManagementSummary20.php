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
    /* $sql = CRM_Core_DAO::executeQuery("SELECT DISTINCT(purl) as purl_perday_visitor
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE DATE(FROM_UNIXTIME(timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
       AND purl <> '{$microsite}'");
    $visitors = array();
    while ($sql->fetch()) {
      $visitors[] = $sql->purl_perday_visitor;
    }
    CRM_Core_Error::debug('af', $visitors);
    exit; */
    $this->_select = "
       SELECT '{$dateName}' as description, '' as perday_visitor_count
       UNION
       SELECT 'Total unique visitors for the day' as description, (a.purl_perday_visitor + {$visitCountDaily}) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(purl)) as purl_perday_visitor
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE DATE(FROM_UNIXTIME(timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
       AND purl <> '{$microsite}' AND purl LIKE '%{$microsite}'
       ) as a
       UNION
       SELECT 'Total unique new visitors for the day' as description, (c.purl_perday_visitor + {$visitCountUnique}) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(purl)) as purl_perday_visitor
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE DATE(FROM_UNIXTIME(timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
       AND purl <> '{$microsite}' AND purl LIKE '%{$microsite}'
       AND (purl) NOT IN (SELECT DISTINCT(purl)
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE DATE(FROM_UNIXTIME(timestamp)) < '{$to}')
       ) as c
       UNION
       SELECT 'Cumulative unique visitors to date' as description, (e.purl_perday_visitor + {$visitCountCumulative}) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(purl)) as purl_perday_visitor
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE purl <> '{$microsite}' AND purl LIKE '%{$microsite}'
       AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
       ) as e
       UNION
       SELECT 'Application page visits - yesterday' as description, (g.purl_perday_start + {$applicationCountDaily}) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(location)) as purl_perday_start
       FROM
       ( SELECT
       SUBSTR(sub.location,
       INSTR(sub.location, '://') + 3,
       IF(INSTR(sub.location,'?')>0,
        INSTR(sub.location,'?') - INSTR(sub.location, '://') - 3,
        LENGTH(sub.location)
        )) as location, timestamp
       FROM {$this->_drupalDatabase}.watchdog_nrm sub
       WHERE DATE(FROM_UNIXTIME(sub.timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(sub.timestamp)) <= '{$to}'
       GROUP BY sub.location ) as loc
       WHERE location LIKE '%.{$microsite}%' {$urlWhere}
       AND location NOT IN (SELECT
       SUBSTR(wn.location,
       INSTR(wn.location, '://') + 3,
       IF(INSTR(wn.location,'?')>0,
        INSTR(wn.location,'?') - INSTR(wn.location, '://') - 3,
        LENGTH(wn.location)
        )) as location
       FROM {$this->_drupalDatabase}.watchdog_nrm wn
       WHERE DATE(FROM_UNIXTIME(wn.timestamp)) < '{$to}'
       )
       ) as g
       UNION
       SELECT 'Applications submitted - yesterday' as description, i.perday_completed as perday_visitor_count FROM
       ( SELECT COUNT(w.nid) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions w WHERE DATE(FROM_UNIXTIME(w.completed)) >= '{$from}' AND DATE(FROM_UNIXTIME(w.completed)) <= '{$to}' {$appWhere}
       ) as i
       UNION
       SELECT 'Cumulative application page visits to date' as description, (j.purl_perday_start + {$applicationCountCumulative}) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(location)) as purl_perday_start
       FROM
       ( SELECT
       SUBSTR(sub.location,
       INSTR(sub.location, '://') + 3,
       IF(INSTR(sub.location,'?')>0,
        INSTR(sub.location,'?') - INSTR(sub.location, '://') - 3,
        LENGTH(sub.location)
        )) as location, timestamp
       FROM {$this->_drupalDatabase}.watchdog_nrm sub
       WHERE DATE(FROM_UNIXTIME(sub.timestamp)) <= '{$from}'
       GROUP BY sub.location ) as loc
       WHERE location LIKE '%.{$microsite}%' {$urlWhere}
       ) as j
       UNION
       SELECT 'Cumulative applications submitted to date' as description, l.perday_completed as perday_visitor_count FROM
       ( SELECT COUNT(w.nid) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions w WHERE (1) {$appWhere} AND DATE(FROM_UNIXTIME(w.completed)) <= '{$to}'
       ) as l
       UNION
       SELECT 'Total visit registrations - yesterday' as description, m.perday_completed as perday_visitor_count FROM
       ( SELECT COUNT(nid) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE DATE(FROM_UNIXTIME(completed)) >= '{$from}' AND DATE(FROM_UNIXTIME(completed)) <= '{$to}' {$urlVisitSubWhere}
       ) as m
       UNION
       SELECT 'Cumulative visit registrations submitted to date' as description, n.perday_completed as perday_visitor_count FROM
       ( SELECT COUNT(nid) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE (1) {$urlVisitSubWhere} AND DATE(FROM_UNIXTIME(completed)) <= '{$to}'
       ) as n
       UNION
       SELECT 'Unique visitors engaging for the day' as description, num.ecount as perday_visitor_count FROM
       (SELECT COUNT(*) as ecount FROM
       (SELECT contact_id FROM
       (SELECT w.data as contact_id
       FROM {$this->_drupalDatabase}.webform_submitted_data w
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid
       INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = w.nid AND w.sid = ws.sid
       WHERE (1) {$engageWhere}
       AND data IS NOT NULL and data <> ''
       AND DATE(FROM_UNIXTIME(ws.completed)) >= '{$from}' AND DATE(FROM_UNIXTIME(ws.completed)) <= '{$to}'
       GROUP BY w.sid
       UNION
       SELECT p.entity_id as download
       FROM {$this->_drupalDatabase}.watchdog_nrm wn LEFT JOIN civicrm_value_nrmpurls_5 p
       ON REPLACE(wn.purl, '.{$microsite}', '') COLLATE utf8_unicode_ci = p.purl_145
       WHERE wn.location LIKE '%files/%' AND DATE(FROM_UNIXTIME(wn.timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(wn.timestamp)) <= '{$to}'
       ) as e
       INNER JOIN civicrm_value_nrmpurls_5 p ON p.entity_id = e.contact_id
	     WHERE p.purl_145 IN (SELECT REPLACE(purl,'.{$microsite}','') COLLATE utf8_unicode_ci AS visit FROM {$this->_drupalDatabase}.watchdog_nrm
	     WHERE DATE(FROM_UNIXTIME(timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}' AND purl <> '{$microsite}' AND purl LIKE '%{$microsite}')
       GROUP BY contact_id
       ) as ue
       ) AS num
       UNION
       SELECT 'Daily engagement rate' as description, IF(denom.visit IS NULL OR denom.visit = 0, '0%', CONCAT(ROUND(num.ecount * 100/denom.visit, 2),'%')) as perday_visitor_count FROM
       (SELECT (COUNT(DISTINCT(purl)) + {$visitCountDaily}) AS visit
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE DATE(FROM_UNIXTIME(timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
       AND purl <> '{$microsite}' AND purl LIKE '%{$microsite}'
       ) AS denom
       JOIN
       (SELECT COUNT(*) as ecount FROM
       (SELECT contact_id FROM
       (SELECT w.data as contact_id from {$this->_drupalDatabase}.webform_submitted_data w
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid
       INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = w.nid AND w.sid = ws.sid
       WHERE (1) {$engageWhere}
       AND w.data IS NOT NULL and w.data <> ''
       AND DATE(FROM_UNIXTIME(ws.completed)) >= '{$from}' AND DATE(FROM_UNIXTIME(ws.completed)) <= '{$to}'
       GROUP BY w.sid
       UNION
       SELECT p.entity_id as download
       FROM {$this->_drupalDatabase}.watchdog_nrm wn LEFT JOIN civicrm_value_nrmpurls_5 p
       ON REPLACE(wn.purl, '.{$microsite}', '') COLLATE utf8_unicode_ci = p.purl_145
       WHERE location LIKE '%files/%' AND DATE(FROM_UNIXTIME(timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
       ) as e
       INNER JOIN civicrm_value_nrmpurls_5 p ON p.entity_id = e.contact_id
	     WHERE p.purl_145 IN (SELECT REPLACE(purl,'.{$microsite}','') COLLATE utf8_unicode_ci AS visit FROM {$this->_drupalDatabase}.watchdog_nrm
	     WHERE DATE(FROM_UNIXTIME(timestamp)) >= '{$from}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}' AND purl <> '{$microsite}' AND purl LIKE '%{$microsite}')
       GROUP BY contact_id
       ) as ue
       ) AS num
       UNION
       SELECT 'Cumulative unique visitors that have engaged' as description, num.ecount as perday_visitor_count FROM
       (SELECT COUNT(*) as ecount FROM
       (SELECT contact_id FROM
       (SELECT w.data as contact_id from {$this->_drupalDatabase}.webform_submitted_data w
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid
       INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = w.nid AND w.sid = ws.sid
       WHERE (1) {$engageWhere}
       AND w.data IS NOT NULL and w.data <> '' AND DATE(FROM_UNIXTIME(ws.completed)) <= '{$to}'
       GROUP BY w.sid
       UNION
       SELECT p.entity_id as download
       FROM {$this->_drupalDatabase}.watchdog_nrm wn LEFT JOIN civicrm_value_nrmpurls_5 p
       ON REPLACE(wn.purl, '.{$microsite}', '') COLLATE utf8_unicode_ci = p.purl_145
       WHERE wn.location LIKE '%files/%' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
       ) as e
       INNER JOIN civicrm_value_nrmpurls_5 p ON p.entity_id = e.contact_id
       WHERE p.purl_145 IN (SELECT REPLACE(purl,'.{$microsite}','') COLLATE utf8_unicode_ci AS visit FROM {$this->_drupalDatabase}.watchdog_nrm
	     WHERE DATE(FROM_UNIXTIME(timestamp)) <= '{$to}' AND purl <> '{$microsite}' AND purl LIKE '%{$microsite}')
       GROUP BY contact_id
       ) as ue
       ) AS num
       UNION
       SELECT 'Cumulative engagement rate' as description, IF(denom.visit IS NULL OR denom.visit = 0, '0%', CONCAT(ROUND(num.ecount * 100/denom.visit, 2),'%')) as perday_visitor_count FROM
       (SELECT (COUNT(DISTINCT(purl)) + {$visitCountCumulative}) AS visit FROM {$this->_drupalDatabase}.watchdog_nrm
       WHERE purl <> '{$microsite}'  AND purl LIKE '%{$microsite}' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
       ) AS denom
       JOIN
       (SELECT COUNT(*) as ecount FROM
       (SELECT contact_id FROM
       (SELECT w.data as contact_id FROM
       {$this->_drupalDatabase}.webform_submitted_data w
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid
       INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = w.nid AND w.sid = ws.sid
       WHERE (1) {$engageWhere}
       AND w.data IS NOT NULL and w.data <> '' AND DATE(FROM_UNIXTIME(ws.completed)) <= '{$to}'
       GROUP BY w.sid
       UNION
       SELECT p.entity_id as download
       FROM {$this->_drupalDatabase}.watchdog_nrm wn
       LEFT JOIN civicrm_value_nrmpurls_5 p on REPLACE(wn.purl, '.{$microsite}', '') COLLATE utf8_unicode_ci = p.purl_145
       WHERE location LIKE '%files/%' AND DATE(FROM_UNIXTIME(timestamp)) <= '{$to}'
       ) as e
       INNER JOIN civicrm_value_nrmpurls_5 p ON p.entity_id = e.contact_id
	     WHERE p.purl_145 IN (SELECT REPLACE(purl,'.{$microsite}','') COLLATE utf8_unicode_ci AS visit FROM {$this->_drupalDatabase}.watchdog_nrm
	     WHERE DATE(FROM_UNIXTIME(timestamp)) <= '{$to}' AND purl <> '{$microsite}' AND purl LIKE '%{$microsite}')
       GROUP BY contact_id
       ) as ue
       ) AS num
       UNION
       SELECT 'Cumulative Unsubscribes' as description, COUNT(num.contact_id) as perday_visitor_count FROM
       ( SELECT 1 as contact_id FROM
       {$this->_drupalDatabase}.webform_submitted_data wsd WHERE wsd.cid = 2 AND wsd.sid IN
       (SELECT sid FROM {$this->_drupalDatabase}.webform_submitted_data WHERE nid = 434 AND cid = 17 AND data = 2020)
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

    CRM_Nrm_BAO_Nrm::filterIP();

    CRM_Nrm_BAO_Nrm::updateWatchdog_nrm();

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
