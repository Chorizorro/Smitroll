<?php
	require_once(__DIR__."/../../Modules/DisplayHelper.php");
	global $cfg, $data;
	$isResult = isset($data["loadedBuild"]);
?>
<?php if($isResult) { ?><script type="text/javascript">$(document).on("ready",function(){$(document).scrollTop($("div#resultFrame").offset().top);});</script><?php } ?>
<!--
<section class="message centralContent">
    Since August the 14th, 2013, a change in the patch 0.1.1682 disables selecting items not suitable for your god. That means SMITROLL is now <strong>useless</strong>. However, the site will be maintained in the case Hi-Rez rolls back that shit.<br>
    If you think this change is just the stupidest thing they could come up with, do not hesitate to go on their <a href="//www.facebook.com/smitegame" target="_blank" title="Official SMITE Facebook Page">Facebook page</a>, <a href="//twitter.com/SmiteGame" target="_blank" title="Official SMITE Twitter page">Twitter</a> or <a href="//reddit.com/r/Smite/" target="_blank" title="Official SMITE Reddit page">Reddit</a> and tell them!
</section>
-->
<section class="message centralContent">
    Since August the 14th, 2013, SMITROLL has lot any interest for me and for the most of you. That's why this website will (sadly) get offline the 22th of October.<br>
    I'm looking forward to publish the website source code and content, just in case.
</section>
<section class="configuration centralContent">
	<h1>Configure your shit</h1>
	<div class="abstract">
		Your whole configuration will be saved on your computer (method: )
	</div>
	<ul id="configurationList"><li class="god">
			<header>
				<a href="javascript:void(0);" title="Click to enable/disable the Random God Pick">
					<div class="checkbox"></div>
					Random God Pick
				</a>
			</header>
			<div class="details">
				<div class="abstract">
					Select the gods you own in the following list.<br>
				</div>
				<div class="controls">
					<a id="selectAllGods" href="javascript:void(0);" title="Selects all the Gods">Select all</a>
					<a id="deselectAllGods" href="javascript:void(0);" title="Deselects all the Gods">Unselect all</a>
				</div>
				<ul id="availableGodsList"><?php
					foreach($data["godsList"] as $god) {
						?><li data-id="<?php echo DisplayHelper::htmlentities($god["id"]); ?>" data-name="<?php echo DisplayHelper::htmlentities($god["name"]); ?>" title="<?php echo DisplayHelper::htmlentities($god["name"] . ', ' . $god["title"]); ?>">
						<img src="<?php echo DisplayHelper::htmlentities($god["picture"]); ?>" alt="Picture of <?php echo DisplayHelper::htmlentities($god["name"]); ?>">
					</li><?php } ?></ul>
				<div id="chosenGods">
					Selected Gods:
					<span></span>
				</div>
			</div>
		</li><li class="skills">
			<header>
				<a href="javascript:void(0);" title="Click to enable/disable the Random Skills Pick">
					<div class="checkbox"></div>
					Random Skills Pick
				</a>
			</header>
			<div class="details">
				<!--<div class="abstract">
					Select the apocalypshit level for your skills build.<br>
					Consider that soft random is for pussies.
				</div>-->
				<ul id="selectSkillsLvl" class="picker">
					<li>
						<a href="javascript:void(0);" class="soft" title="At least one level of each skill before lvl 5, ult maxed whenever possible, then skills maxed in a random order (for pussies)">
							<div class="radiobutton checked"></div>
							Soft <span class="abstract">At least one level of each skill before lvl 5, ult maxed whenever possible, then skills maxed in a random order (for pussies)</span>
						</a>
					</li>
					<li>
						<a href="javascript:void(0);" class="hard" title="Full random shit">
							<div class="radiobutton"></div>
							Hard <span class="abstract">Full random shit</span>
						</a>
					</li>
				</ul>
			</div>
		</li><li class="items">
			<header>
				<a href="javascript:void(0);" title="Click to enable/disable the Random God Pick">
					<div class="checkbox"></div>
					Random Items Pick
				</a>
			</header>
			<div class="details">
				<!--<div class="abstract">
					Select the apocalypshit level for your items build.<br>
					Consider that soft random is for pussies, but that hard random will probably be painful.
				</div>-->
				<ul id="selectItemsLvl" class="picker">
					<li>
						<a href="javascript:void(0);" class="soft" title="Generating always 1 boot item and 1 CD reduction item (for pussies)">
							<div class="radiobutton checked"></div>
							Soft <span class="abstract">Generating always 1 boot item and avoid duplicates unique passives or stats overcap (for pussies)</span>
						</a>
					</li>
					<li>
						<a href="javascript:void(0);" class="hard" title="Full random shit (prepare your anus)">
							<div class="radiobutton"></div>
							Hard <span class="abstract">Full random shit (prepare your anus)</span>
						</a>
					</li>
				</ul>
			</div>
		</li></ul>
	<button id="apocalypshitIsComing">Brace yourself</button>
</section>
<div id="resultFrame">
	<?php
	$isGod = $isResult && isset($data["loadedBuild"]["god"]); $god = $isGod ? $data["loadedBuild"]["god"] : null;
	$isSkills = $isResult && isset($data["loadedBuild"]["skills"]); $skills = $isSkills ? $data["loadedBuild"]["skills"] : null;
	$isBuild = $isResult && isset($data["loadedBuild"]["build"]); $build = $isBuild ? $data["loadedBuild"]["build"] : null;
	?>
	<section id="result" class="centralContent"<?php if($isResult) { ?> style="display: block;"<?php }?>>
		<?php // <h1>Result</h1> ?>
		<div class="god">
			<h2>GOD</h2>
			<div class="content"<?php if(!$isGod) { ?> style="display: none;"<?php } ?>><?php
				if($isGod) { ?>
				<div id="resultGodName" class="name"><?php echo DisplayHelper::htmlentities($god["name"]); ?></div>
				<img id="resultGodImage" alt="<?php echo DisplayHelper::htmlentities($god["name"]); ?>" src="<?php echo DisplayHelper::htmlentities($god["picture"]); ?>" />
				<div id="resultGodNickname" class="nickname"><?php echo DisplayHelper::htmlentities($god["title"]); ?></div><?php } else { ?>
				<div id="resultGodName" class="name">God's name</div>
				<img id="resultGodImage" alt="No God selected" src="<?php echo DisplayHelper::htmlentities($cfg["path"]["rel"]); ?>Styles/Pics/Icons/Default/God.png" />
				<div id="resultGodNickname" class="nickname">God's nickname</div><?php } ?>
			</div>
			<div class="noContent"<?php if(!$isGod) { ?> style="display: block;"<?php } ?>>
				Do what the fuck you want.
			</div>
		</div>
		<div class="hShadow">&nbsp;</div>
		<div class="skills">
			<h2>SKILLS</h2>
			<div class="content"<?php if(!$isSkills) { ?> style="display: none;"<?php } ?>>
				<div id="resultSkillsAbstract">
					<?php echo $isSkills ? $skills["type"] === "soft" ? "Take 1 level of each skill before level 5, then: 4 > ".$skills["order"][0]." > ".$skills["order"][1]." > ".$skills["order"][3] : "Full random shitz!" : "Result abstract"; ?>
				</div>
				<table id="resultSkillsTable">
					<tr class="header">
						<th></th>
						<?php for($i = 1 ; $i <= 20 ; $i++) { ?>
						<th><?php echo $i; ?></th>
						<?php } ?>
					</tr>
					<?php if($isSkills) { ?>
					<tr class="firstSkill">
						<td>
							<?php if($isGod) {?>
							<img src="<?php echo DisplayHelper::htmlentities($god["skills"]["1"]["picture"]); ?>" alt="<?php echo DisplayHelper::htmlentities($god["skills"]["1"]["name"]) ;?>" />
							<?php } else { ?>
							<img src="<?php echo DisplayHelper::htmlentities($cfg["path"]["rel"]); ?>Styles/Pics/Icons/Default/Ability_1.jpg" alt="" />
							<?php } ?>
						</td>
						<?php for($i = 0 ; $i < 20 ; $i++) { ?>
						<td<?php if($skills["order"][$i] === "1") { ?> class="checked"<?php } ?>>&nbsp;</td>
						<?php } ?>
					</tr>
					<tr class="secondSkill">
						<td>
							<?php if($isGod) {?>
							<img src="<?php echo DisplayHelper::htmlentities($god["skills"]["2"]["picture"]); ?>" alt="<?php echo DisplayHelper::htmlentities($god["skills"]["2"]["name"]) ;?>" />
							<?php } else { ?>
							<img src="<?php echo DisplayHelper::htmlentities($cfg["path"]["rel"]); ?>Styles/Pics/Icons/Default/Ability_2.jpg" alt="" />
							<?php } ?>
						</td>
						<?php for($i = 0 ; $i < 20 ; $i++) { ?>
						<td<?php if($skills["order"][$i] === "2") { ?> class="checked"<?php } ?>>&nbsp;</td>
						<?php } ?>
					</tr>
					<tr class="thirdSkill">
						<td>
							<?php if($isGod) {?>
							<img src="<?php echo DisplayHelper::htmlentities($god["skills"]["3"]["picture"]); ?>" alt="<?php echo DisplayHelper::htmlentities($god["skills"]["3"]["name"]) ;?>" />
							<?php } else { ?>
							<img src="<?php echo DisplayHelper::htmlentities($cfg["path"]["rel"]); ?>Styles/Pics/Icons/Default/Ability_3.jpg" alt="" />
							<?php } ?>
						</td>
						<?php for($i = 0 ; $i < 20 ; $i++) { ?>
						<td<?php if($skills["order"][$i] === "3") { ?> class="checked"<?php } ?>>&nbsp;</td>
						<?php } ?>
					</tr>
					<tr class="ultimate">
						<td>
							<?php if($isGod) {?>
							<img src="<?php echo DisplayHelper::htmlentities($god["skills"]["u"]["picture"]); ?>" alt="<?php echo DisplayHelper::htmlentities($god["skills"]["u"]["name"]) ;?>" />
							<?php } else { ?>
							<img src="<?php echo DisplayHelper::htmlentities($cfg["path"]["rel"]); ?>Styles/Pics/Icons/Default/Ability_u.jpg" alt="" />
							<?php } ?>
						</td>
						<?php for($i = 0 ; $i < 20 ; $i++) { ?>
						<td<?php if($skills["order"][$i] === "u") { ?> class="checked"<?php } ?>>&nbsp;</td>
						<?php } ?>
					</tr>
					<?php } else { ?>
					<tr class="firstSkill">
						<td>
							<img src="<?php echo DisplayHelper::htmlentities($cfg["path"]["rel"]); ?>Styles/Pics/Icons/Default/Ability_1.jpg" alt="" />
						</td>
						<?php for($i = 0 ; $i < 20 ; $i++) { ?>
						<td>&nbsp;</td>
						<?php } ?>
					</tr>
					<tr class="secondSkill">
						<td>
							<img src="<?php echo DisplayHelper::htmlentities($cfg["path"]["rel"]); ?>Styles/Pics/Icons/Default/Ability_2.jpg" alt="" />
						</td>
						<?php for($i = 0 ; $i < 20 ; $i++) { ?>
						<td>&nbsp;</td>
						<?php } ?>
					</tr>
					<tr class="thirdSkill">
						<td>
							<img src="<?php echo DisplayHelper::htmlentities($cfg["path"]["rel"]); ?>Styles/Pics/Icons/Default/Ability_3.jpg" alt="" />
						</td>
						<?php for($i = 0 ; $i < 20 ; $i++) { ?>
						<td>&nbsp;</td>
						<?php } ?>
					</tr>
					<tr class="ultimate">
						<td>
							<img src="<?php echo DisplayHelper::htmlentities($cfg["path"]["rel"]); ?>Styles/Pics/Icons/Default/Ability_u.jpg" alt="" />
						</td>
						<?php for($i = 0 ; $i < 20 ; $i++) { ?>
						<td>&nbsp;</td>
						<?php } ?>
					</tr>
					<?php } ?>
				</table>
			</div>
			<div class="noContent"<?php if(!$isSkills) { ?> style="display: block;"<?php } ?>>
				Do what the fuck you want.
			</div>
		</div>
		<div class="vShadow">&nbsp;</div>
		<div class="items">
			<h2>ITEMS & ACTIVES</h2>
			<div class="content"<?php if(!$isBuild) { ?> style="display: none;"<?php } ?>>
				<?php if($isBuild) { ?>
				<ul id="resultItemsList"><?php for($i = 0 ; $i < 6 ; $i++) {
					$item = $build["items"][$i];
					?><li>
						<img src="<?php echo DisplayHelper::htmlentities($item["picture"]); ?>" alt="<?php echo DisplayHelper::htmlentities($item["name"]); ?>" />
						<h3><?php echo DisplayHelper::htmlentities($item["name"]); ?></h3>
						<div class="cost">
							<?php for($j = 0, $jLength = min(Array(count($item["prices"]), 3)); $j < $jLength; ++$j) { ?>
							<div class="r<?php echo $j; ?>"><?php echo $item["prices"][$j] ?></div>
							<?php }
							for($j; $j < 3; ++$j) { ?>
							<div class="r<?php echo $j; ?>">&nbsp;</div>
							<?php } ?>
						</div>
					</li><?php } ?></ul>
				<ul id="resultAbilitiesList"><?php for($i = 0; $i < 2; ++$i) {
					$active = $build["actives"][$i];
					?><li>
						<img src="<?php echo DisplayHelper::htmlentities($active["picture"]); ?>" alt="<?php echo DisplayHelper::htmlentities($active["name"]); ?>" />
						<h3><?php echo DisplayHelper::htmlentities($active["name"]); ?></h3>
						<div class="cost">
							<?php for($j = 0, $jLength = min(Array(count($active["prices"]), 3)); $j < $jLength; ++$j) { ?>
							<div class="r<?php echo $j; ?>"><?php echo $active["prices"][$j] ?></div>
							<?php }
							for($j; $j < 3; ++$j) { ?>
							<div class="r<?php echo $j; ?>">&nbsp;</div>
							<?php } ?>
						</div>
					</li><?php } ?></ul>
				<?php } else { ?>
				<ul id="resultItemsList"><?php for($i = 1 ; $i <= 6 ; $i++) { ?><li>
						<img src="<?php echo DisplayHelper::htmlentities($cfg["path"]["rel"]); ?>Styles/Pics/Icons/Default/Item.jpg" alt="Item <?php echo $i; ?>" />
						<h3>Item <?php echo $i; ?></h3>
						<div class="cost">
							<?php for($j = 0 ; $j < 3 ; $j++) { ?>
							<div class="r<?php echo $j; ?>">&nbsp;</div>
							<?php } ?>
						</div>
					</li><?php } ?></ul>
				<ul id="resultAbilitiesList"><?php for($i = 1 ; $i <= 2 ; $i++) { ?><li>
						<img src="<?php echo DisplayHelper::htmlentities($cfg["path"]["rel"]); ?>Styles/Pics/Icons/Default/Item.jpg" alt="Ability <?php echo $i; ?>" />
						<h3>Ability <?php echo $i; ?></h3>
						<div class="cost">
							<?php for($j = 0 ; $j < 3 ; $j++) { ?>
							<div class="r<?php echo $j; ?>">&nbsp;</div>
							<?php } ?>
						</div>
					</li><?php } ?></ul>
				<?php } ?>
				<div id="resultTotalBuildCost">Total cost: <?php if($isBuild) { echo $build["cost"]; } else { ?>0<?php } ?> gold</div>
			</div>
			<div class="noContent"<?php if(!$isBuild) { ?> style="display: block;"<?php } ?>>
				Do what the fuck you want.
			</div>
		</div>
		<div class="vShadow">&nbsp;</div>
		<div class="share">
			<h2>SAVE & SHARE</h2>
			<div class="abstract">Use this URL if you wanna save and/or share that shitty build.</div>
			<input id="resultPermalink" type="text" value="<?php echo $isResult ? DisplayHelper::htmlentities($data["loadedBuild"]["permalink"]) : "Nothing to show here" ?>" readonly />
		</div>
	</section>
</div>