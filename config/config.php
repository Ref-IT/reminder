<?php

global $DB_DSN, $DB_USERNAME, $DB_PASSWORD, $DB_PREFIX, $SIMPLESAML, $SIMPLESAMLAUTHSOURCE, $AUTHGROUP, $ADMINGROUP;

$DB_DSN = "mysql:dbname=tu-ilmenau-de_stura;host=dbhost2";
$DB_USERNAME = "tu-ilmenau-de-03";
$DB_PASSWORD = "a4ph9JQQwbEc6sSP";
$DB_PREFIX = "reminder__";
$SIMPLESAML = dirname(dirname(dirname(__FILE__)))."/simplesamlphp";
$SIMPLESAMLAUTHSOURCE = "wayfinder";
$AUTHGROUP = "sgis";
$ADMINGROUP = "konsul,admin";
