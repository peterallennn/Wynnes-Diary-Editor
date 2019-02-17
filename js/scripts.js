jQuery(document).ready(function($) {
	jQuery('.init-wdeditor-ajax').click(function(event) {
		event.preventDefault();

		var method = jQuery(this).attr('data-method');

		if(method == 'wdeditor_ajax_update_month_description') {
			var description = tinymce.editors['category-description-editor'].getContent();
			var monthID = jQuery(this).attr('data-month');

			var data = {
				'action': method,
				'description': description,
				'month_id': monthID
			};
		}

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			alert(response);

			WDEdtiorUpdateMonthLivePreview();
		});
	})
});

function WDEdtiorUpdateMonthLivePreview()
{
	console.log('test');

	document.getElementById('live-preview').contentWindow.location.reload();

}