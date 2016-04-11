<?php
header("Content-Type: text/html; charset=UTF-8");
?>
 <!DOCTYPE html>
<html lang="de">
 <head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>StuRa Erinnerungen</title>
  <link href="css/jquery-ui.min.css" rel="stylesheet" />
  <link href="css/reminder.css" rel="stylesheet" />
  <link href="css/bootstrap.min.css" rel="stylesheet" />
  <link href="css/bootstrap-select.min.css" rel="stylesheet"/>
  <link href="css/bootstrap-toggle.min.css" rel="stylesheet"/>
  <link rel="stylesheet" type="text/css" href="css/datatables.min.css"/>
  <link rel="stylesheet" href="css/font-awesome.min.css">
<!--  <link href="css/dataTables.bootstrap.min.css" rel="stylesheet" /> -->
  <link href="css/jquery.dataTables.min.css" rel="stylesheet" />
  <link href="css/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
  <script src="js/jquery-1.12.0.min.js"></script>
  <script src="js/jquery-ui.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/bootstrap-select.min.js"></script>
  <script src="js/i18n-bootstrap-select/defaults-de_DE.min.js"></script>
  <script src="js/bootstrap-toggle.min.js"></script>
  <script type="text/javascript" src="js/datatables.min.js"></script>
  <script type="text/javascript" src="js/reminder.js"></script>
  <script src="js/fileinput.min.js" type="text/javascript"></script>
  <script src="js/fileinput_locale_de.js"></script>
  <script src="js/jquery-ui.multidatespicker.js"></script>
 </head>
 <body>
 <div class="container">

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">
        <span class="hidden-xs">StuRa Erinnerungen (Reminder)</span>
        <span class="visible-xs-inline">StuRa Reminder</span>
      </a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav navbar-right">
        <li><a href="<?php echo $logoutUrl; ?>">Logout</a></li>
<!--
        <li><a href="?tab=person">Personen</a></li>
        <li><a href="?tab=gremium">Gremien und Rollen</a></li>
        <li><a href="?tab=gruppe">Gruppen</a></li>
        <li><a href="?tab=mailingliste">Mailinglisten</a></li>
        <li><a href="?tab=export">Export</a></li>
        <li><a href="?tab=help">Hilfe</a></li>
-->
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>


<!-- Modal -->
<div class="modal fade" id="waitDialog" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Bitte warten...</h4>
      </div>
      <div class="modal-body">
        <p>Bitte warten, die Daten werden verarbeitet. Dies kann einen Moment dauern.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>

  </div>
</div>

<script type="text/javascript">
function xpAjaxErrorHandler (jqXHR, textStatus, errorThrown) {
      $("#waitDialog").modal("hide");
      alert(textStatus + "\n" + errorThrown + "\n" + jqXHR.responseText);
};
$(function () {
  $( "form.ajax" ).submit(function (ev) {
    var action = $(this).attr("action");
    if ($(this).find("input[name=action]").length + $(this).find("select[name=action]").length == 0) { return true; }
    var data = new FormData(this);
    data.append("ajax", 1);
    $("#waitDialog").modal("show");
    $.ajax({
      url: action,
      data: data,
      cache: false,
      contentType: false,
      processData: false,
      type: "POST"
    })
    .success(function (values, status, req) {
       $("#waitDialog").modal("hide");
       if (typeof(values) == "string") {
         alert(values);
         return;
       }
       var txt;
       if (values.ret) {
         txt = "Die Daten wurden erfolgreich gespeichert.";
       } else {
         txt = "Die Daten konnten nicht gespeichert werden.";
       }
       if (values.msgs && values.msgs.length > 0) {
           txt = values.msgs.join("\n")+"\n"+txt;
       }
       alert(txt);
       if (values.ret && !values.target) {
        self.opener.location.reload();
        self.opener.focus();
        self.close();
       }
       if (values.ret && values.target) {
        self.location.href = values.target;
        self.opener.location.reload();
       }
     })
    .error(xpAjaxErrorHandler);
    return false;
   });
});
</script>
