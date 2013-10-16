// Generator main process
(function() {
	
	// Useful aliases
	var log = ut.log;
	var data = {
		loadingState: null,
		result: {},
		storageMethod: (function() {
			// localStorage
			try {
				localStorage.setItem("smitroll.test", "test");
				localStorage.removeItem("smitroll.test");
				return "localStorage";
			}
			catch(e) {
				log.warn('The browser does not support localStorage ("' + e.message + '")');
			}
			// cookie
			if(ut.cookie.enabled()) {
                return "cookie";
            }
			// No storage available
			return null;
		})(),
		cfg: {
			errors: [],
			items: "soft"
		}
	};
	
	var requests = {
		generateBuild: function(o) {
			log.info("Calling generateBuildTeam web-service with data:");
            log.print(o);
			$.ajax({
				url: cfg.baseUrl + "ws/generateBuildTeam",
				data: o,
				dataType: "json",
                cache: false,
				success: function(json) {
					// Web-service returned an error
					if(json.errors && typeof json.errors === "object" && json.errors.length) {
						log.error("Errors were returned by generateBuild web-service:");
						for(var i = 0, iLength = json.errors.length ; i < iLength ; i++) {
							var err = json.errors[i];
							log.error("#" + (i + 1) + " [" + err.code + "] " + err.details);
						}
						return;
					}
					// Web-service returned a generation result
					if(json.success && json.data) {
						log.info("Retrieved generateBuild web-service response:");
						log.print(json.data);
						data.result = json.data;
						handlers.showResult();
						return;
					}
					log.warn("Unknown json response from generateBuild web-service");
				},
				error: function(jqXHR, textStatus, errorThrown) {
					log.error("Error while calling generateBuild web-service: status \"" + textStatus + "\": \"" + errorThrown +"\"");
				}
			});
		}
//        generateGod: function(o, team, player) {
//			log.info("Calling generateGod web-service with data:")
//            log.print(o);
//			$.ajax({
//				url: cfg.baseUrl + "ws/generateGod",
//				data: o,
//				dataType: "json",
//                cache: false,
//				success: function(json) {
//					// Web-service returned an error
//					if(json.errors && typeof json.errors === "object" && json.errors.length) {
//						log.error("Errors were returned by generateGod web-service:");
//						for(var i = 0, iLength = json.errors.length ; i < iLength ; i++) {
//							var err = json.errors[i];
//							log.error("#" + (i + 1) + " [" + err.code + "] " + err.details);
//						}
//						return;
//					}
//					// Web-service returned a generation result
//					if(json.success && json.data) {
//						log.info("Retrieved generateGod web-service response:");
//						log.print(json.data);
//                        // TODO
//						return;
//					}
//					log.warn("Unknown json response from generateGod web-service");
//				},
//				error: function(jqXHR, textStatus, errorThrown) {
//					log.error("Error while calling generateGod web-service: status \"" + textStatus + "\": \"" + errorThrown +"\"");
//				}
//			});
//        }
	};
	
	var load = {
		configuration: function() {
			var dataLoaded = handlers.storage.config.load();
            log.print(dataLoaded);
			if(dataLoaded !== null) {
				if(dataLoaded.items) {
                    handlers.radioList.items(dataLoaded.items);
                } 
                if(dataLoaded.teams) {
                    log.print("coin");
                    var members = dataLoaded.teams.split(',');
                    var input = null;
                    // Team 1
                    for(var i = 0; i < 5; ++i) {
                        input = document.querySelector("#team1player" + (i + 1));
                        input.value = members[i] || "";
                        log.print(input);
                        handlers.teamMembers.update(input);
                    }
                    // Team 2
                    for(i = 0; i < 5; ++i) {
                        input = document.querySelector("#team2player" + (i + 1));
                        input.value = members[5 + i] || "";
                        log.print(input);
                        handlers.teamMembers.update(input);
                    }
                }
				return;
			}
		}
	};
	
	var dom = {
		processButton: {
			update: function() {
				var cfg = data.cfg;
				cfg.errors = [];
                var players = [];
                var isDuplicate = false;
                $("ul#configurationList > li.teams input").each(function(i, elt) {
                    if(!(elt.className.match(/\bno\b/g) || elt.value === "")) {
                        var val = elt.value;
                        if(players.indexOf(val) !== -1) {
                            isDuplicate = true;
                            return false;
                        }
                        players.push(val);
                    }
                    return true;
                });
                if(isDuplicate) {
					cfg.errors.push("A FUCKER IS PRESENT MORE THAN ONCE");
					$("button#apocalypshitIsComing").attr("title", "You entered the same name multiple times, you retard.");
                }
                else if(!players.length) {
					cfg.errors.push("NO FUCKERS");
					$("button#apocalypshitIsComing").attr("title", "So you really want to play a 0 vs 0 game? How fascinating.");
                }
				if(cfg.errors.length !== 0) {
					$("button#apocalypshitIsComing").attr("disabled", "disabled");
					return;
				}
				data.cfg.errors = [];
				$("button#apocalypshitIsComing").attr("disabled", null).attr("title", "GO GO GO Fire in da owl!");
			}
		}
	};

	var handlers = {
        teamMembers: {
            update: function(e) {
				if (e.currentTarget) {
                    e = e.currentTarget;
                }
                var jElt = $(e);
                jElt[jElt.val() === "" ? "addClass" : "removeClass"]("no");
                dom.processButton.update();
				handlers.storage.config.save();
            }
        },
		radioList: {
			items: function(e) {
				var className = null;
				if (e.currentTarget) {
                    e = e.currentTarget.className;
                }
				if(e.match(/\bchecked\b/g)) {
                    return;
                }
				if(e.match(/\bsoft\b/g)) {
                    className = "soft";
                }
				else if(e.match(/\bhard\b/g)) {
                    className = "hard";
                } 
				else {
					log.error("handlers.radioList.items(): unknown radiobutton value");
					return;
				}
				$("ul#selectItemsLvl > li > a").each(function(i, elt) {
					var jElt = $(elt);
					jElt.children("div.radiobutton")[jElt.hasClass(className) ? "addClass" : "removeClass"]("checked");
					data.cfg.items = className;
				});
				handlers.storage.config.save();
			}
		},
		process: function() {
			var o = {};
            // Teams
			var teams = [];
            $("ul#configurationList > li.teams > div.details > div.team").each(function(i, elt) {
                var team = [];
                $(elt).find("input").each(function(i2, elt2) {
                    var val = elt2.value.replace(/[^a-z0-9]/gi, '');
                    if(val === "") {
                        return;
                    }
                    team.push(val);
                });
                teams.push(team.join(','));
            })
            o.teams = teams.join('-');
			// Random items pick
            o.build = data.cfg.items === "hard" ? 1 : 0;
			// Sending request
			requests.generateBuild(o);
		},
//        refreshGod: function(team, player) {
//            return function() {
//                requests.generateGod({
//                    gods: "all"
//                }, team, player);
//            };
//        },
		showResult: function() {
			var sectionResult = $("section#result");
			var divTeams = [
                $("section#result").find("div#resultTeam1 > div.data"),
                $("section#result").find("div#resultTeam2 > div.data")
            ];
            var result = data.result;
            var teams = result.teams;
            divTeams[0].empty();
            divTeams[1].empty();
            for(var i = 0, iLength = teams.length; i < iLength; ++i) {
                var team = teams[i];
                var j = 0;
                for(var memberName in team) {
                    var member = team[memberName];
                    var god = member.god;
                    var build = member.build;
                    // Computing items
                    var items = build.items;
                    var ulItems = $(document.createElement("ul"))
                        .attr("class", "items");
                    for(var k = 0, kLength = items.length; k < kLength; k++) {
                        var item = items[k];
                        ulItems.append($(document.createElement("li"))
                            .append($(document.createElement("img"))
                                .attr("src", item.picture)
                                .attr("alt", item.name))
                            .append($(document.createElement("div"))
                                .attr("class", "name")
                                .text(item.name)));
                    }
                    // Computing actives
                    var actives = build.actives;
                    var ulActives = $(document.createElement("ul"))
                        .attr("class", "actives");
                    for(k = 0, kLength = actives.length; k < kLength; k++) {
                        var active = actives[k];
                        ulActives.append($(document.createElement("li"))
                            .append($(document.createElement("img"))
                                .attr("src", active.picture)
                                .attr("alt", active.name))
                            .append($(document.createElement("div"))
                                .attr("class", "name")
                                .text(active.name)));
                    }
                    // Computing actives
                    var newPlayer = $(document.createElement("div"))
                        .attr("id", "resultTeam" + (i + 1) + "Player" + (j + 1))
                        .attr("class", "player")
                        .append($(document.createElement("div"))
                            .attr("class", "name")
                            .text(memberName))
                        .append($(document.createElement("div"))
                            .attr("class", "god")
                            .append($(document.createElement("div"))
                                .attr("class", "top")
//                                .append($(document.createElement("a"))
//                                    .attr("href", "javascript:void(0);")
//                                    .attr("title", "Refresh god for this player"))
//                                    .on("click", handlers.refreshGod(i, j))
                                .append($(document.createElement("img"))
                                    .attr("src", god.picture)
                                    .attr("alt", god.name)))
                            .append($(document.createElement("div"))
                                .attr("class", "name")
                                .text(god.name)))
                        .append($(document.createElement("div"))
                            .attr("class", "build")
                            .append(ulItems)
                            .append(ulActives))
                        .append($(document.createElement("div"))
                            .attr("class", "cost")
                            .text(build.cost + " g"));
                    // Finalizing
                    divTeams[i].append(newPlayer);
                    ++j;
                }
                // Displaying frames
                $("#resultTeam" + (i + 1))[j === 0 ? "hide" : "show"]();
            }
			// Permalink
            $("input#resultPermalink").val(result.permalink);
			// Displaying result sheet and scrolling to it
			sectionResult.show();
			$(document).scrollTop($("div#resultFrame").offset().top);
		},
		storage: {
			config: {
				save: function() {
					if(data.loadingState === "loading" || data.storageMethod === null) {
                        return;
                    }
					var toSave = {
                        teams: "",
						items: data.cfg.items
					};
                    var teams = [];
                    $("ul#configurationList > li.teams input").each(function(i, elt) {
                        teams.push(elt.value);
                    });
                    toSave.teams = teams.join(",");
					if(data.storageMethod === "localStorage") {
                        localStorage.smitrollTeamCfg = JSON.stringify(toSave);
                    }
					else {
						var expiration = new Date();
						expiration.setTime(expiration.getTime() + 7776000000);
						ut.cookie.save([{
								name: "smitroll-teamcfg-teams",
								value: JSON.stringify(toSave.teams),
								expires: expiration
							},{
								name: "smitroll-teamcfg-items",
								value: JSON.stringify(toSave.items),
								expires: expiration
						}]);
					} 
				},
				load: function() {
					var dataLoaded = {};
					try {
						if(data.storageMethod === "localStorage") {
							dataLoaded = localStorage.smitrollTeamCfg || null;
							if(dataLoaded !== null) {
                                dataLoaded = JSON.parse(dataLoaded);
                            }
						}
						else if(data.storageMethod === "cookie") {
							var cookieData = ut.cookie.load();
							dataLoaded.teams = JSON.parse(cookieData["smitroll-teamcfg-teams"]) || null;
							dataLoaded.items = JSON.parse(cookieData["smitroll-teamcfg-items"]) || null;
						}
						return dataLoaded;
					}
					catch(e) {
						log.error('handlers.storage.config.load(): Error occured while retrieving local data ("' + e.message + '"');
					}
					return null;
				}
			}
		}
	};
	
	$(document).on("ready", function() {
	
		// Bind handlers
		$("ul#selectItemsLvl > li > a").on("click", handlers.radioList.items);
		$("ul#configurationList > li.teams input").on("keypress", handlers.teamMembers.update)
            .on("keyup", handlers.teamMembers.update)
            .on("blur", handlers.teamMembers.update);
		$("button#apocalypshitIsComing").on("click", handlers.process);
        
        // Loading teams
        $("ul#configurationList > li.teams input").each(function(i, elt) {
            var jElt = $(elt);
            jElt[jElt.val() === "" ? "addClass" : "removeClass"]("no");
        });
		
        /*
		// Checking some stuff
		switch(data.storageMethod) {
			case "localStorage":
				$("div#mainContent > section.configuration > div.abstract").text("Your whole configuration will be saved on your computer (method: localStorage)");
				break;
			case "cookie":
				$("div#mainContent > section.configuration > div.abstract").text("Your whole configuration will be saved on your computer (method: cookie)");
				break;
			default:
				$("div#mainContent > section.configuration > div.abstract").addClass("error").text("Your browser is a piece of shit and can't support localStorage nor cookies: your configuration won't be saved.");
				break;
		}
        */
		
		// Load stuff
		data.loadingState = "loading";
		load.configuration();
		// data.loadingState = "result";
		data.loadingState = "complete";
	});
})();