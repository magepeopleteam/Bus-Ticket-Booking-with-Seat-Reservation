jQuery(document).ready(function($){

  jQuery( "#od_start, #on_start, #on_end, #j_date" ).datepicker({
    dateFormat: "yy-mm-dd",
    minDate:0
  });
  jQuery( "#od_end, #ja_date" ).datepicker({
    dateFormat: "yy-mm-dd"
    // minDate:0
  });


  }); 