<?php

define('NRM_PRO', 'civicrm_value_nrmlayer_6');
define('HIGH_SCHOOL', 'hs_name_160');
define('GRAD_YEAR', 'hs_graddate_153');
define('MAJOR', 'major_interests_149');
define('PURLS', 'civicrm_value_nrmpurls_5');
define('TERRITORY_COUNSELOR', 446);

$config = CRM_Core_Config::singleton();
$host = $config->userFrameworkBaseURL;
preg_match("/[^\.\/]+\.[^\.\/]+$/", $host, $matches);
define('MICROSITE', $matches[0]);

?>