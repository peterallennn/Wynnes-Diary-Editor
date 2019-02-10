<h1 class="wp-heading-inline">Diary</h1>

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
			<div class="diary-year display-year-months">
				<h3><?= $year->name ?></h3>
			
				<ul class="months">
					<?php foreach ($months as $month): ?>
						<li class="<?= ($month['post_count'] == 0 ? 'no-posts' : 'has-posts') ?>"><a href="<?= CURRENT_ADMIN_URL . '&period=' . strtolower($month['name']) . '-' . $year->name ?>"><?= $month['name'] ?> (<?= $month['post_count'] ?>)</a></li>
					<?php endforeach ?>
				</ul>
			</div>
		<?php endif; ?>
	<?php endforeach ?>
</div>