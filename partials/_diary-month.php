<div class="diary-month-editor wrap">
	<div class="heading">
		<a href="/wp-admin/admin.php?page=diary-editor&focus=<?= $year->name ?>" class="wdeditor-back-to-link">Back to Diary</a>
		<h1 class="wp-heading-inline"><?= $month->name; ?>, <?= $year->name; ?></h1>
		<a href="/wp-admin/post-new.php?period=<?= $_GET['period'] ?>" class="page-title-action">Add New Post to <?= $month->name; ?>, <?= $year->name; ?></a>
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
					<?php foreach ($month_posts as $post): ?>
						<tr data-post="<?= $post->ID ?>">
							<td><span class="dashicons dashicons-move"></span></td>
							<td>
								<?php if(has_post_thumbnail($post->ID)) : ?>
									<img src="<?= get_the_post_thumbnail_url($post->ID) ?>" height="20" class="post-image">
								<?php endif; ?>

								<?= $post->post_title ?>
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
				<a href="/the-diary#<?= $year->name . ',' . date('M', strtotime($month->name)) ?>" target="_BLANK">View Live <i>(opens in a new tab)</i></a>

				<iframe src="/the-diary/?admin_preview#<?= $year->name . ',' . date('M', strtotime($month->name)) ?>" frameborder="0" class="live-preview" id="live-preview"></iframe>
			</div>
		</div>
		<div class="description col-md-5">
			<h3>Edit the description for <?= $month->name ?></h3>

			<? wp_editor(apply_filters('the_content', $month->description), 'category-description-editor', ['textarea_name' => 'category_description', 'media_buttons' => false, 'wpautop' => false, 'quicktags' => false]) ?>

			<a href="#" class="update-month-description init-wdeditor-ajax button button-primary" data-month="<?= $month->term_id ?>" data-method="wdeditor_ajax_update_month_description">Save Description</a>
		</div>
	</div>
</div>