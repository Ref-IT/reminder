<?php

#ini_set('display_errors', 1);
#ini_set('display_startup_errors', 1);
#error_reporting(E_ALL);

require_once "../lib/inc.all.php";

header("Content-Type: text/plain; charset=UTF-8");

$emailToSend = listAllMail();

$mail_object = Mail::factory('smtp', array("debug" => false, "timeout" => 5));
$obj = new Mail_RFC822();

foreach ($emailToSend as $e) {
  $m = getMessageDetailsById($e["message_id"]);

  $subject = '=?UTF-8?B?'.base64_encode($m["subject"]).'?=';
  $header = Array(
    "From"                      => $m["from_email"],
    "Sender"                    => "ref-it@tu-ilmenau.de",
    "Cc"                        => $m["cc_email"],
    "To"                        => $m["to_email"],
    "Content-type"              => "text/plain; charset=UTF-8",
    "Content-Transfer-Encoding" => "base64",
    "X-Mailer"                  => "helfer.stura.tu-ilmenau.de/reminder",
    "Subject"                   => $subject,
    "Date"                      => date('r', time()),
  );
  $message = $m["message"]."\n\n--\nAutomatisch erzeugte Nachricht\nVerwaltung: https://helfer.stura.tu-ilmenau.de/reminder/index.php?tab=message.edit&message_id=".$e["message_id"];
  $message = chunk_split(base64_encode($message));

  $to = Array();
  foreach (["to_email","cc_email","bcc_email"] as $key) {
    if (trim($m[$key]) == "") continue;
    $tmp = $obj->parseAddressList($m[$key], 'example.org', FALSE);
    foreach ($tmp as $t) {
      $to[] = $t->mailbox."@".$t->host;
    }
  }

  $res = (true === $mail_object->send($to, $header, $message));

  if ($res) {
    recordSendMail($e["message_id"], $e["send_date"]);
  }
}

// vim:set filetype=php:

