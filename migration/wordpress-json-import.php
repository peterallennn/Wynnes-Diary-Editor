<?php
	$json_file = file_get_contents('data.json');
	$data = json_decode($json_file, true);

	foreach ($data as $year => $years) {
		
		echo '<h1>' . $year . '</h1>';

		foreach ($years as $month_name => $month) {

			echo '<h3>' . ucwords($month_name) . '</h3>';

			if(isset($month['placeholder'])) {
				echo '<p><i>Seems to be a placeholder with no content.</i></p>';
			} else {
				echo str_replace("<p>&nbsp;</p>", '', $month['description']);
			}

			echo '<h4>Errors</h4>';
			
			echo '<pre>';
			print_r($month['errors']);
			echo '</pre>';
		
			echo '<hr/>';
		}

	}
?>