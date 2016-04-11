<?php

define('SGISBASE', dirname(dirname(__FILE__)));

set_include_path(get_include_path() . PATH_SEPARATOR . SGISBASE . '/lib');

function PEAR_is_Error($obj) {
  return (new PEAR)->isError($obj);
}

require_once SGISBASE.'/config/config.php';
require_once SGISBASE.'/lib/inc.error.php';
require_once SGISBASE.'/lib/inc.simplesaml.php';
require_once SGISBASE.'/lib/inc.db.php';
require_once SGISBASE.'/lib/inc.nonce.php';
require_once SGISBASE.'/lib/inc.header.php';
require_once 'ssp.class.php';
require_once 'Net/SMTP.php';
require_once 'Mail.php';
require_once 'Mail/RFC822.php';

