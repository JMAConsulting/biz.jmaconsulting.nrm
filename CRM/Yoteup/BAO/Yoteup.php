<?php
/**
 * Yote Up extension integrates CiviCRM's reports
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
 * Support: https://github.com/JMAConsulting/biz.jmaconsulting.yoteup/issues
 * 
 * Contact: info@jmaconsulting.biz
 *          JMA Consulting
 *          215 Spadina Ave, Ste 400
 *          Toronto, ON  
 *          Canada   M5T 2C7
 */

class CRM_Yoteup_BAO_Yoteup extends CRM_Core_DAO {

  /*
   * function to build select clause for reports
   *
   * @access public
   * @static
   *
   *
   */
  public static function reportSelectClause(&$form, $columns) {
    self::createInquiry();
    $form->_columnHeaders = $select = array();
    $select[] = 'wsd.sid';
    $defaultColumnName = 'wsd.data';
    $abr = array('Country_Code', 'State_Abbr');
    $ignoreSelectClause = array('State');
    foreach ($columns as $key => $column) {
      $form->_columnHeaders[$key]['title'] = ts($column['title']);
      if (in_array($key, $ignoreSelectClause)) {
        continue;
      }
      if (CRM_Utils_Array::value('ignore_group_concat', $column)) {
        $select[] = "{$column['columnName']} AS '{$column['title']}'";
      }
      else {
        $columnName = CRM_Utils_Array::value('columnName', $column, $defaultColumnName);
        $col = (in_array($key, $abr)) ? substr($key, 0, strpos($key, '_')) : $column['title'];
        $select[] = "GROUP_CONCAT(if(wc.name='{$col}', {$columnName}, NULL)) AS '{$column['title']}'";
      }
    }
    $form->_select = "
      SELECT sq.*, sp.name AS 'State' FROM 
      (SELECT " . implode(',', $select);
  }

  /*
   * function to build from clause for reports
   *
   * @access public
   * @static
   *
   *
   */
  public static function reportFromClause(&$from) {
    $config = CRM_Core_Config::singleton();
    $dsnArray = DB::parseDSN($config->userFrameworkDSN);
    $drupalDb = $dsnArray['database'];
    $from = "FROM {$drupalDb}.webform_submitted_data wsd 
      LEFT JOIN civicrm_contact contact_civireport ON wsd.data = contact_civireport.id AND wsd.cid = 2
      LEFT JOIN {$drupalDb}.webform_component wc ON wc.cid = wsd.cid 
      LEFT JOIN {$drupalDb}.webform_submissions ws ON ws.sid = wsd.sid 
      LEFT JOIN civicrm_option_value g ON wsd.data COLLATE utf8_unicode_ci = g.value AND g.option_group_id = 3
      LEFT JOIN civicrm_option_value pt1 ON wsd.data COLLATE utf8_unicode_ci = pt1.value AND pt1.option_group_id = 35
      LEFT JOIN civicrm_option_value pt2 ON wsd.data COLLATE utf8_unicode_ci = pt2.value AND pt2.option_group_id = 35
      LEFT JOIN civicrm_country c ON wsd.data = c.id
      LEFT JOIN inquiry i ON wsd.data COLLATE utf8_unicode_ci = i.value ";
  }
  
  /*
   * function to build where clause for reports
   *
   * @access public
   * @static
   *
   *
   */
  public static function reportWhereClause(&$where, $webFormId) {
    $where = "WHERE wc.nid = {$webFormId} AND wsd.nid = {$webFormId} AND DATE(FROM_UNIXTIME(ws.completed)) = DATE(NOW() - INTERVAL 1 DAY)";
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
  public static function createInquiry() {
    $config = CRM_Core_Config::singleton();
    $dsnArray = DB::parseDSN($config->userFrameworkDSN);
    $drupalDatabase = $dsnArray['database'];
    $sql = "SELECT extra
      FROM {$drupalDatabase}.webform_component
      WHERE form_key = 'type_of_inquiry' AND nid = 72";
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
}