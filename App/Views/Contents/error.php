<?php
global $viewBag;
?>

<div class="centralContent">
	<div id="HTTPShatItself">
		<h1>Fail <?php echo $viewBag["error"]["code"] ?></h1>
		<div class="description">
			<?php echo $viewBag["error"]["message"] ?>
		</div>
	</div>
</div>
