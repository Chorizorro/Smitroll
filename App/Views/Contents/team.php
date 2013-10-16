<?php
	require_once(__DIR__."/../../Modules/DisplayHelper.php");
	global $cfg, $data;
	$isResult = isset($data["loadedBuilds"]);
?>
<?php if($isResult) { ?><script type="text/javascript">$(document).on("ready",function(){$(document).scrollTop($("div#resultFrame").offset().top);});</script><?php } ?>
<section class="configuration centralContent">
	<h1>Configure your team</h1>
	<div class="abstract">
		[BETA]<br>
        Generate builds for 2 teams of 5 trolls at once!
	</div>
	<ul id="configurationList"><li class="teams">
			<header>
                Teams
			</header>
			<div class="details">
				<div class="abstract">
					Type in the fuckers' nicknames for both teams, or left blank to ignore<br>
				</div>
                <div class="team"><label for="team1player1">
                        Team 1:
                    </label><input id="team1player1" name="team1player1" type="text" placeholder="Fucker 1" value="You" /><input id="team1player2" name="team1player2" class="no" type="text" placeholder="Fucker 2" value="" /><input id="team1player3" name="team1player3" class="no" type="text" placeholder="Fucker 3" value="" /><input id="team1player4" name="team1player4" class="no" type="text" placeholder="Fucker 4" value="" /><input id="team1player5" name="team1player5" class="no" type="text" placeholder="Fucker 5" value="" /> </div>
                <div class="team"><label for="team2player1">
                        Team 2:
                    </label><input id="team2player1" name="team2player1" class="no" type="text" placeholder="Fucker 1" value="" /><input id="team2player2" name="team2player2" class="no" type="text" placeholder="Fucker 2" value="" /><input id="team2player3" name="team2player3" class="no" type="text" placeholder="Fucker 3" value="" /><input id="team2player4" name="team2player4" class="no" type="text" placeholder="Fucker 4" value="" /><input id="team2player5" name="team2player5" class="no" type="text" placeholder="Fucker 5" value="" /></div>
			</div>
		</li><li class="items">
			<header>
                Randomization type
			</header>
			<div class="details">
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
	<button id="apocalypshitIsComing">Brace yourselves</button>
</section>
<div id="resultFrame">
	<section id="result" class="centralContent"<?php if($isResult) { ?> style="display: block;"<?php }?>>
        <?php
        for($i = 0, $iBuild = 0; $i < 2; ++$i) {
            $isTeam = $isResult && isset($data['loadedBuilds']['teams'][$i]);
            $teamNb = $i + 1;
            $playerNb = 0;
            ?><div id="resultTeam<?php echo $teamNb; ?>" class="team">
            <h2>Team <?php echo $teamNb; ?></h2>
            <div class="data">
                <?php if($isTeam) {
                    $team = $data['loadedBuilds']['teams'][$i];
                    foreach($team as $member) { 
                        ++$playerNb;
                        $build = $data['loadedBuilds']['build']['data'][$iBuild++]; ?>
                <div id="resultTeam<?php echo $teamNb; ?>Player<?php echo $playerNb; ?>" class="player"><div class="name">
                        <?php echo DisplayHelper::htmlentities($member); ?>
                    </div><div class="god">
                        <div class="top">
                            <?php // <a href="javascript:void(0);" title="Refresh god for this player"></a> ?>
                            <img src="<?php echo DisplayHelper::htmlentities($build['god']['picture']); ?>" alt="<?php echo DisplayHelper::htmlentities($build['god']['name']); ?>" />
                        </div>
                        <div class="name">
                            <?php echo DisplayHelper::htmlentities($build['god']['name']); ?>
                        </div>
                    </div><div class="build"><ul class="items"><?php 
                                $items = $build['build']['items'];
                                foreach($items as $item) { ?><li>
                                <img src="<?php echo DisplayHelper::htmlentities($item['picture']); ?>" alt="<?php echo DisplayHelper::htmlentities($item['name']); ?>" />
                                <div class="name"><?php echo DisplayHelper::htmlentities($item['name']); ?></div>
                            </li><?php } ?></ul><ul class="actives"><?php 
                                $actives = $build['build']['actives'];
                                foreach($actives as $active) { ?><li>
                                <img src="<?php echo DisplayHelper::htmlentities($active['picture']); ?>" alt="<?php echo DisplayHelper::htmlentities($active['name']); ?>" />
                                <div class="name"><?php echo DisplayHelper::htmlentities($active['name']); ?></div>
                            </li><?php } ?></ul>
                    </div><div class="cost"><?php echo DisplayHelper::htmlentities($build['build']['totalCost']); ?> g</div></div>
                    <?php }
                } ?>
            </div>
            <div class="vShadow">&nbsp;</div>
        </div>
        <?php } ?>
        <div class="share">
			<h2>SAVE &amp; SHARE</h2>
			<div class="abstract">Use this URL if you wanna save and/or share that shitty team build.</div>
			<input id="resultPermalink" type="text" value="<?php echo $isResult ? DisplayHelper::htmlentities($data['loadedBuilds']["permalink"]) : "Nothing to show here" ?>" readonly="">
		</div>
    </section>
</div>