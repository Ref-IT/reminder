<?php

$message = getMessageDetailsById($_REQUEST["message_id"]);
if ($message === false) die("invalid id");
requireGroup($message["group"]);

?>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST" enctype="multipart/form-data" class="ajax">
<input type="hidden" name="id" value="<?php echo $message["id"];?>"/>
<input type="hidden" name="action" value="message.update"/>
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
    <label for="<?php echo htmlspecialchars($key); ?>" class="control-label col-sm-2"><?php echo htmlspecialchars($desc); ?></label>
    <div class="col-sm-10">

      <?php
        switch($key) {
          case"message":
            $lineNum = count(explode("\n", $message[$key]))+5;
            if ($lineNum < 10) $lineNum = 10;
?>         <textarea class="form-control" rows="<?php echo $lineNum; ?>" name="<?php echo htmlspecialchars($key); ?>"><?php echo htmlspecialchars($message[$key]); ?></textarea><?php
            break;
          case"group":
          case"created_by":
          case"created_at":
?>          <div class="form-control"><?php echo htmlspecialchars($message[$key]); ?></div><?php
            break;
          case"active":
?>         <select name="<?php echo htmlspecialchars($key); ?>" size="1" class="selectpicker" data-width="fit">
              <option value="1" <?php  if ($message[$key]) echo "selected=\"selected\""; ?>>ja</option>
              <option value="0" <?php  if (!$message[$key]) echo "selected=\"selected\""; ?>>nein</option>
           </select><?php
            break;
          case "dates":
?>          <div id="choose-<?php echo htmlspecialchars($key);?>" class="box multidatepicker" data-mdp-alt-field="choose-<?php echo htmlspecialchars($key);?>-input"></div>
            <input type="text" id="choose-<?php echo htmlspecialchars($key);?>-input" class="form-control" name="<?php echo htmlspecialchars($key);?>" value="<?php echo htmlspecialchars($message[$key]); ?>">
            <input type="hidden" name="<?php echo htmlspecialchars($key);?>_old" value="<?php echo htmlspecialchars($message[$key]); ?>">
<?php
            break;
          case "id":
?>         <div class="form-control"><?php echo htmlspecialchars($message[$key]); ?></div><?php
            break;
          default:
?>         <input class="form-control" type="text" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($message[$key]); ?>"><?php
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
     <input type="submit" name="submit" value="Speichern" class="btn btn-primary"/>
     <input type="reset" name="reset" value="Abbrechen" onClick="self.close();" class="btn btn-default"/>
     <a href="?tab=message.delete&amp;message_id=<?php echo $message["id"];?>" class="btn btn-default pull-right">Löschen</a>
 </div>
</div>

</form>

<?php

// vim:set filetype=php:
