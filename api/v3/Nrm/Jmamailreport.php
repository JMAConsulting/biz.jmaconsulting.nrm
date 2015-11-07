<?php

/**
 * YoteUp JMA Mail Report API
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nrm_jmamailreport($params) {
  $sql = "SELECT parameters FROM civicrm_job WHERE api_action='mail_report' AND run_frequency='Daily' AND is_active=1";
  $dao = CRM_Core_DAO::executeQuery($sql);
  $format = array('format' => 'csv', 'version' => 3);
  while ($dao->fetch()) {
    $parameters[] = explode("\n", $dao->parameters);
  }
  foreach ($parameters as $key => $value) {
    foreach ($value as $values) {
      list($parameter, $option) = explode("=", $values);
      if ($parameter != 'format') {
        $instances[] = array($parameter => (int)$option);
      }
    }
  }
  foreach ($instances as $instance) {
    $instance = array_merge($instance, $format);
    $result = CRM_Report_Utils_Report::processReport($instance);

    if ($result['is_error'] != 0) {
      $messages[] = $result['messages'];
    }
  }
  if (!empty($messages)) {
    return civicrm_api3_create_error($messages);
  }
  else {
    return civicrm_api3_create_success();
  }
}

