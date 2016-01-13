<?php

require_once 'nrm.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function nrm_civicrm_config(&$config) {
  _nrm_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function nrm_civicrm_xmlMenu(&$files) {
  _nrm_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function nrm_civicrm_install() {
  _nrm_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function nrm_civicrm_uninstall() {
  _nrm_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function nrm_civicrm_enable() {
  _nrm_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function nrm_civicrm_disable() {
  _nrm_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function nrm_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _nrm_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function nrm_civicrm_managed(&$entities) {
  _nrm_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function nrm_civicrm_caseTypes(&$caseTypes) {
  _nrm_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function nrm_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _nrm_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

function nrm_civicrm_alterMailParams(&$params, $context) { 
  if (CRM_Utils_Array::value('groupName', $params) == 'Report Email Sender') {
    if (CRM_Utils_Request::retrieve('instanceId', 'Int') == 74) {
      $html = "<html><body><table>\n\n";
      $f = fopen($params['attachments'][0]['fullPath'], "r");
      while (($line = fgetcsv($f)) !== FALSE) {
        $html .= "<tr>";
        foreach ($line as $cell) {
          $html .= "<td>" . htmlspecialchars($cell) . "</td>";
        }
        $html .= "</tr>\n";
      }
      fclose($f);
      $html .= "\n</table></body></html>";
      $params['html'] = $html;
    }
    $email = CRM_Utils_Request::retrieve('email_to_send', 'String', CRM_Core_DAO::$_nullObject);
    if (0 && $email) {
      if (!empty($params['toEmail'])) {
        $params['toEmail'] .= ',' . $email;
      }
      else {
        $params['toEmail'] = $email;
      }
    }
  }  
}