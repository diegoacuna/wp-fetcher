(function( $ ) {
	'use strict';

	// simple cache using localStorage
	$.ajaxPrefilter(function (options, originalOptions, jqXHR) {
		if (options.cache) {
		    var success = originalOptions.success || $.noop,
		        url = originalOptions.url;

		    options.cache = false; //remove jQuery cache as we have our own localStorage
		    options.beforeSend = function () {
		    	var cached_url = localStorage.getItem(url + '_date');
		    	if (cached_url != null && ((new Date().getTime() - cached_url) / 1000) < 3600) {
		        if (localStorage.getItem(url)) {
		            success(localStorage.getItem(url));
		            return false;
		        }
		        return true;
	        }
		    };
		    options.success = function (data, textStatus) {
		        var responseData = JSON.stringify(data);
		        localStorage.setItem(url, responseData);
		        localStorage.setItem(url+'_date', new Date().getTime());
		        if ($.isFunction(success)) success(responseData); //call back to original ajax call
		    };
		}
	});

	$(function() {
		var container = $("div[data-load='posts']");
		var categories = container.data('categories');
		$.ajax({
			type: 'POST',
			url: WPFetcher.ajaxurl,
			data: {
				action : 'fetch_posts',
				categories: categories
			},
			cache: true,
			success: function( response ) {
				response = JSON.parse(response);
				if (response.status == '200') {
					var content = '';
					for(var i=0; i < response.content.length; i++) {
						if (i % 3 == 0)
							content += '<div class="clearfix">';
						content += '<div class="fetched-post">';
						content += '<h3><a href="' + response.content[i].link + '">' + response.content[i].title + '</a></h3>';
						if (response.content[i].avatar != null)
							content += '<div class="post-img-container"><img src="' + response.content[i].avatar + '"/></div>';
						content += '<p><i style="font-size:12px;">' + response.content[i].date + '</i><br>' + response.content[i].description + '</p>';
						content += '</div>';
						if (i % 3 == 2)
							content += '</div>';
					}
					if (container.length > 0)
						container.append(content);
				}
			}
		});
	});
})( jQuery );
