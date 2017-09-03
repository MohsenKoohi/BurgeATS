<ul class="dash-ul" style="padding:10px">
	<?php
		foreach($counts as $c)
			if(isset($c['name']))
				echo "<li>".$c['name'].": ".$c['count']."</li>";
	?>
</ul>
