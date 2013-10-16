<?php
global $viewBag, $cfg;
?><!doctype html>
<html>
	<head>
		<title><?php echo $viewBag["title"]; ?></title>
		<link rel="shortcut icon" href="<?php echo $cfg["path"]["rel"]; ?>favicon.ico">
		<link rel="shortcut icon" type="image/x-icon" href="<?php echo $cfg["path"]["rel"]; ?>favicon.png">
<?php
foreach($viewBag["styles"] as $style){
?>
		<link rel="stylesheet" href="<?php echo $cfg["path"]["rel"]."Styles/$style.css" ?>" />
<?php
} ?>
		<script type="text/javascript">
var cfg = {
	"baseUrl": "<?php echo $cfg["path"]["rel"] ?>",
	"debug": <?php echo isset($cfg["debug"]) && $cfg["debug"] === true ? "true" : "false"; ?>
};
		</script>
<?php
foreach($viewBag["scripts"] as $script){
?>
		<script src="<?php echo $cfg["path"]["rel"]."Scripts/$script.js"; ?>"></script>
<?php
} ?>
		<meta charset="utf-8">
		<meta name="description" content="Beautiful chaotic troll games generator for SMITE">
		<meta name="keywords" content="smite, random, pick, build, dota, moba, smitroll, troll, ultimate, bravery">
		<meta name="author" content="yo mama, chorizorro">
	</head>
	<body>
		<section id="page">
			<header>
				<a href="<?php echo $cfg["path"]["rel"]; ?>" title="<?php echo $viewBag["project"]["name"]; ?>: This is definitely a cool banner">
					<img class="banner" src="<?php echo $cfg["path"]["rel"]; ?>Styles/Pics/banner.jpg" alt="SMITROLLOLOLOLOLOLOLOLOLOLOLOLOLOLOLOLOLOLOLOLOLOL" />
				</a>
				<div class="nav">
					<nav>
						<div class="separator">&nbsp;</div>
						<div>
							<a<?php if($viewBag["page"] === "generator") echo ' class="selected"' ?> href="<?php echo $cfg["path"]["rel"]; ?>" title="Go to the Trollolol Generator">Generator</a>
						</div>
						<div class="separator">&nbsp;</div>
						<div>
							<a<?php if($viewBag["page"] === "about" || $viewBag["page"] === "changelog") echo ' class="selected"' ?>  href="<?php echo $cfg["path"]["rel"]; ?>about" title="Learn more about that shit">About</a>
						</div>
						<div class="separator">&nbsp;</div>
					</nav>
				</div>
			</header>
			<div id="main">
				<div id="mainContent">
					<?php include(__DIR__."/../Contents/".$viewBag["content"]); ?>
				</div>
			</div>
			<footer>
				<div>
					<div class="logos">
						<a href="http://www.hirezstudios.com/hirezwp/" title="Hi-Rez official website" target="_blank">
							<img src="<?php echo $cfg["path"]["rel"]; ?>Styles/Pics/Logos/hirez.png" alt="Hi-Rez Studios logo" />
						</a>
						<a href="https://account.hirezstudios.com/smitegame/" title="SMITE official website" target="_blank">
							<img src="<?php echo $cfg["path"]["rel"]; ?>Styles/Pics/Logos/smite.png" alt="SMITE logo" />
						</a>
					</div>
					<a href="https://account.hirezstudios.com/smitegame/" title="SMITE official website" target="_blank">SMITE</a> content and materials are trademarks and copyrights of <a href="http://www.hirezstudios.com/hirezwp/" title="Hi-Rez official website" target="_blank">Hi-Rez Studios</a> or its licensors.<br>
					Copyright 2007-2012 <a href="http://www.hirezstudios.com/hirezwp/" title="Hi-Rez official website" target="_blank">Hi-Rez Studios</a>. All rights reserved.
				</div>
			</footer>
		</section>
	</body>
</html>