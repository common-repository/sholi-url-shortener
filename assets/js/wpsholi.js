(function($, window, undefined) {

	$(".copy_sholi").mouseout(function() {
		$('.wpsholi_tooltiptext').html("Click to Copy");
	});

	$('body').on('click', '.copy_sholi', function(event) {
		event.preventDefault();
		$url = $(this).find('.copy_sholi_link').html();
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val($url).select();
		document.execCommand("copy");
		$temp.remove();
		$(this).find('.wpsholi_tooltiptext').html("Copied: " + $url);
	});

	$('body').on('click', '.generate_sholi', function(event) {
		event.preventDefault();
		$wpsholi_generate_button = $(this);
		let wpsholi_post_id = $(this).attr('data-post_id');
		if (!wpsholi_post_id) {
			$('.generate_sholi').addClass('generate_sholi_disable');
		}
		$.ajax({
			url: wpsholiJS.ajaxurl,
			data: {
				'action': 'generate_wpsholi_url_via_ajax',
				'post_id': wpsholi_post_id
			},
			method: 'POST',
			//Post method
			beforeSend: function() {
				$('.generate_sholi').addClass('generate_sholi_disable');
			},
			success: function(response) {
				var data = JSON.parse(response);
				if (data.status) {
					$main_container = $wpsholi_generate_button.parent().parent();
					$main_container.html('').html(data.sholi_link_html)
				}
			},
			error: function(error) {
				$('.generate_sholi').removeClass('generate_sholi_disable');
			},
			complete: function() {
				$('.generate_sholi').removeClass('generate_sholi_disable');
			}
		})
	});
	
}(jQuery, window));