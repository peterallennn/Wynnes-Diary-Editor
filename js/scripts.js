jQuery(document).ready(function($) {
	jQuery('body').on('click', '.init-wdeditor-ajax', function(event) {
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

		if(method == 'wdeditor_ajax_update_month_posts_order') {
			var posts = {};

			$('.wpeditor-posts tr').each(function() {
				if($(this).attr('data-post')) {
					var postID = $(this).attr('data-post');
					var order = $(this).index();

					posts[postID] = order;
				}
			});

			var data = {
				'action': method,
				'posts': posts
			};
		}

		if(method == 'wdeditor_ajax_add_year') {
			var year = jQuery('input[name="year"]').val();

			var data = {
				'action': method,
				'year': year
			}
		}

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			if(data.action == 'wdeditor_ajax_add_year' && response == 'The year has been added.') {
				window.location.href = window.location.href + '&focus=' + data.year;
			}			

			if(data.action == 'wdeditor_ajax_update_month_posts_order' || data.action == 'wdeditor_ajax_update_month_description') {
				WDEdtiorUpdateMonthLivePreview();
			}

			if(data.action == 'wdeditor_ajax_update_month_posts_order') {
				jQuery('.update-posts-order-container').slideUp(function() {
					jQuery(this).remove();
				});
			}

			alert(response);
		});
	});

	// Move 'back' link within post.php to the correct location within the DOM. 
	// See 'inc/post-editor.php' for more details on this bodged solution to a problem
	if(jQuery('.wdeditor-post-editor-back-link').length == 1) {
		jQuery('.wdeditor-post-editor-back-link').prependTo('.wrap').slideDown();
	}

	WDEditorSortMonthPosts();
	WDEditorPostEditor();
	WDEditorDiaryFocus();
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

	  		if(jQuery('.update-posts-order-container').length == 0) {
	  			// Display button to update order in the database
	  			jQuery('.wdeditor-sortable-posts').after('<div class="update-posts-order-container" style="display: none;"><a href="#" class="init-wdeditor-ajax" data-method="wdeditor_ajax_update_month_posts_order">Update Order</div>');
	  			jQuery('.update-posts-order-container').slideDown();
	  		}
	  	}
	});
}

function WDEditorPostEditor()
{
	// When on the post.php admin page, set the Diary navigation item to active
	var url = window.location.href;

	if(url.indexOf('post.php') != -1 || url.indexOf('post-new.php')) {
		jQuery('.toplevel_page_diary-editor').removeClass('wp-not-current-submenu').addClass('current')
	}
}

/**
 * Refreshes the iFrame containing the live page of a month
 */
function WDEdtiorUpdateMonthLivePreview()
{
	document.getElementById('live-preview').contentWindow.location.reload();
}

function WDEditorDiaryFocus()
{
	var url = new URL(document.location.href);
	var searchParams = new URLSearchParams(url.search);

	if(searchParams.get('focus')) {
		var focus = searchParams.get('focus');

		jQuery([document.documentElement, document.body]).animate({
	        scrollTop: (jQuery('div[data-year="' + focus + '"]').offset().top - 50)
	    }, 2000);

	    setTimeout(function() {
	    	jQuery('div[data-year="' + focus + '"]').addClass('focus');

	    	// Remove focus param from url
			var currentURL = document.location.href;
			var newURL = currentURL.replace('&focus=' + focus, '');
			history.replaceState({}, null, newURL);

	    	setTimeout(function() {
	    		jQuery('div[data-year="' + focus + '"]').removeClass('focus');

	    		
	    	}, 5000);
	    }, 1500);
	}
	
}