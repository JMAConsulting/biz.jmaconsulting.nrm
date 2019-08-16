<?php

require_once 'nrm_constants.php';

/**
 * YoteUp ProcessCounselor API
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nrm_processcounselor($params) {

  // Filter the table once since the report will be run multiple times.
  //CRM_Nrm_BAO_Nrm::filterIP();
  //CRM_Nrm_BAO_Nrm::updateWatchdog_nrm();
  
  // Get list of counselors
  //$counsellorCount = civicrm_api3('Contact', 'getCount', array('contact_sub_type' => 'Counselors'));
  $counselorParams = array(
    'contact_sub_type' => 'Counselors',
    'sequential' => 1,
    'return' => array("email", "custom_459", "display_name"),
    // 'rowCount' => $counsellorCount,
  );
  $counselors = civicrm_api3('Contact', 'get', $counselorParams);
  $ind = array();
  $is_error = 0;
  $messages = array("Report Mail Triggered...");
  if ($counselors['count'] >= 1) {
    $counselors = $counselors['values'];
    foreach ($counselors as $key => $value) {
      if (!empty($value['custom_' . TERRITORY_COUNSELOR])) {
        $ind[$key]['contact_id'] = $value['contact_id'];
        $ind[$key]['email'] = $value['email'];
      }
    }
    // Now email
    $instanceId = (int)CRM_Utils_Array::value('instanceId', $params);
    $_REQUEST['instanceId'] = $instanceId;
    $_REQUEST['sendmail'] = CRM_Utils_Array::value('sendmail', $params, 1);

    // if cron is run from terminal --output is reserved, and therefore we would provide another name 'format'
    $_REQUEST['output'] = CRM_Utils_Array::value('format', $params, CRM_Utils_Array::value('output', $params, 'pdf'));
    $_REQUEST['reset'] = CRM_Utils_Array::value('reset', $params, 1);

    $optionVal = CRM_Report_Utils_Report::getValueFromUrl($instanceId);
    $templateInfo = CRM_Core_OptionGroup::getRowValues('report_template', $optionVal, 'value');
    if (strstr(CRM_Utils_Array::value('name', $templateInfo), '_Form')) {
      $obj = new CRM_Report_Page_Instance();
      $instanceInfo = array();
      CRM_Report_BAO_ReportInstance::retrieve(array('id' => $instanceId), $instanceInfo);
      if (!empty($instanceInfo['title'])) {
        $obj->assign('reportTitle', $instanceInfo['title']);
      }
      else {
        $obj->assign('reportTitle', $templateInfo['label']);
      }
      foreach ($ind as $key => $value) {
        $_REQUEST['email_to_send'] = $value['email'];
        $_GET['counsellor_id_value'] = $value['contact_id'];
        $wrapper = new CRM_Utils_Wrapper();
        $arguments = array(
          'urlToSession' => array(
             array(
               'urlVar' => 'instanceId',
               'type' => 'Positive',
               'sessionVar' => 'instanceId',
               'default' => 'null',
             ),                               
           ),
          'ignoreKey' => TRUE,
        );
        $messages[] = $wrapper->run($templateInfo['name'], NULL, $arguments);
      }
    }
  }
  if ($is_error == 0) {
    return civicrm_api3_create_success();
  }
  else {
    return civicrm_api3_create_error($messages);
  }
}

