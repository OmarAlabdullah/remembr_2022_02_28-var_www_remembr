
(function ($) {
    $(document).ready(function () {
      if( $('meta[property="og:locale"]').attr('content') == 'nl_BE' ) {
        window._ruf_checks = 0;
	window._ruf_interval = window.setInterval(function () {
		if( $('img[src*="piggy-bank-and-coin.png"]').length >= 1 ) {
			window.clearInterval(window._ruf_interval);
			// hide the row with the piggybank
			$('img[src*="piggy-bank-and-coin.png"]').closest('div.rm-row').hide();
		
			// change the background from the first block
			$('div.contentview div.rm-row:first()').css({
			   'background-image': 'url(/images/bottom-arrow0.png), url(/images/margraten-resized.jpg)'
			});

			$($('div.contentview div.rm-row')[2]).css({
				'background-image': 'none',
				'border-bottom': '2px solid #fff'
			});
		}

		if( window._ruf_checks >= 15 ) {
			window.clearInterval(window._ruf_interval);
		}

		window._ruf_checks += 1;
	}, 500);
      }
    });
})(jQuery);
