<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

global $attributes, $logoutUrl, $AUTHROUP, $nonce, $ADMINGROUP;
ob_start('ob_gzhandler');

require_once "../lib/inc.all.php";
requireGroup($AUTHGROUP);
$isadmin = hasGroup($ADMINGROUP);

if (isset($_POST["group"]) && !$isadmin) {
  requireGroup($_POST["group"]);
}

function escapeMe($d, $row) {
  return htmlspecialchars($d);
}

foreach (["to_email","cc_email","bcc_email"] as $key) {
  if (!isset($_REQUEST[$key])) continue;
  if (trim($_REQUEST[$key]) == "") continue;
  $obj->parseAddressList($_REQUEST[$key], 'example.org', FALSE);
}

if (isset($_POST["action"])) {
 $msgs = Array();
 $ret = false;
 $target = false;
 if (!isset($_REQUEST["nonce"]) || $_REQUEST["nonce"] !== $nonce) {
  $msgs[] = "Formular veraltet - CSRF Schutz aktiviert.";
 } else {
  $logId = logThisAction();
  switch ($_POST["action"]):
  case "message.table":
   header("Content-Type: text/json; charset=UTF-8");
   $columns = array(
     array( 'db' => 'id',                 'dt' => 'id' ),
     array( 'db' => 'group',              'dt' => 'group', 'formatter' => 'escapeMe' ),
     array( 'db' => 'from_email',         'dt' => 'from_email', 'formatter' => 'escapeMe' ),
     array( 'db' => 'to_email',           'dt' => 'to_email', 'formatter' => 'escapeMe' ),
     array( 'db' => 'cc_email',           'dt' => 'cc_email', 'formatter' => 'escapeMe' ),
     array( 'db' => 'bcc_email',          'dt' => 'bcc_email', 'formatter' => 'escapeMe' ),
     array( 'db' => 'subject',            'dt' => 'subject', 'formatter' => 'escapeMe' ),
     array( 'db' => 'message',            'dt' => 'message', 'formatter' => 'escapeMe' ),
     array( 'db' => 'created_by',         'dt' => 'created_by', 'formatter' => 'escapeMe' ),
     array( 'db' => 'created_at',         'dt' => 'created_at',
       'formatter' => function( $d, $row ) {
         return $d ? date( 'Y-m-d', strtotime($d)) : "";
       }
     ),
     array( 'db'    => 'pending',          'dt'    => 'active',
       'formatter' => function( $d, $row ) {
         return $d ? "ja" : "nein";
       }
     ),
   );

   if ($isadmin) {
     $where = NULL;
   } else {
     $grps = mysql_escape_mimic(getGroups());
     $where = implode(' OR ', $grps);
   }

   echo json_encode(
     SSP::complex( $_POST, ["dsn" => $DB_DSN, "user" => $DB_USERNAME, "pass" => $DB_PASSWORD], "{$DB_PREFIX}message_current", /* primary key */ "id", $columns, NULL, $where )
   );
  exit;
  case "message.insert":
   if (!isset($_POST["group"])) die("Missing argument.");
   $ret = dbMessageInsert($_POST["subject"], $_POST["message"], $_POST["from_email"], $_POST["to_email"], $_POST["cc_email"], $_POST["bcc_email"], $_POST["active"], $_POST["group"], $_POST["dates"]);
   $msgs[] = "Nachricht wurde erstellt.";
   if ($ret !== false)
     $target = $_SERVER["PHP_SELF"]."?tab=message.edit&message_id=".$ret;
  break;
  case "message.update":
   if (!isset($_POST["id"])) die("Missing argument.");
   $id = (int) $_POST["id"];

   $message = getMessageDetailsById($id);
   if ($message === false) die("invalid id");
   requireGroup($message["group"]);

   $dd_old = array_unique(array_map("trim",explode(",", $_POST["dates_old"])));
   $dd = array_unique(array_map("trim",explode(",", $_POST["dates"])));
   $ddadd = array_diff($dd, $dd_old);
   $ddel = array_diff($dd_old, $dd);

   $ret = dbMessageUpdate($id, $_POST["subject"], $_POST["message"], $_POST["from_email"], $_POST["to_email"], $_POST["cc_email"], $_POST["bcc_email"], $_POST["active"], $ddadd, $dddel);

   $msgs[] = "Nachricht wurde aktualisiert.";
  break;
  case "message.delete":
   if (!isset($_POST["id"])) die("Missing argument.");
   $id = (int) $_POST["id"];

   $message = getMessageDetailsById($id);
   if ($message === false) die("invalid id");
   requireGroup($message["group"]);

   $ret = dbMessageDelete($id);

   $msgs[] = "Nachricht wurde entfernt.";
  break;
  default:
   logAppend($logId, "__result", "invalid action");
   die("Aktion nicht bekannt.");
  endswitch;
 } /* switch */

 logAppend($logId, "__result", ($ret !== false) ? "ok" : "failed");
 logAppend($logId, "__result_msg", $msgs);

 $result = Array();
 $result["msgs"] = $msgs;
 $result["ret"] = ($ret !== false);
 if ($target !== false)
   $result["target"] = $target;

 header("Content-Type: text/json; charset=UTF-8");
 echo json_encode($result);
 exit;
}

require "../template/header.tpl";

if (!isset($_REQUEST["tab"])) {
  $_REQUEST["tab"] = "message";
}

switch($_REQUEST["tab"]) {
  case "message":
  require "../template/messages.tpl";
  break;
  case "message.new":
  require "../template/messages_new.tpl";
  break;
  case "message.edit":
  require "../template/messages_edit.tpl";
  break;
  case "message.delete":
  require "../template/messages_delete.tpl";
  break;
  case "mail":
  require "../template/mail.tpl";
  break;
  default:
  die("invalid tab name");
}

require "../template/footer.tpl";

exit;

