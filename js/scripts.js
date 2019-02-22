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
	});

	WDEditorSortMonthPosts();
});

/**
 * Sorting functionality for ordering posts within a month
 */
function WDEditorSortMonthPosts()
{
	jQuery('.wdeditor-sortable-posts').sortable({
	  	containerSelector: 'table',
	  	itemPath: '> tbody',
	  	itemSelector: 'tr',
	  	placeholder: '<tr class="placeholder"><td colspan="2"></tr>',
	  	handle: 'span.dashicons-move',
	  	onDrop: function(item, container, _super, event) {
	  		item.removeClass(container.group.options.draggedClass).removeAttr("style");
  			jQuery("body").removeClass(container.group.options.bodyClass);

	  		if(jQuery('.update-posts-order').length == 0) {
	  			// Display button to update order in the database
	  			jQuery('.wdeditor-sortable-posts').after('<div class="update-posts-order-container" style="display: none;"><a href="#" class="update-posts-order">Update Order</div>');
	  			jQuery('.update-posts-order-container').slideDown();
	  		}
	  	}
	});
}

/**
 * Refreshes the iFrame containing the live page of a month
 */
function WDEdtiorUpdateMonthLivePreview()
{
	document.getElementById('live-preview').contentWindow.location.reload();
}