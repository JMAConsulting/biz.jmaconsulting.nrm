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
  public static function reportSelectClause(&$form, $columns, $addTemp = FALSE, $addWSID = TRUE) {
    if ($addTemp) {
      self::createInquiry();
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
        $select[] = "{$column['columnName']} AS '{$column['title']}'";
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
          $select[] = "GROUP_CONCAT(if(wc.name='{$col}', {$columnName}, NULL)) AS '{$column['title']}'";
        }
      }
      elseif (!CRM_Utils_Array::value('ignore_group_concat', $column)) {
        $columnName = CRM_Utils_Array::value('columnName', $column, $defaultColumnName);
        $col = (in_array($key, $abr)) ? substr($key, 0, strpos($key, '_')) : $column['title'];
        $select[] = "GROUP_CONCAT(if(wc.name='{$col}', {$columnName}, NULL)) AS '{$column['title']}'";
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
  public static function reportFromClause(&$from, $tempTable = FALSE, $tempName = array()) {
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
      LEFT JOIN civicrm_country c ON wsd.data = c.id ";
    if ($tempTable) {
      if (!empty($tempName)) {
        foreach ($tempName as $table) {
          $from .= " LEFT JOIN {$table} {$table}_alias ON wsd.data COLLATE utf8_unicode_ci = {$table}_alias.value";
        }
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
  public static function reportWhereClause(&$where, $webFormId) {
    $where = "WHERE wc.nid IN ({$webFormId}) AND wsd.nid IN ({$webFormId}) AND DATE(FROM_UNIXTIME(ws.completed)) = DATE(NOW() - INTERVAL 1 DAY)";
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