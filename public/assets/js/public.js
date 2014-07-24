(function ( $ ) {
	"use strict";

	$(function () {

		//Make sure that if we select "No subscription" we deselect every other option and vise versa
		$('.stmc-input-wrapper input[type="checkbox"]').click(function(e){
			//none clicked and is not previously checked. Toggle our custom modal
			if($(this).attr('id') == 'none' && $(this).prop('checked') == true){
				e.preventDefault();
				$('.cd-popup').toggleClass('is-visible');
			}else{ //another is checked, just make sure none isn't checked anymore!
				if($(this).is(':checked')){
					$('.stmc-input-wrapper input#none').attr('checked', false);
				}
			}
		});
		
		//close popup
		$('.cd-popup').on('click', function(event){
			event.preventDefault();
			if($(event.target).is('.yes')){
				$('.stmc-input-wrapper input#none').attr('checked', true);
				$('.stmc-input-wrapper input').not('#none').each(function(){
					$(this).attr('checked', false);
				});
			}
			$(this).removeClass('is-visible');
		});
		//close popup when clicking the esc keyboard button
		$(document).keyup(function(event){
	    	if(event.which=='27'){
	    		$('.cd-popup').removeClass('is-visible');
		    }
	    });

		

	});

}(jQuery));