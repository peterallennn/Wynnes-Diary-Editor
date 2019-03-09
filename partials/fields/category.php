<div class="date-select-fields">
	<ul>
		<li>
			<input type="number" name="post_year" value="<?= $post_year ?>">
		</li>
		<li>
			<select name="post_month">
				<?php 
					for($m=1; $m<=12; ++$m) {
					    $name = date('F', mktime(0, 0, 0, $m, 1));
					
					    echo '<option value="' . $name . '" ' . ($name == $post_month ? 'selected' : '') . '>' . $name . '</option>';
					}
				?>
			</select>
		</li>
	</ul>
</div>
<p class="howto">
	Choose the year (between 1879 - 1931) and month to place this diary post in the correct time period. The system will automatically assign the post to the correct category.
</p>