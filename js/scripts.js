jQuery(document).ready(function($) {
	var URL = window.location.href;
	if(URL.indexOf('post.php') !== -1 && $('.post-month').length == 1 && $('.post-year').length == 1) {
		// Replace the 'add new' button with the current period
		var href = $('.page-title-action').attr('href') + '?period=' + $('.post-month').text() + '-' + $('.post-year').text();
		$('.page-title-action').attr('href', href);
	}
	if(URL.indexOf('post.php') !== -1 && $('.sidebar-post-category').length == 1) {
		// Replace the 'add new' button with the current period
		var href = $('.page-title-action').attr('href') + '?sidebar=' + $('.sidebar-post-category').text();
		$('.page-title-action').attr('href', href);
	}

	jQuery('body').on('click', '.init-wdeditor-ajax', function(event) {
		event.preventDefault();
		var anchor = jQuery(this);
		var method = anchor.attr('data-method');
		if(anchor.attr('data-confirm')) {
			var confirmText = anchor.attr('data-confirm');
			var confirmed = confirm(confirmText);
			if(!confirmed) {
				return false;
			}
		}
		if(method == 'wdeditor_ajax_update_category_description') {
			var description = tinymce.editors['category-description-editor'].getContent();
			var categoryID = anchor.attr('data-category');
			var data = {
				'action': method,
				'description': description,
				'category_id': categoryID
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
		if(method == 'wdeditor_ajax_delete_year') {
			var year = anchor.attr('data-year');
			var data = {
				'action': method,
				'year': year
			}
		}
		if(method == 'wdeditor_ajax_delete_post') {
			var post = anchor.attr('data-post');
			var data = {
				'action': method,
				'post': post
			}
		}
		if(method == 'wdeditor_ajax_hide_year' || method == 'wdeditor_ajax_show_year') {
			var year = anchor.attr('data-year');
			var data = {
				'action': method,
				'year': year
			}
		}
		if(method == 'wdeditor_ajax_hide_month' || method == 'wdeditor_ajax_show_month') {
			var month = anchor.attr('data-month');
			var data = {
				'action': method,
				'month': month
			}
		}
		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			alert(response);
			if(data.action == 'wdeditor_ajax_add_year' && response == 'The year has been added.') {
				window.location.href = window.location.href + '&focus=' + data.year;
			}			
			if(data.action == 'wdeditor_ajax_update_month_posts_order' || data.action == 'wdeditor_ajax_update_category_description') {
				WDEdtiorUpdateMonthLivePreview();
			}
			if(data.action == 'wdeditor_ajax_update_month_posts_order') {
				// Rename all posts with 'Viewer' in the title within table
				var c = 1;
				$('.wdeditor-sortable-posts tbody tr').each(function() {
					var title = $(this).find('.post-title').text();
					if(title.indexOf('Viewer') !== -1) {
						$(this).find('.post-title').text('Viewer ' + c);
					}
					c++;
				});
				jQuery('.update-posts-order-container').slideUp(function() {
					$(this).remove();
				});
			}
			if(data.action == 'wdeditor_ajax_delete_year') {
				anchor.closest('.diary-year').fadeOut(function() {
					$(this).remove();
				});
			}
			if(data.action == 'wdeditor_ajax_delete_post') {
				anchor.closest('tr').slideUp(function() {
					$(this).remove();
				});
			}
			if(data.action == 'wdeditor_ajax_hide_year') {
				anchor.attr('checked', true);
				anchor.closest('.diary-year').addClass('hidden-year');

				// Change input field to be able to show the year
				var html = anchor.closest('.diary-year').find('.hide-year').html();
				html = html.replaceAll('hide', 'show');
				anchor.closest('.diary-year').find('.hide-year').replaceWith(html);
			}
			if(data.action == 'wdeditor_ajax_show_year') {
				anchor.removeAttr('checked');
				anchor.closest('.diary-year').removeClass('hidden-year');

				// Change input field to be able to hide the field
				var html = anchor.closest('.diary-year').find('.show-year').html();
				html = html.replaceAll('show', 'hide');
				anchor.closest('.diary-year').find('.show-year').replaceWith(html);
			}
			if(data.action == 'wdeditor_ajax_hide_month') {
				anchor.attr('checked', true);
				anchor.closest('.diary-month').addClass('hidden-month');

				// Change input field to be able to show the month
				var html = anchor.closest('.hide-month').html();
				html = '<div class="show-month">' + html.replaceAll('hide', 'show') + '</div>';
				anchor.closest('.hide-month').replaceWith(html);
			}
			if(data.action == 'wdeditor_ajax_show_month') {
				anchor.removeAttr('checked');
				anchor.closest('.diary-month').removeClass('hidden-month');

				// Change input field to be able to hide the field
				var html = anchor.closest('.show-month').html();
				html = '<div class="hide-month">' + html.replaceAll('show', 'hide') + '</div>';
				anchor.closest('.show-month').replaceWith(html);
			}
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