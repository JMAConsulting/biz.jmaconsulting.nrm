<?php
/**
 * NRM extension integrates CiviCRM's reports
 * 
 * Copyright (C) 2015 JMA Consulting
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * Support: https://github.com/JMAConsulting/biz.jmaconsulting.nrm/issues
 * 
 * Contact: info@jmaconsulting.biz
 *          JMA Consulting
 *          215 Spadina Ave, Ste 400
 *          Toronto, ON  
 *          Canada   M5T 2C7
 */

class CRM_Nrm_BAO_Nrm extends CRM_Core_DAO {

  /*
   * function to build select clause for reports
   *
   * @access public
   * @static
   *
   *
   */
  public static function reportSelectClause(&$form, $columns, $addTemp = FALSE, $addWSID = TRUE, $inq = 300) {
    CRM_Core_DAO::executeQuery('SET SESSION group_concat_max_len = 204800');
    if ($addTemp) {
      self::createInquiry($inq);
    }
    $form->_columnHeaders = $select = array();
    if ($addWSID) {
      $select[] = 'wsd.sid';
    }
    $defaultColumnName = 'wsd.data';
    $abr = array('Country_Code', 'State_Abbr');
    foreach ($columns as $key => $column) {
      $form->_columnHeaders[$key]['title'] = ts($column['title']);
      if (CRM_Utils_Array::value('ignore_group_concat', $column)) {
        $select[] = "{$column['columnName']} AS '{$key}'";
      }
      if ((CRM_Utils_Array::value('is_alias', $column) || CRM_Utils_Array::value('same_alias', $column)) && !CRM_Utils_Array::value('ignore_group_concat', $column)) {
        $columnName = CRM_Utils_Array::value('columnName', $column, $defaultColumnName);
        if (CRM_Utils_Array::value('is_alias', $column)) {
          $col = (in_array($key, $abr)) ? substr($key, 0, strpos($key, '_')) : $column['alias_new'];
        }
        else {
          $col = (in_array($key, $abr)) ? substr($key, 0, strpos($key, '_')) : $column['title'];
        }

        if (CRM_Utils_Array::value('same_alias', $column)) {
          $select[] = "GROUP_CONCAT(if((wc.name='{$col}' AND wc.cid = {$column['cid']}), {$columnName}, NULL)) AS '{$column['title']}_{$column['alias']}'";
        }
        else {
          $select[] = "GROUP_CONCAT(if(wc.name='{$col}', {$columnName}, NULL)) AS '{$key}'";
        }
      }
      elseif (!CRM_Utils_Array::value('ignore_group_concat', $column)) {
        $columnName = CRM_Utils_Array::value('columnName', $column, $defaultColumnName);
        $col = (in_array($key, $abr)) ? substr($key, 0, strpos($key, '_')) : $column['title'];
        $select[] = "GROUP_CONCAT(DISTINCT(if(wc.name='{$col}', {$columnName}, NULL))) AS '{$key}'";
      }
    }
    $form->_select .= " SELECT " . implode(',', $select);
  }

  /*
   * function to build from clause for reports
   *
   * @access public
   * @static
   *
   *
   */
  public static function reportFromClause(&$from, $tempTable = FALSE, $tempName = array(), $ov = array()) {
    $drupalDb = 'chowan_dru';
    $from = "FROM {$drupalDb}.webform_submitted_data wsd 
      LEFT JOIN civicrm_contact contact_civireport ON wsd.data = contact_civireport.id AND wsd.cid = 2
      LEFT JOIN {$drupalDb}.webform_component wc ON wc.cid = wsd.cid AND wc.nid = wsd.nid
      LEFT JOIN {$drupalDb}.webform_submissions ws ON ws.sid = wsd.sid AND ws.nid=wsd.nid
      LEFT JOIN civicrm_option_value g ON wsd.data COLLATE utf8_unicode_ci = g.value AND g.option_group_id = 3
      LEFT JOIN civicrm_option_value pt1 ON wsd.data COLLATE utf8_unicode_ci = pt1.value AND pt1.option_group_id = 35
      LEFT JOIN civicrm_option_value pt2 ON wsd.data COLLATE utf8_unicode_ci = pt2.value AND pt2.option_group_id = 35
      LEFT JOIN civicrm_country c ON wsd.data = c.id ";
    if ($tempTable) {
      if (!empty($tempName)) {
        foreach ($tempName as $table) {
          $from .= " LEFT JOIN {$table} {$table}_alias ON wsd.data COLLATE utf8_unicode_ci = {$table}_alias.value";
        }
      }
    }
    if (!empty($ov)) {
      foreach ($ov as $id => $name) {
        $from .= " LEFT JOIN civicrm_option_value {$name}_alias ON wsd.data COLLATE utf8_unicode_ci = {$name}_alias.value AND {$name}_alias.option_group_id = {$id}";
      }
    }
  }
  
  /*
   * function to build where clause for reports
   *
   * @access public
   * @static
   *
   *
   */
  public static function reportWhereClause(&$where, $webFormId, $cid = 2) {
    self::createUniqueSid($webFormId, $cid);
    $where = "WHERE wc.nid IN ({$webFormId}) AND DATE(FROM_UNIXTIME(ws.completed)) = DATE(NOW() - INTERVAL 1 DAY) AND wsd.nid IN ({$webFormId}) AND wsd.sid IN (SELECT sids FROM validsids)";
    //$where = "WHERE wc.nid IN ({$webFormId}) AND DATE(FROM_UNIXTIME(ws.completed)) = '2017-08-05' AND wsd.nid IN ({$webFormId}) AND wsd.sid IN (SELECT sids FROM validsids)";
  }
  
  /*
   * function to build groub by clause for reports
   *
   * @access public
   * @static
   *
   *
   */
  public static function reportGroupByClause(&$form, $columns) {    
  }

  /*
   * function to add temp table
   *
   * @access public
   * @static
   *
   *
   */ 
  public static function createInquiry($inq) {
    $drupalDatabase = 'chowan_dru';
    $sql = "SELECT extra
      FROM {$drupalDatabase}.webform_component
      WHERE form_key = 'type_of_inquiry' AND nid = {$inq}";
    $result = CRM_Core_DAO::singleValueQuery($sql);
    $result = unserialize($result);
    $inquiry = explode('|', $result['items']);
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS inquiry");
    CRM_Core_DAO::executeQuery("CREATE TEMPORARY TABLE IF NOT EXISTS inquiry (
      value int(50) NOT NULL,
      name varchar(64) NOT NULL)");
    $sql = "INSERT INTO inquiry VALUES";
    foreach ($inquiry as $key => &$items) {
      $items = trim(preg_replace('/[0-9]+/', NULL, $items));
      if ($key != 0) {
        $vals[] = " ({$key}, '{$items}')";
      }
    }
    $sql .= implode(',', $vals);
    CRM_Core_DAO::executeQuery($sql);
  }

  /*
   * Function to get latest submissions
   *
   * @access public
   * @static
   *
   *
   */ 
  public static function createUniqueSid($webFormId, $cid = 2) {
    $drupalDatabase = 'chowan_dru';
    CRM_Core_DAO::executeQuery("DROP TEMPORARY TABLE IF EXISTS validsids");
    CRM_Core_DAO::executeQuery("CREATE TEMPORARY TABLE validsids AS
      SELECT MAX(d.sid) as sids from {$drupalDatabase}.webform_submitted_data d
      LEFT JOIN {$drupalDatabase}.webform_submissions s ON s.sid = d.sid
      WHERE d.cid = {$cid} AND d.nid IN ({$webFormId}) AND s.nid IN ({$webFormId})
      GROUP BY d.data");
  }
  
  /**
   * Fill watchdog_nrm with records matching watchdog including calculated purls
   * 
   * @return CRM_Core_DAO|object
   *   object that holds the results of the query, in this case no records
   */
  function updateWatchdog_nrm() {
    $drupalDatabase = 'chowan_dru';

    $sql = "INSERT INTO {$drupalDatabase}.watchdog_nrm (wid, location, timestamp, purl)
            SELECT w.wid, w.location, w.timestamp, 
            SUBSTRING_INDEX(SUBSTRING_INDEX(w.location, '://', -1), '/', 1) as purl 
            FROM {$drupalDatabase}.watchdog w 
            LEFT JOIN {$drupalDatabase}.watchdog_nrm n ON w.wid=n.wid 
            WHERE n.wid IS NULL";
            
    return CRM_Core_DAO::executeQuery($sql);
  }
  
  /**
   * Filter out the IPs used for testing from the count.
   * 
   * @return CRM_Core_DAO|object
   *   object that holds the results of the query, in this case no records
   */
  function filterIP() {
    $drupalDatabase = 'chowan_dru';

    $options = civicrm_api3('OptionValue', 'get', array(
      'sequential' => 1,
      'return' => array("label"),
      'option_group_id' => "exclude_ip",
    ));

    if ($options['count'] > 0) {
      foreach ($options['values'] as $value) {
        $ips[] = "'" . trim($value['label']) . "'";
      }

      $ipList = implode(', ', $ips);

      $sql = "DELETE FROM {$drupalDatabase}.watchdog WHERE hostname IN ({$ipList})";
            
      CRM_Core_DAO::executeQuery($sql);
    }

    // Delete accidental visits and old PURL visits.
    CRM_Core_DAO::executeQuery("DELETE FROM {$drupalDatabase}.watchdog WHERE location LIKE '%chowan2017.com%'");
    CRM_Core_DAO::executeQuery("DELETE FROM {$drupalDatabase}.watchdog WHERE location LIKE '%.com/oops%'");
    CRM_Core_DAO::executeQuery("DELETE FROM {$drupalDatabase}.watchdog WHERE location LIKE '%/sites/all/modules/civicrm/bin/cron.php%'");
    
    CRM_Core_DAO::executeQuery("DELETE FROM {$drupalDatabase}.watchdog_nrm WHERE location LIKE '%chowan2017.com%'");
    CRM_Core_DAO::executeQuery("DELETE FROM {$drupalDatabase}.watchdog_nrm WHERE location LIKE '%.com/oops%'");
    CRM_Core_DAO::executeQuery("DELETE FROM {$drupalDatabase}.watchdog_nrm WHERE location LIKE '%/sites/all/modules/civicrm/bin/cron.php%'");
    
    // Delete test visits.
    $testPurls = array(
      'michaellearch',
      'chernan',
      'florreyescortez',
      'kaylahill15',
      'valarie',
      'mattleonard',
      'michaellerch',
      'nykiyahanthony',
      '2018prospect',
      'prospect',
      'aniyagregory',
      'antonioscott',
      'ginohernandez',
      'johnfricke',
      'keynishakelson',
      'kimbailey',
      'outlooktest',
      'thomaswells',
      'xavierstewart',
      'yahootest',
      'www',
      'elizabethhixon',
      'edsellopez',
      'chowan.server282.com',
      'chowan.server288.com',
      'staging',
      'nancyaltstatt',
      'blog',
      'test',
      'wp',
      'sandbox',
      'prod',
      'digett',
      'pradeepnayak',
    );

    foreach ($testPurls as $purl) {
      CRM_Core_DAO::executeQuery("DELETE FROM {$drupalDatabase}.watchdog WHERE location LIKE '%{$purl}%'");
      CRM_Core_DAO::executeQuery("DELETE FROM {$drupalDatabase}.watchdog_nrm WHERE location LIKE '%{$purl}%'");
    }
    
    CRM_Core_DAO::executeQuery("DELETE FROM {$drupalDatabase}.watchdog_nrm WHERE purl = 'chowan2018.com'");
    CRM_Core_DAO::executeQuery("DELETE FROM {$drupalDatabase}.watchdog_nrm WHERE purl = 'chowan2018.com.'");
  }
}
