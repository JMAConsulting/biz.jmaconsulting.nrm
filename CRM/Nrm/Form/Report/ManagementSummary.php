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

    $visitCountDaily = self::getVisitCount('yesterday');
    $visitCountUnique = self::getVisitCount('unique_yesterday');
    $visitCountCumulative = self::getVisitCount('cumulative');

    $this->_select = "
       SELECT CONCAT('For ', DAYNAME(DATE_ADD(CURDATE(),INTERVAL -1 DAY)), ', ', DATE_FORMAT(DATE_ADD(CURDATE(),INTERVAL -1 DAY), '%m/%d/%Y')) as description, '' as perday_visitor_count
       UNION
       SELECT 'Total unique visitors for the day' as description, (a.purl_perday_visitor + {$visitCountDaily}) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(purl)) as purl_perday_visitor  
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW() - INTERVAL 1 DAY)
       AND purl <> 'chowan2016.com'
       ) as a
       UNION
       SELECT 'Total unique new visitors for the day' as description, (c.purl_perday_visitor + {$visitCountUnique}) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(purl)) as purl_perday_visitor  
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW() - INTERVAL 1 DAY)
       AND purl <> 'chowan2016.com'
       AND (purl) NOT IN (SELECT DISTINCT(purl)
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE DATE(FROM_UNIXTIME(timestamp)) < DATE(NOW() - INTERVAL 1 DAY))
       ) as c
       UNION
       SELECT 'Cumulative unique visitors to date' as description, (e.purl_perday_visitor + {$visitCountCumulative}) as perday_visitor_count FROM
       ( SELECT COUNT(DISTINCT(purl)) as purl_perday_visitor
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE purl <> 'chowan2016.com' AND DATE(FROM_UNIXTIME(timestamp)) <= DATE(NOW() - INTERVAL 1 DAY) 
       ) as e
       UNION
       SELECT 'Applications started - yesterday' as description, (g.purl_perday_start + h.non_purl_perday_start) as perday_visitor_count FROM
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
       WHERE DATE(FROM_UNIXTIME(sub.timestamp)) = DATE(NOW() - INTERVAL 1 DAY)
       GROUP BY sub.location ) as loc
       WHERE location LIKE '%.chowan2016.com%' {$urlWhere}
       ) as g
       JOIN
       ( SELECT COUNT(DISTINCT(timestamp)) as non_purl_perday_start
       FROM
       ( SELECT 
       SUBSTR(sub.location, 
       INSTR(sub.location, '://') + 3, 
       IF(INSTR(sub.location,'?')>0, 
        INSTR(sub.location,'?') - INSTR(sub.location, '://') - 3, 
        LENGTH(sub.location)
        )) as location, timestamp
       FROM {$this->_drupalDatabase}.watchdog_nrm sub
       WHERE DATE(FROM_UNIXTIME(sub.timestamp)) = DATE(NOW() - INTERVAL 1 DAY)
       GROUP BY sub.location ) as loc
       WHERE location LIKE 'chowan2016.com%' {$urlWhere}
       ) as h
       UNION
       SELECT 'Applications submitted - yesterday' as description, i.perday_completed as perday_visitor_count FROM
       ( SELECT COUNT(nid) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE DATE(FROM_UNIXTIME(completed)) = DATE(NOW() - INTERVAL 1 DAY) {$appWhere}
       ) as i
       UNION
       SELECT 'Cumulative applications started to date' as description, (j.purl_perday_start + k.non_purl_perday_start) as perday_visitor_count FROM
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
       WHERE DATE(FROM_UNIXTIME(sub.timestamp)) <= DATE(NOW() - INTERVAL 1 DAY)
       GROUP BY sub.location ) as loc
       WHERE location LIKE '%.chowan2016.com%' {$urlWhere}
       ) as j
       JOIN
       ( SELECT COUNT(DISTINCT(timestamp)) as non_purl_perday_start
       FROM 
       ( SELECT 
       SUBSTR(sub.location, 
       INSTR(sub.location, '://') + 3, 
       IF(INSTR(sub.location,'?')>0, 
        INSTR(sub.location,'?') - INSTR(sub.location, '://') - 3, 
        LENGTH(sub.location)
        )) as location, timestamp
       FROM {$this->_drupalDatabase}.watchdog_nrm sub
       WHERE DATE(FROM_UNIXTIME(sub.timestamp)) <= DATE(NOW() - INTERVAL 1 DAY)
       GROUP BY sub.location ) as loc
       WHERE location LIKE 'chowan2016.com%' {$urlWhere}
       ) as k
       UNION
       SELECT 'Cumulative applications submitted to date' as description, l.perday_completed as perday_visitor_count FROM
       ( SELECT COUNT(nid) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE (1) {$appWhere} AND DATE(FROM_UNIXTIME(completed)) <= DATE(NOW() - INTERVAL 1 DAY)
       ) as l
       UNION
       SELECT 'Total visit registrations - yesterday' as description, m.perday_completed as perday_visitor_count FROM
       ( SELECT COUNT(nid) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE DATE(FROM_UNIXTIME(completed)) = DATE(NOW() - INTERVAL 1 DAY) {$urlVisitSubWhere}
       ) as m
       UNION
       SELECT 'Cumulative visit registrations submitted to date' as description, n.perday_completed as perday_visitor_count FROM
       ( SELECT COUNT(nid) as perday_completed
       FROM {$this->_drupalDatabase}.webform_submissions WHERE (1) {$urlVisitSubWhere} AND DATE(FROM_UNIXTIME(completed)) <= DATE(NOW() - INTERVAL 1 DAY)
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
       AND DATE(FROM_UNIXTIME(ws.completed)) = DATE(NOW() - INTERVAL 1 DAY)
       GROUP BY w.sid
       UNION
       SELECT p.entity_id as download 
       FROM {$this->_drupalDatabase}.watchdog_nrm wn LEFT JOIN civicrm_value_nrmpurls_5 p 
       ON REPLACE(wn.purl, '.chowan2016.com', '') COLLATE utf8_unicode_ci = p.purl_145
       WHERE wn.location LIKE '%files/%' AND DATE(FROM_UNIXTIME(wn.timestamp)) = DATE(NOW() - INTERVAL 1 DAY)
       ) as e 
       GROUP BY contact_id
       ) as ue
       ) AS num
       UNION
       SELECT 'Daily engagement rate' as description, IF(denom.visit IS NULL OR denom.visit = 0, '0%', CONCAT(ROUND(num.ecount * 100/denom.visit, 2),'%')) as perday_visitor_count FROM
       (SELECT COUNT(DISTINCT(purl)) AS visit
       FROM {$this->_drupalDatabase}.watchdog_nrm WHERE DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW() - INTERVAL 1 DAY)
       ) AS denom
       JOIN 
       (SELECT COUNT(*) as ecount FROM 
       (SELECT contact_id FROM 
       (SELECT w.data as contact_id from {$this->_drupalDatabase}.webform_submitted_data w 
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid 
       INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = w.nid AND w.sid = ws.sid     
       WHERE (1) {$engageWhere}
       AND w.data IS NOT NULL and w.data <> ''
       AND DATE(FROM_UNIXTIME(ws.completed)) = DATE(NOW() - INTERVAL 1 DAY)
       GROUP BY w.sid
       UNION
       SELECT p.entity_id as download 
       FROM {$this->_drupalDatabase}.watchdog_nrm wn LEFT JOIN civicrm_value_nrmpurls_5 p 
       ON REPLACE(wn.purl, '.chowan2016.com', '') COLLATE utf8_unicode_ci = p.purl_145
       WHERE location LIKE '%files/%' AND DATE(FROM_UNIXTIME(timestamp)) = DATE(NOW() - INTERVAL 1 DAY)
       ) as e 
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
       AND w.data IS NOT NULL and w.data <> '' AND DATE(FROM_UNIXTIME(ws.completed)) <= DATE(NOW() - INTERVAL 1 DAY) 
       GROUP BY w.sid
       UNION
       SELECT p.entity_id as download 
       FROM {$this->_drupalDatabase}.watchdog_nrm wn LEFT JOIN civicrm_value_nrmpurls_5 p
       ON REPLACE(wn.purl, '.chowan2016.com', '') COLLATE utf8_unicode_ci = p.purl_145
       WHERE wn.location LIKE '%files/%' AND DATE(FROM_UNIXTIME(timestamp)) <= DATE(NOW() - INTERVAL 1 DAY)
       ) as e 
       GROUP BY contact_id
       ) as ue
       ) AS num
       UNION
       SELECT 'Cumulative engagement rate' as description, IF(denom.visit IS NULL OR denom.visit = 0, '0%', CONCAT(ROUND(num.ecount * 100/denom.visit, 2),'%')) as perday_visitor_count FROM
       (SELECT COUNT(DISTINCT(purl)) AS visit FROM {$this->_drupalDatabase}.watchdog_nrm
       ) AS denom
       JOIN 
       (SELECT COUNT(*) as ecount FROM 
       (SELECT contact_id FROM 
       (SELECT w.data as contact_id FROM 
       {$this->_drupalDatabase}.webform_submitted_data w 
       INNER JOIN {$this->_drupalDatabase}.webform_component c ON c.cid = w.cid AND c.name = 'Contact ID' AND w.nid = c.nid 
       INNER JOIN {$this->_drupalDatabase}.webform_submissions ws ON ws.nid = w.nid AND w.sid = ws.sid  
       WHERE (1) {$engageWhere}
       AND w.data IS NOT NULL and w.data <> '' AND DATE(FROM_UNIXTIME(ws.completed)) <= DATE(NOW() - INTERVAL 1 DAY)
       GROUP BY w.sid
       UNION
       SELECT p.entity_id as download 
       FROM {$this->_drupalDatabase}.watchdog_nrm wn
       LEFT JOIN civicrm_value_nrmpurls_5 p on REPLACE(wn.purl, '.chowan2016.com', '') COLLATE utf8_unicode_ci = p.purl_145
       WHERE location LIKE '%files/%' AND DATE(FROM_UNIXTIME(timestamp)) <= DATE(NOW() - INTERVAL 1 DAY)
       ) as e 
       GROUP BY contact_id
       ) as ue
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
    
    $this->updateWatchdog_nrm();
    
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
  
  /**
   * Fill watchdog_nrm with records matching watchdog including calculated purls
   * 
   * @return CRM_Core_DAO|object
   *   object that holds the results of the query, in this case no records
   */
  function updateWatchdog_nrm() {
    $sql = "INSERT INTO {$this->_drupalDatabase}.watchdog_nrm (wid, location, timestamp, purl)
            SELECT w.wid, w.location, w.timestamp, 
            SUBSTRING_INDEX(SUBSTRING_INDEX(w.location, '://', -1), '/', 1) as purl 
            FROM {$this->_drupalDatabase}.watchdog w 
            LEFT JOIN {$this->_drupalDatabase}.watchdog_nrm n ON w.wid=n.wid 
            WHERE n.wid IS NULL;";
            
    return CRM_Core_DAO::executeQuery($sql);
  }
  
  public static function getVisitCount($dateWhere) {
    $subQuery = '';
    switch ($dateWhere) {
      case 'yesterday':
        $date = "DATE(FROM_UNIXTIME(ws.completed)) = DATE(NOW() - INTERVAL 1 DAY)";
        break;
      case 'unique_yesterday':
        $date = "DATE(FROM_UNIXTIME(ws.completed)) = DATE(NOW() - INTERVAL 1 DAY)";
        $subQuery = "AND w.data NOT IN (SELECT wsub.data from {$this->_drupalDatabase}.webform_submitted_data wsub 
          INNER JOIN {$this->_drupalDatabase}.webform_component csub ON csub.cid = wsub.cid AND csub.name = 'Contact ID' AND wsub.nid = csub.nid 
          INNER JOIN {$this->_drupalDatabase}.webform_submissions wssub ON wssub.nid = wsub.nid AND wsub.sid = wssub.sid   
          WHERE wsub.data IS NOT NULL and wsub.data <> '' AND DATE(FROM_UNIXTIME(wssub.completed)) < DATE(NOW() - INTERVAL 1 DAY)
          GROUP BY wsub.sid)";
        break;
      case 'cumulative':
        $date = "DATE(FROM_UNIXTIME(ws.completed)) <= DATE(NOW() - INTERVAL 1 DAY)";
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
       GROUP BY w.sid
       ) as e 
       LEFT JOIN civicrm_value_nrmpurls_5 cp ON cp.entity_id = contact_id
       WHERE cp.entity_id IS NULL
       GROUP BY contact_id
       ) as ue";
    return CRM_Core_DAO::singleValueQuery($sql);
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
