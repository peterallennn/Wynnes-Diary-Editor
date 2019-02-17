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
	  	group: 'simple_with_animation',
		  pullPlaceholder: false,

	  	onDrop: function  (item, container, _super) {
	    	var clonedItem = jQuery('<tr/>').css({height: 0});
	    	item.before(clonedItem);
	    	clonedItem.animate({'height': item.height()});

	    	item.animate(clonedItem.position(), function  () {
	      		clonedItem.detach();
	      		_super(item, container);
	    	});
	  	},

	  	// set $item relative to cursor position
	  onDragStart: function (item, container, _super) {
	    var offset = item.offset(),
	        pointer = container.rootGroup.pointer;

	    adjustment = {
	      left: pointer.left - offset.left,
	      top: pointer.top - offset.top
	    };

	    _super(item, container);
	  },
	  onDrag: function (item, position) {
	    item.css({
	      left: position.left - adjustment.left,
	      top: position.top - adjustment.top
	    });
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