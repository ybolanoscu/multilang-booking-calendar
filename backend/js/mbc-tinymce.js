(function() {
    tinymce.PluginManager.add('mbc_booking_button', function( editor, url ) {
        editor.addButton( 'mbc_booking_button', {
            title: 'Multilang Booking Calendar',
            type: 'menubutton',
            icon: 'icon dashicons-calendar-alt',
            menu: [
                {
                    text: editor.getLang('multilang-booking-calendar.bookingform'),
                    value: '[mbc-bookingform]',
                    onclick:  function() {
						editor.windowManager.open( {
							title: editor.getLang('multilang-booking-calendar.bookingform'),
							width: 600,
							height: 100,
							body: [
								{
									type: 'checkbox',
									name: 'hideotherBox',
								    checked: false,
								    text: editor.getLang('multilang-booking-calendar.hideother')
								},
								{
									type: 'checkbox',
									name: 'hidetooshortBox',
								    checked: false,
								    text: editor.getLang('multilang-booking-calendar.hidetooshort')
								}
							],
							onsubmit: function( e ) {
								var hideother = "";
								if(e.data.hideotherBox){
									hideother = ' hide_other="1"';
								}
								var hidetooshort = "";
								if(e.data.hidetooshortBox){
									hidetooshort = ' hide_tooshort="1"';
								}
								editor.insertContent( '[mbc-bookingform'+ hideother + hidetooshort + ']');
							}
						});
					}
				},
                {
                    text: editor.getLang('multilang-booking-calendar.calendaroverview'),
                    value: '[mbc-overview]',
                    onclick: function() {
                        editor.insertContent(this.value());
                    }
                },
                {
                    text: editor.getLang('multilang-booking-calendar.singlecalendar'),
                    value: '[mbc-overview]',
                    onclick:  function() {
						editor.windowManager.open( {
							title: editor.getLang('multilang-booking-calendar.addsingle'),
							width: 400,
							height: 100,
							body: [
								{
									type: 'listbox',
									name: 'calendar',
									label: editor.getLang('multilang-booking-calendar.calendar'),
									'values': mbc_tinymce_calendars
								},
								{
									type: 'checkbox',
									name: 'legendBox',
								    checked: true,
								    text: editor.getLang('multilang-booking-calendar.legend')
								}
							],
							onsubmit: function( e ) {
								var legend = "";
								if(e.data.legendBox){
									legend = ' legend="1"';
								}
								editor.insertContent( '[mbc-single calendar="' + e.data.calendar + '"'+ legend + ']');
							}
						});
					}
				}	
           ]
        });
    });
})();