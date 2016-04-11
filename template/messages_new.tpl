<?php

$mail = getUserMail();

?>

<form action="<?php echo $_SERVER["PHP_SELF"];?>" method="POST" enctype="multipart/form-data" class="ajax">
<input type="hidden" name="action" value="message.insert"/>
<input type="hidden" name="nonce" value="<?php echo htmlspecialchars($nonce);?>"/>

<div class="panel panel-default">
 <div class="panel-heading">
  Neue Nachricht
 </div>
 <div class="panel-body">

<div class="form-horizontal" role="form">

<?php

foreach ([
  "subject"    => "Betreff",
  "message"    => "Nachricht",
  "from_email" => "Absender (eMail)",
  "to_email"   => "Empfänger (eMail)",
  "cc_email"   => "Kopie-Empfänger (eMail)",
  "bcc_email"  => "Blindkopie-Empfänger (eMail)",
  "active"     => "Nachricht versenden/aktiv?",
  "group"      => "Gruppe",
  "dates"      => "Senden am",
 ] as $key => $desc):

?>

  <div class="form-group">
    <label for="<?php echo htmlspecialchars($key); ?>" class="control-label col-sm-3"><?php echo htmlspecialchars($desc); ?></label>
    <div class="col-sm-9">

      <?php
        switch($key) {
          case"message":
?>         <textarea rows="10" class="form-control" name="<?php echo htmlspecialchars($key); ?>"></textarea><?php
            break;
          case"group":
           $grps = getGroups();
?>         <select name="<?php echo htmlspecialchars($key); ?>" size="1" class="selectpicker" data-width="fit">
<?php
           foreach ($grps as $g):
?>
              <option value="<?php echo htmlspecialchars($g); ?>"><?php echo htmlspecialchars($g); ?></option>
<?php
           endforeach;
?>
           </select><?php
            break;
          case"active":
?>         <select name="active" size="1" class="selectpicker" data-width="fit">
              <option value="1" selected="selected">Ja</option>
              <option value="0" >Nein</option>
           </select><?php
            break;
          case "dates":
?>          <div id="choose-<?php echo htmlspecialchars($key);?>" class="box multidatepicker" data-mdp-alt-field="choose-<?php echo htmlspecialchars($key);?>-input"></div>
            <input type="text" id="choose-<?php echo htmlspecialchars($key);?>-input" class="form-control" name="<?php echo htmlspecialchars($key);?>">
<?php
            break;
          case "from_email":
?>         <input class="form-control" type="text" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($mail); ?>"><?php
            break;
          default:
?>         <input class="form-control" type="text" name="<?php echo htmlspecialchars($key); ?>" value=""><?php
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
 </div>
</div>

</form>

<?php

// vim:set filetype=php:
