<?php
requireGroup("admin");
$emailToSend = listAllMail();

$mail_object = Mail::factory('smtp', array("debug" => false, "timeout" => 5));
$obj = new Mail_RFC822();


?>
<table class="table">
<tr><th>Nachricht</th><th>Datum</th><th>Betreff</th><th>An</th><th>Gesendet</th></tr>
<?php

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
  );
  $message = chunk_split(base64_encode($m["message"]));

  $to = Array();
  foreach (["to_email","cc_email","bcc_email"] as $key) {
    if (trim($m[$key]) == "") continue;
    $tmp = $obj->parseAddressList($m[$key], 'example.org', FALSE);
    foreach ($tmp as $t) {
      $to[] = $t->mailbox."@".$t->host;
    }
  }

#  $res = (true === $mail_object->send($to, $header, $message));

?><tr>
   <td><?php echo htmlspecialchars($e["message_id"]); ?></td>
   <td><?php echo htmlspecialchars($e["send_date"]); ?></td>
   <td><?php echo htmlspecialchars($m["subject"]); ?></td>
   <td><?php echo htmlspecialchars($m["to_email"]); ?></td>
   <td><?php echo htmlspecialchars($res ? "gesendet":"Fehler"); ?></td>
</tr>
<?php

}

?>
</table>
<?php

// vim:set filetype=php:

#function recordSendMail($msgId, $d) {
