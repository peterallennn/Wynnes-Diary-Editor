<div class="diary-month-editor wrap">
	<div class="heading">
		<a href="/wp-admin/admin.php?page=diary-editor&focus=<?= ($sidebar ? 'Sidebar' : $year->name) ?>" class="wdeditor-back-to-link">Back to Diary</a>
		<h1 class="wp-heading-inline">
			<?php if($sidebar) : ?>
				Sidebar - <?= $category->name ?>
			<?php else : ?>
				<?= $category->name; ?>, <?= $year->name; ?>
			<?php endif; ?>	
		</h1>
		<?php if($sidebar) : ?>
			<a href="/wp-admin/post-new.php?sidebar=<?= $category->term_id ?>" class="page-title-action">Add New Post to <?= $category->name ?></a>
		<?php else : ?>
			<a href="/wp-admin/post-new.php?period=<?= $_GET['period'] ?>" class="page-title-action">Add New Post to <?= $category->name; ?>, <?= $year->name; ?></a>
		<?php endif; ?>
		<hr class="wp-header-end">
	</div>
	<div class="row">
		<div class="col-md-7">
			<table class="wpeditor-posts wp-list-table widefat fixed striped pages wdeditor-sortable-posts">
				<thead>
					<tr>
						<td class="order-post">Order</td>
						<td>Title</td>
						<td>Actions</td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($posts as $post): ?>
						<tr data-post="<?= $post->ID ?>">
							<td><span class="dashicons dashicons-move"></span></td>
							<td>
								<?php if(has_post_thumbnail($post->ID)) : ?>
									<img src="<?= get_the_post_thumbnail_url($post->ID) ?>" height="20" class="post-image">
								<?php endif; ?>
								<span class="post-title"><?= $post->post_title ?></span>
							</td>
							<td>
								<span class="edit"><a href="/wp-admin/post.php?post=<?= $post->ID ?>&action=edit">Edit</a> |</span>
								<span class="trash"><a href="#" class="delete-post init-wdeditor-ajax" data-method="wdeditor_ajax_delete_post" data-post="<?= $post->ID ?>" data-confirm="Are you sure you want to delete <?= $post->post_title ?>?">Delete</a> |</span>
								<span class="view"><a href="<?= get_permalink($post->ID) ?>">View Live</a></span>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
			<div class="live-preview-container">
				<?php
					if($sidebar) {
						$live_url = get_category_link($category->term_id);
						$live_preview = get_category_link($category->term_id) . '?admin_preview';
					} else {
						$live_url = '/the-diary/' . $year->name . '/' . date('M', strtotime($category->name));
						$live_preview = '/the-diary/' . $year->name . '/' . date('M', strtotime($category->name)) . '?admin_preview';
					}
				?>
				<a href="<?= $live_url; ?>" target="_BLANK">View Live <i>(opens in a new tab)</i></a>
				<iframe src="<?= $live_preview; ?>" frameborder="0" class="live-preview" id="live-preview"></iframe>
			</div>
		</div>
		<div class="description col-md-5">
			<?php if(!in_array($category->term_id, $hidden_months)) : ?>
				<div class="hide-month">
					<label><input type="checkbox" name="hide_month" class="init-wdeditor-ajax" data-method="wdeditor_ajax_hide_month" data-month="<?= $category->term_id ?>" autocomplete="off"> Hide <?= $category->name ?> from the public</label>
					<i>Note: Whilst logged in as an admin, the month will still be visible within the diary. This will only affect public users or in other words, users that are not logged in to the WordPress admin.</i>
				</div>
			<?php else: ?>
				<div class="show-month">
					<label><input type="checkbox" name="show_month" class="init-wdeditor-ajax" data-method="wdeditor_ajax_show_month" data-month="<?= $category->term_id ?>" autocomplete="off" checked> Hide <?= $category->name ?> from the public</label>
					<i>Note: Whilst logged in as an admin, the month will still be visible within the diary. This will only affect public users or in other words, users that are not logged in to the WordPress admin.</i>
				</div>
			<?php endif; ?>

			<h3>Edit the description for <?= $category->name ?></h3>
			<? wp_editor(apply_filters('the_content', $category->description), 'category-description-editor', ['textarea_name' => 'category_description', 'media_buttons' => false, 'wpautop' => false, 'quicktags' => false]) ?>
			<a href="#" class="update-category-description init-wdeditor-ajax button button-primary" data-category="<?= $category->term_id ?>" data-method="wdeditor_ajax_update_category_description">Save Description</a>
		</div>
	</div>
</div>