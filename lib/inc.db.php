<?php
global $pdo;
global $DB_DSN, $DB_USERNAME, $DB_PASSWORD, $DB_PREFIX;

$pdo = new PDO($DB_DSN, $DB_USERNAME, $DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8; SET lc_time_names = 'de_DE';"));

# Nachrichten

$r = $pdo->query("SELECT COUNT(*) FROM {$DB_PREFIX}message");
if ($r === false) {
  $pdo->query("CREATE TABLE {$DB_PREFIX}message (
                id INT NOT NULL AUTO_INCREMENT,
                active BOOLEAN NOT NULL DEFAULT 1,
                `group` VARCHAR(64) NOT NULL,
                from_email VARCHAR(128) NOT NULL,
                to_email VARCHAR(512) NOT NULL,
                cc_email VARCHAR(512) NOT NULL,
                bcc_email VARCHAR(512) NOT NULL,
                subject VARCHAR(128) NOT NULL,
                message VARCHAR(32768) NOT NULL,
                created_by VARCHAR(128) NOT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX(`group`),
                PRIMARY KEY (id)
               ) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;") or httperror(print_r($pdo->errorInfo(),true));
}

$r = $pdo->query("SELECT COUNT(*) FROM {$DB_PREFIX}schedule");
if ($r === false) {
  $pdo->query("CREATE TABLE {$DB_PREFIX}schedule (
                message_id INT NOT NULL,
                send_date DATE NOT NULL,
                PRIMARY KEY(message_id, send_date),
                INDEX(`send_date`),
                FOREIGN KEY (message_id) REFERENCES {$DB_PREFIX}message(id) ON DELETE CASCADE
               ) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;") or httperror(print_r($pdo->errorInfo(),true));
}

# dataTables view
$r = $pdo->query("SELECT COUNT(*) FROM {$DB_PREFIX}message_is_active");
if ($r === false) {
  $pdo->query("CREATE VIEW {$DB_PREFIX}message_is_active AS
     SELECT DISTINCT s.message_id as message_id
       FROM {$DB_PREFIX}schedule s
      WHERE s.send_date > CURRENT_DATE;")
  or httperror(print_r($pdo->errorInfo(),true));
}

$r = $pdo->query("SELECT COUNT(*) FROM {$DB_PREFIX}message_current");
if ($r === false) {
  $pdo->query("CREATE VIEW {$DB_PREFIX}message_current AS
SELECT m.*, (m.active AND (am.message_id IS NOT NULL)) as pending
   FROM {$DB_PREFIX}message m
        LEFT JOIN {$DB_PREFIX}message_is_active am ON am.message_id = m.id;")
  or httperror(print_r($pdo->errorInfo(),true));
}

# txlog

$r = $pdo->query("SELECT COUNT(*) FROM {$DB_PREFIX}txlog");
if ($r === false) {
  $pdo->query("CREATE TABLE {$DB_PREFIX}txlog (
                message_id INT NOT NULL,
                send_date DATE NOT NULL,
                PRIMARY KEY(message_id, send_date),
                INDEX(`send_date`),
                FOREIGN KEY (message_id) REFERENCES {$DB_PREFIX}message(id) ON DELETE CASCADE
               ) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;") or httperror(print_r($pdo->errorInfo(),true));
}

# Log

$r = $pdo->query("SELECT COUNT(*) FROM {$DB_PREFIX}log");
if ($r === false) {
  $pdo->query("CREATE TABLE {$DB_PREFIX}log (
                id INT NOT NULL AUTO_INCREMENT,
                action VARCHAR(254) NOT NULL,
                evtime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                responsible VARCHAR(254),
                PRIMARY KEY(id)
               ) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;") or httperror(print_r($pdo->errorInfo(),true));
}

$r = $pdo->query("SELECT COUNT(*) FROM {$DB_PREFIX}log_property");
if ($r === false) {
  $pdo->query("CREATE TABLE {$DB_PREFIX}log_property (
                id INT NOT NULL AUTO_INCREMENT,
                log_id INT NOT NULL,
                name VARCHAR(128) NOT NULL,
                value LONGTEXT,
                INDEX(log_id),
                INDEX(name),
                INDEX(name, value(256)),
                PRIMARY KEY(id),
                FOREIGN KEY (log_id) REFERENCES {$DB_PREFIX}log(id) ON DELETE CASCADE
              ) ENGINE=INNODB CHARACTER SET utf8 COLLATE utf8_general_ci;") or httperror(print_r($pdo->errorInfo(),true));
}

function logThisAction() {
  global $pdo, $DB_PREFIX;
  $query = $pdo->prepare("INSERT INTO {$DB_PREFIX}log (action, responsible) VALUES (?, ?)");
  $query->execute(Array($_REQUEST["action"], getUsername())) or httperror(print_r($query->errorInfo(),true));
  $logId = $pdo->lastInsertId();
  foreach ($_REQUEST as $key => $value) {
    $key = "request_$key";
    logAppend($logId, $key, $value);
  }
  return $logId;
}

function logAppend($logId, $key, $value) {
  global $pdo, $DB_PREFIX;
  $query = $pdo->prepare("INSERT INTO {$DB_PREFIX}log_property (log_id, name, value) VALUES (?, ?, ?)");
  if (is_array($value)) $value = print_r($value, true);
  $query->execute(Array($logId, $key, $value)) or httperror(print_r($query->errorInfo(),true));
}

function dbMessageInsert($subject, $message, $from_email, $to_email, $cc_email, $bcc_email, $active, $group, $dates) {
  global $pdo, $DB_PREFIX;

  $pdo->beginTransaction() or httperror(print_r($pdo->errorInfo(),true));

  $query = $pdo->prepare("INSERT {$DB_PREFIX}message (subject, message, from_email, to_email, cc_email, bcc_email, active, `group`, created_by) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?)");
  $ret = $query->execute(Array($subject, $message, $from_email, $to_email, $cc_email, $bcc_email, $active, $group, getUsername())) or httperror(print_r($query->errorInfo(),true));
  if ($ret === false) {
    $pdo->commit();
    return $ret;
  }

  $msgId = $pdo->lastInsertId();
  $dates = explode(",", $dates);
  $dates = array_unique(array_map("trim", $dates));

  $query = $pdo->prepare("INSERT {$DB_PREFIX}schedule (message_id, send_date) VALUES ( ?, ? )");
  foreach($dates as $d) {
    if ($d == "") continue;
    $ret = $query->execute(Array($msgId, $d)) or httperror(print_r($query->errorInfo(),true));
    if ($ret === false) {
      $pdo->commit();
      return $ret;
    }
  }

  $ret = $pdo->commit() or httperror(print_r($pdo->errorInfo(),true));
  if ($ret === false)
    return $ret;

  return $msgId;
}

function getMessageDetailsById($id) {
   global $pdo, $DB_PREFIX;
   $query = $pdo->prepare("SELECT m.*, group_concat(s.send_date separator ', ') as `dates` FROM {$DB_PREFIX}message m left join {$DB_PREFIX}schedule s on m.id = s.message_id WHERE m.id = ? group by m.id");
   $query->execute(Array($id)) or httperror(print_r($query->errorInfo(),true));
   if ($query->rowCount() == 0) return false;
   return $query->fetch(PDO::FETCH_ASSOC);
}

function dbMessageUpdate($id, $subject, $message, $from_email, $to_email, $cc_email, $bcc_email, $active, $ddadd, $ddold) {
  global $pdo, $DB_PREFIX;

  $query = $pdo->prepare("UPDATE {$DB_PREFIX}message SET subject = ?, message = ?, from_email = ?, to_email = ?, cc_email = ?, bcc_email = ?, active = ? WHERE id = ?");
  $query->execute(Array($subject, $message, $from_email, $to_email, $cc_email, $bcc_email, $active, $id)) or httperror(print_r($query->errorInfo(),true));

  $query = $pdo->prepare("INSERT {$DB_PREFIX}schedule (message_id, send_date) VALUES ( ?, ? )");
  foreach($ddadd as $d) {
    if ($d == "") continue;
    $query->execute(Array($id, $d)) or httperror(print_r($query->errorInfo(),true));
  }

  $query = $pdo->prepare("DELETE FROM {$DB_PREFIX}schedule WHERE message_id = ?, send_date = ?");
  foreach($dddel as $d) {
    if ($d == "") continue;
    $query->execute(Array($id, $d)) or httperror(print_r($query->errorInfo(),true));
  }
}

function dbMessageDelete($id) {
  global $pdo, $DB_PREFIX;

  $query = $pdo->prepare("DELETE FROM {$DB_PREFIX}message WHERE id = ?");
  $query->execute(Array($id)) or httperror(print_r($query->errorInfo(),true));
}

function listAllMail() {
   global $pdo, $DB_PREFIX;
   $query = $pdo->prepare("SELECT IFNULL(MAX(t.send_date),CURRENT_DATE) as ld FROM {$DB_PREFIX}txlog t WHERE (t.send_date <= CURRENT_DATE)");
   $query->execute(Array()) or httperror(print_r($query->errorInfo(),true));
   $row = $query->fetch(PDO::FETCH_ASSOC);
   $minDate = $row["ld"];

   $query = $pdo->prepare("SELECT s.* FROM {$DB_PREFIX}schedule s NATURAL LEFT JOIN {$DB_PREFIX}txlog t WHERE (s.send_date <= CURRENT_DATE) AND (t.send_date IS NULL OR t.send_date < CURRENT_DATE) AND (s.send_date >= ?)");
   $query->execute(Array($minDate)) or httperror(print_r($query->errorInfo(),true));
   return $query->fetchAll(PDO::FETCH_ASSOC);
}

function recordSendMail($msgId, $d) {
   global $pdo, $DB_PREFIX;
  $query = $pdo->prepare("INSERT {$DB_PREFIX}txlog (message_id, send_date) VALUES ( ?, ? )");
  $query->execute(Array($msgId, $d)) or httperror(print_r($query->errorInfo(),true));
}

function getDBDump() {
  global $pdo, $DB_PREFIX;
  $tables = Array("message" => "id",
                  "schedule" => "message_id,send_date",
                  "txlog" => "message_id,send_date",
                 );
  $ret = Array();
  foreach ($tables as $t => $s) {
    $query = $pdo->prepare("SELECT * FROM {$DB_PREFIX}{$t} ORDER BY {$s}");
    $query->execute(Array()) or httperror(print_r($query->errorInfo(),true));
    $ret[$t] = $query->fetchAll(PDO::FETCH_ASSOC);
  }
  ksort($ret);
  return $ret;
}

function mysql_escape_mimic($inp) {
 if(is_array($inp))
   return array_map(__METHOD__, $inp);

 if(!empty($inp) && is_string($inp)) {
   return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
 }

 return $inp;
}

# vim: set expandtab tabstop=8 shiftwidth=8 :

