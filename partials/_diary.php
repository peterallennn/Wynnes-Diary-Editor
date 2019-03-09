<div class="wrap">
	<div class="heading">
			<h1 class="wp-heading-inline">Diary</h1>
			<a href="#TB_inline?width=300&height=185&inlineId=add-year-dialog" class="add-new-year thickbox page-title-action">Add Year</a>
			<hr class="wp-header-end">
		</div>
	
	<div class="diary-years group">
		<?php foreach($years as $year) : ?>
			<?php
				$months = [];
	
				for($m=1; $m<=12; ++$m) {
				    $month = date('F', mktime(0, 0, 0, $m, 1));
				    $month_term = get_term_by('slug', $month . '-' . $year->name, 'category');
	
				    $months[] = [
				    	'name' => $month,
				    	'month_shorthand' => date('M', mktime(0, 0, 0, $m, 1)),
				    	'post_count' => ($month_term ? $month_term->count : 0),
				    	'id' => (!empty($month_term) ? $month_term->term_id : null)
				    ];
				}
			?>
			<?php if(!$year->parent) : // Exclude month sub categories ?>
				<div class="diary-year display-year-months" data-year="<?= $year->name ?>">
					<h3><?= $year->name ?> <a href="#" class="delete-year init-wdeditor-ajax" data-method="wdeditor_ajax_delete_year" data-year="<?= $year->term_id ?>" data-confirm="Are you sure you want to delete the year of <?= $year->name ?>? Doing so will also delete the month and posts within if any have been created."><span class="dashicons dashicons-no"></span></a></h3>
				
					<ul class="months">
						<?php foreach ($months as $month): ?>
							<li class="<?= ($month['post_count'] == 0 ? 'no-posts' : 'has-posts') ?>"><a href="<?= CURRENT_ADMIN_URL . '&period=' . strtolower($month['name']) . '-' . $year->name ?>"><?= $month['name'] ?> (<?= $month['post_count'] ?>)</a></li>
						<?php endforeach ?>
					</ul>
				</div>
			<?php endif; ?>
		<?php endforeach ?>
	</div>

	<div id="add-year-dialog" style="display: none;">
		<h1>Add Year</h1>

		<input type="number" name="year" placeholder="e.g. 1879">

		<p>Enter a year between 1879 - 1931.</p>

		<a href="#" class="button-primary init-wdeditor-ajax" data-method="wdeditor_ajax_add_year">Add Year</a>
	</div>
</div>