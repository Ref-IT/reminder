$(function() {
  $('.datepicker').datepicker({ dateFormat: 'yy-mm-dd' });
  $('.multidatepicker').each(function (index, value) {
    var cfg = { dateFormat: "yy-mm-dd" };
    var af = $(value).data("mdpAltField");
    var val;
    cfg.numberOfMonths = [4,3];
    if (af) {
      cfg.altField = '#'+af;
      val = $('#'+af).val();
      if (val) {
        cfg.addDates = val.split(', ');
      }
    }
    $(value).multiDatesPicker(cfg);
  });
});
