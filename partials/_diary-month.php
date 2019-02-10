<div class="diary-month-editor wrap">
	<div class="heading">
		<h1 class="wp-heading-inline"><?= $month->name; ?>, <?= $year->name; ?></h1>
		<a href="/wp-admin/post-new.php?period=<?= $_GET['period'] ?>" class="page-title-action">Add New Post to <?= $month->name; ?>, <?= $year->name; ?></a>
		<hr class="wp-header-end">
	</div>
	
	<div class="row">
		<div class="col-md-7">
			<table class="wpeditor-posts wp-list-table widefat fixed striped pages">
				<thead>
					<tr>
						<td class="order-post">Order</td>
						<td>Title</td>
						<td>Actions</td>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($month_posts as $post): ?>
						<tr>
							<td><span class="dashicons dashicons-sort"></span></td>
							<td><?= $post->post_title ?></td>
							<td>
								<span class="edit"><a href=/wp-admin/post.php?post=<?= $post->ID ?>&action=edit">Edit</a> |</span>
								<span class="trash"><a href="#" class="delete-post" data-id="<?= $post->ID ?>">Trash</a> |</span>
								<span class="view"><a href="<?= get_permalink($post->ID) ?>">View Live</a></span>
							</td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>

			<div class="live-preview-container">
				<iframe src="/the-diary/?admin_preview#<?= $year->name . ',' . date('M', strtotime($month->name)) ?>" frameborder="0" class="live-preview"></iframe>
			</div>
		</div>
		<div class="col-md-5">
			<h3>Edit the description for <?= $month->name ?></h3>

			<? wp_editor($month->description, 'category-description-editor', ['textarea_name' => 'category_description', 'media_buttons' => false]) ?>

			<a href="#" class="update-month-description button button-primary">Save Description</a>
		</div>
	</div>
</div>