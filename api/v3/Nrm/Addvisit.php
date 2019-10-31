<?php

/**
 * NRM AddVisit API
 *
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_nrm_addvisit($params) {
  // Filter the table once since the report will be run multiple times.
  CRM_Nrm_BAO_Nrm::updateWatchdog_nrm();
  CRM_Nrm_BAO_Nrm::filterIP();
}

