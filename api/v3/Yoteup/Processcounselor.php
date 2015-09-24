<?php
define('TERRITORY_IND', 147);
define('TERRITORY_COUNSELOR', 424);

/**
 * YoteUp ProcessCounselor API
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_yoteup_processcounselor($params) {
  $contactParams = array(
    'return.custom_' . TERRITORY_IND  => 1,
    'rowCount' => 200,
  );
  $contacts = civicrm_api3('Contact', 'get', $contactParams);
  $contacts = $contacts['values'];
  
  // Get list of counselors
  $counselorParams = array(
    'contact_sub_type' => 'Counselors',
    'return.custom_' . TERRITORY_COUNSELOR => 1,
    'return.email' => 1,
    'rowCount' => 200,
  );
  $counselors = civicrm_api3('Contact', 'get', $counselorParams);
  $ind = array();
  $is_error = 0;
  $messages = array("Report Mail Triggered...");
  if ($counselors['count'] >= 1) {
    $counselors = $counselors['values'];
    foreach ($counselors as $key => $value) {
      foreach ($value['custom_' . TERRITORY_COUNSELOR] as $territory) {
        foreach ($contacts as $contact) {
          if (!empty($contact['custom_' . TERRITORY_IND]) && $territory == $contact['custom_' . TERRITORY_IND]) {
            $ind[$value['contact_id']]['email'] = $value['email'];
            $ind[$value['contact_id']]['contact_id'][] = $contact['contact_id'];
          }
        }
      }
    }
    // Now email
    $instanceId = CRM_Utils_Array::value('instanceId', $params);
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
      $_GET['id_op'] = 'in';
      foreach ($ind as $key => $value) {
        $_REQUEST['email_to_send'] = $value['email'];
        $_GET['id_value'] = implode(',', $value['contact_id']);
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

