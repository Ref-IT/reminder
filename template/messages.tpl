<?php

$metadata = [
#  "id" => "ID",
  "name" => "Betreff",
  "to_email" => "Empfänger",
  "cc_email" => "Empfänger (CC)",
  "bcc_email" => "Empfänger (BCC)",
  "active" => "<small>aktiv</small>",
 ];

?>

<div class="panel panel-default">
 <div class="panel-heading">Filter <span class="visible-xs-inline">: Nachrichten anzeigen</span></div>
 <div class="panel-body">
  <div class="hidden-xs col-sm-4">
    Nachrichten anzeigen:
  </div>
  <div class="col-xs-12 col-sm-8">
   <select class="selectpicker tablefilter" data-column="active:name">
    <option value="1">Nur aktive</option>
    <option value="0">Nur inaktive</option>
    <option value="" selected>Alle (aktiv)</option>
   </select>
  </div>
 </div> <!-- panel-body -->
</div> <!-- panel -->

<?php
 $obj = "message";
 $obj_editable = true;
 $obj_smallpageinate = false;
 $obj_selectable = false;
 require dirname(__FILE__)."/admin_table.tpl";

// vim: set filetype=php:
