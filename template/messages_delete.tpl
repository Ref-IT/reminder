<?php

$message = getMessageDetailsById($_REQUEST["message_id"]);
if ($message === false) die("invalid id");
requireGroup($message["group"]);

?>

<form method="POST" action="<?php echo $_SERVER["PHP_SELF"];?>" enctype="multipart/form-data" class="ajax">
<input type="hidden" name="id" value="<?php echo $message["id"];?>"/>
<input type="hidden" name="action" value="message.delete"/>
<input type="hidden" name="nonce" value="<?php echo htmlspecialchars($nonce);?>"/>

<div class="panel panel-default">
 <div class="panel-heading">
  <?php echo htmlspecialchars($message["subject"]); ?>
 </div>
 <div class="panel-body">

<div class="form-horizontal" role="form">

<?php

foreach ([
  "id" => "ID",
  "subject"    => "Betreff",
  "message"    => "Nachricht",
  "from_email" => "Absender (eMail)",
  "to_email"   => "Empfänger (eMail)",
  "cc_email"   => "Kopie-Empfänger (eMail)",
  "bcc_email"  => "Blindkopie-Empfänger (eMail)",
  "active"     => "Nachricht versenden/aktiv?",
  "group"      => "Gruppe",
  "dates"      => "Senden am",
  "created_by" => "erstellt durch",
  "created_at" => "erstellt am",
 ] as $key => $desc):

?>

  <div class="form-group">
    <label class="control-label col-sm-2"><?php echo htmlspecialchars($desc); ?></label>
    <div class="col-sm-10">
      <?php
        switch($key) {
          case"message":
?>         <div class="form-control kommentar"><?php echo implode("<br>",explode("\n",htmlspecialchars($message[$key]))); ?></div><?php
            break;
          case "active":
?>         <div class="form-control"><?php echo htmlspecialchars($message[$key] ? "ja" : "nein"); ?></div><?php
            break;
          case "dates":
?>         <div class="form-control"><?php echo htmlspecialchars($message[$key]); ?></div><?php
            break;
          default:
?>         <div class="form-control"><?php echo htmlspecialchars($message[$key]); ?></div><?php
        }
      ?>
    </div>
  </div>

<?php

endforeach;

?>

</div> <!-- form -->

 </div>
 <div class="panel-footer">
     <input type="submit" name="submit" value="Löschen" class="btn btn-danger"/>
     <input type="reset" name="reset" value="Abbrechen" onClick="self.close();" class="btn btn-default"/>
 </div>
</div>

</form> <!-- form -->

<?php

// vim:set filetype=php:
