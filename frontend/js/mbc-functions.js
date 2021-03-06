 jQuery(function() {
	  var startdate;
	  var enddate;
	  var mindate;
	  var numberofmonths;
	  if (mbc_functions_vars.startdate != undefined) {
		  startdate = new Date(mbc_functions_vars.startdate);
		  enddate = startdate;
		  mindate = startdate;
		  numberofmonths = 1;
	  } else {
		  startdate = "+1d";
		  enddate = "+2d"; 
		  mindate = 0;
		  numberofmonths = 1;
	  }

	  if(jQuery(window).width() <= 600){
		  numberofmonths = 1;
	  } 
	jQuery( "#mbc-from" ).datepicker({
      defaultDate: startdate,
      changeMonth: true,
	  changeYear: true,
	  minDate: mindate,
	  showCurrentAtPos: 0,
      numberOfMonths: numberofmonths,
	  dateFormat: mbc_functions_vars.dateformat,
	  firstDay: mbc_functions_vars.firstday,
      onClose: function( selectedDate ) {
        jQuery( "#mbc-to" ).datepicker( "option", "minDate", selectedDate );
		window.setTimeout(function(){
	    	jQuery( "#mbc-to" ).focus();
	    }, 0);
      }
    });
    jQuery( "#mbc-to" ).datepicker({
      defaultDate: enddate,
      changeMonth: true,
	  changeYear: true,
	  minDate: mindate,
	  showCurrentAtPos: 0,
      numberOfMonths: numberofmonths,
	  dateFormat: mbc_functions_vars.dateformat,
	  firstDay: mbc_functions_vars.firstday,
      onClose: function( selectedDate ) {
        jQuery( "#mbc-from" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
  });