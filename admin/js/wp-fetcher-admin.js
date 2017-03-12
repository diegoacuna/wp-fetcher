(function( $ ) {
	'use strict';

	$(function() {
		$('.test-url').click(function(e){
			e.preventDefault();
			var url = $('.url-to-fetch').val();
			if (url) {
				var to_fetch = '/wp-fetcher/fetch/?url=' + url; 
				$.get(to_fetch, function( data ) {
					if (data == 'XML')
				  	alert("URL fetched correctly. The format of data is XML");
				  else if (data == 'JSON')
				  	alert("URL fetched correctly. The format of data is JSON");
				  else if (data == 'UNK')
				  	alert("URL fetched correctly but the data is not in XML or JSON! Try with a different url");
				  else
				  	alert("Bad URL. Provide a url with data in XML or JSON format");
				}).fail(function() {
			    alert("Error fetching the URL. Check your URL and try again.");
			  });
			} else {
				alert("You need to specify an URL to fetch!");
			}
		});
	});

})( jQuery );
