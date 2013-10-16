// Generator main process
(function() {
	
	// Useful aliases
	var log = ut.log;
	var data = {
		loadingState: null,
		result: {
			god: null,
			skills: null,
			items: null,
			url: null
		},
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
			if(ut.cookie.enabled()) return "cookie";
			// No storage available
			return null;
		})(),
		cfg: {
			errors: [],
			toCompute: [],
			gods: [],
			skills: "soft",
			items: "soft"
		}
	};
	
	var requests = {
		generateBuild: function(o) {
			log.info("Calling generateBuild web-service with data:");
			log.print(o);
			$.ajax({
				url: cfg.baseUrl + "ws/generateBuild",
				data: o,
				dataType: "json",
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
	};
	
	var load = {
		configuration: function() {
			var dataLoaded = handlers.storage.config.load();
			if(dataLoaded !== null) {
				// toCompute
				if(dataLoaded.toCompute) {
					for(var i = 0, iLength = dataLoaded.toCompute.length ; i < iLength ; i++) {
						handlers.views.switchFor(dataLoaded.toCompute[i])
					}
				}
				if(dataLoaded.skills) handlers.radioList.skills(dataLoaded.skills);
				if(dataLoaded.items) handlers.radioList.items(dataLoaded.items);
				load.godList(dataLoaded.gods || null);
				return;
			}
			load.godList();
		},
		godList: function(godList) {
			var list = $("ul#availableGodsList"), takeGodList = typeof godList == "object" && godList instanceof Array && godList.length > 0;
			list.children("li").each(function(i, elt) {
				var jElt = $(elt);
				var godId = parseInt(jElt.attr("data-id"));
				jElt.on("click", handlers.pickGod.switchOne);
				if(!takeGodList || godList.indexOf(godId) !== -1) {
					jElt.attr("class", "selected");
					data.cfg.gods.push(godId);
				}
			});
			dom.godList.showListString();
			dom.processButton.update();
		}
	};
	
	var dom = {
		godList: {
			switchSelect: function(id, selected) {
				var li = $("ul#availableGodsList > li[data-id=" + id + "]");
				if(typeof selected !== "boolean") selected = li.hasClass("selected");
				li[selected ? "addClass" : "removeClass"]("selected");
				return selected;
			},
			showListString: function() {
				var span = $("div#chosenGods > span"), gods = data.cfg.gods;
				if(gods.length < 1) {
					span.addClass("error").text("How do you want me to randomize anything if you don't pick at least one god?... Moron.");
					return;
				}
				var list = $("ul#availableGodsList > li"), strGods = "";
				for(var i = 0, iLength = gods.length ; i < iLength ; i++)
					strGods += ", " + list.filter("[data-id=" + gods[i] + "]").attr("data-name");
				span.removeClass("error").text(strGods);
			}
		},
		processButton: {
			update: function() {
				var cfg = data.cfg;
				cfg.errors = [];
				if(cfg.toCompute.length < 1) {
					cfg.errors.push("NOTHING TO DO");
					$("button#apocalypshitIsComing").attr("title", "You have to select at least one random function, retard.");
				}
				else if(cfg.toCompute.indexOf("god") !== -1 && cfg.gods.length < 1) {
					cfg.errors.push("GODS: EMPTY");
					$("button#apocalypshitIsComing").attr("title", "You want me to randomize god pick on 0 gods? Are you retarded or something?");
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
		
		views: {
			god: false,
			skills: false,
			items: false,
			switchFor: function(v) {
				// Security checks
				if(v !== "god" && v !== "skills" && v !== "items") {
					log.error("handlers.views.switchView(): v must be a string containing a valid view name.");
					return;
				}
				var view = !handlers.views[v];
				data.result[v] = handlers.views[v] = view;
				$("#configurationList > li." + v + " > div.details")[view ? "show" : "hide"]();
				$("#configurationList > li." + v + " > header > a > div.checkbox")[view ? "addClass" : "removeClass"]("checked");
				if(view) data.cfg.toCompute.push(v);
				else data.cfg.toCompute.splice(data.cfg.toCompute.indexOf(v), 1);
				dom.processButton.update();
				handlers.storage.config.save();
			}
		},
		pickGod: {
			selectAll: function() {
				var s = dom.godList.switchSelect;
				data.cfg.gods = [];
				$("ul#availableGodsList > li").each(function(i, elt) {
					var id = parseInt(elt.getAttribute("data-id"));
					data.cfg.gods.push(id); // Data
					s(id, true); // DOM Image
				});
				dom.godList.showListString();
				dom.processButton.update();
				handlers.storage.config.save();
			},
			deselectAll: function() {
				var s = dom.godList.switchSelect;
				var gods = data.cfg.gods; // Data
				log.print(gods);
				for(var i = 0, iLength = gods.length ; i < iLength ; i++)
					s(gods[i], false); // DOM Image
				data.cfg.gods = [];
				dom.godList.showListString();
				dom.processButton.update();
				handlers.storage.config.save();
			},
			switchOne: function(e) {
				var t = $(e.currentTarget), id = parseInt(t.attr("data-id"), 10);
				var switchTo = !t.hasClass("selected");
				var i = data.cfg.gods.indexOf(id);
				if(switchTo) {
					if(i !== -1) log.warn("handlers.pickGod.switchOne(): God #" + id + " is already in data.cfg.gods array");
					else data.cfg.gods.push(id);
				}
				else {
					if(i === -1) log.warn("handlers.pickGod.switchOne(): Can't remove God #" + id + " from selection: this god is not in data.cfg.gods array");
					else data.cfg.gods.splice(i, 1);
				}
				dom.godList.switchSelect(id, switchTo);
				dom.godList.showListString();
				dom.processButton.update();
				handlers.storage.config.save();
			}
		},
		radioList: {
			skills: function(e) {
				var className = null;
				if (e.currentTarget) e = e.currentTarget.className;
				if(e.match(/\bchecked\b/g)) return;
				if(e.match(/\bsoft\b/g)) className = "soft";
				else if(e.match(/\bhard\b/g)) className = "hard";
				else {
					log.error("handlers.radioList.skills(): unknown radiobutton value");
					return;
				}
				$("ul#selectSkillsLvl > li > a").each(function(i, elt) {
					var jElt = $(elt);
					jElt.children("div.radiobutton")[jElt.hasClass(className) ? "addClass" : "removeClass"]("checked");
					data.cfg.skills = className;
				});
				handlers.storage.config.save();
			},
			items: function(e) {
				var className = null;
				if (e.currentTarget) e = e.currentTarget.className;
				if(e.match(/\bchecked\b/g)) return;
				if(e.match(/\bsoft\b/g)) className = "soft";
				else if(e.match(/\bhard\b/g)) className = "hard";
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
			// Random god pick
			if(data.cfg.toCompute.indexOf("god") !== -1) {
				var gods = data.cfg.gods;
				o.gods = gods.length === $("ul#availableGodsList > li").length ? "all" : data.cfg.gods.join(',');
			}
			// Random skills pick
			if(data.cfg.toCompute.indexOf("skills") !== -1)
				o.skills = data.cfg.skills === "hard" ? 1 : 0;
			// Random items pick
			if(data.cfg.toCompute.indexOf("items") !== -1)
				o.build = data.cfg.items === "hard" ? 1 : 0;
			// Sending request
			requests.generateBuild(o);
		},
		showResult: function() {
			var sectionResult = $("section#result");
			var divGod = sectionResult.children("div.god");
			var divGodContent = divGod.children("div.content");
			var divGodNoContent = divGod.children("div.noContent");
			var divSkills = sectionResult.children("div.skills");
			var divSkillsContent = divSkills.children("div.content");
			var divSkillsNoContent = divSkills.children("div.noContent");
			var divItems = sectionResult.children("div.items");
			var divItemsContent = divItems.children("div.content");
			var divItemsNoContent = divItems.children("div.noContent");
			var result = data.result;
			var isGod = typeof result.god !== "undefined" && result.god;
			var isSkills = typeof result.skills !== "undefined" && result.skills;
			var isBuild = typeof result.build !== "undefined" && result.build;
			// Displaying god
			var god = null;
			if(isGod) {
				god = result.god;
				divGodContent.children("div#resultGodName").text(god.name);
				divGodContent.children("img#resultGodImage")
					.attr("src", god.picture)
					.attr("alt", god.name);
				divGodContent.children("div#resultGodNickname").text(god.title);
			}
			// Displaying skill list
			var i, j; // Iterators
			if(isSkills) {
				var skills = result.skills;
				divSkillsContent.children("div#resultSkillsAbstract").text(skills.type === "hard" ? "Full random shitz! " : "Take 1 level of each skill before level 5, then: 4 > " + skills.order[0] + " > " + skills.order[1] + " > " + skills.order[2] + " ");
				var something = ["1", "2", "3", "u"];
				var abilities = isGod ? god.skills : null;
				var table = divSkillsContent.children("table#resultSkillsTable");
				var rows = table.find("tr");
				// Left header
				for(i = 0 ; i < 4 ; i++) {
					var a = abilities !== null ? abilities[something[i]] : "null";
					$(rows[i + 1]).children("td:eq(0)").children("img")
						.attr("src", (isGod ? a.picture : "/Styles/Pics/Icons/Default/Ability_" + (i === 3 ? "u" : (i + 1)) + ".jpg"))
						.attr("title", (isGod ? (i + 1) + ": " + a.name : (i + 1)))
						.attr("alt", "");
				}
				// Body
				if(skills.order) {
					var order = skills.order;
					skills = [
						order[0], order[1], order[0], order[2], "u",
						order[0], order[0], order[1], "u", order[0],
						order[1], order[1], "u", order[1], order[2],
						order[2], "u", order[2], order[2], "u"
					];
				}
				else
					skills = skills.list;
				for(i = 0 ; i < 20 ; i++) {
					var r = skills[i];
					for(j = 0 ; j < 4 ; j++) {
						var td = $(rows[1 + j]).children("td:eq(" + (i + 1) + ")");
						if(r == something[j]) {
							td.addClass("checked")
							.attr("title", "Up that skill at level " + (i + 1));
							continue;
						}
						td.removeClass("checked").attr("title", "");
					}
				}
			}
			// Displaying build items and actives
			if(isBuild) {
				var build = data.result.build;
				var itemsUl = divItemsContent.children("ul#resultItemsList"), activesUl = divItemsContent.children("ul#resultAbilitiesList");
				var itemLi, activeLi, name, cost, computedCost, divCost, curCost;
				var h, hLength;
				// List for items
				for(i = 0 ; i < 6 ; i++) {
					var oneItem = build.items[i];
					name = oneItem.name;
					cost = [];
					for(j = 3; j > 0; --j) {
						if(typeof oneItem.prices[j] === "undefined")
							continue;
						hLength = cost.length;
						cost[hLength] = curCost = oneItem.prices[j];
						for(h = 0; h < hLength; ++h)
							cost[h] += curCost;
					}
					itemLi = itemsUl.children("li:eq(" + i + ")");
					itemLi.children("img")
						.attr("src", oneItem.picture)
						.attr("alt", name);
					itemLi.children("h3").text(name);
					divCost = itemLi.children("div.cost");
					for(j = 0 ; j < cost.length ; j++)
						divCost.children("div.r" + j).text(cost[j]);
					// Fixing cost for items with less than 3 ranks
					for(j ; j < 3 ; j++) 
						divCost.children("div.r" + j).text("");
				}
				// List for actives
				for(i = 0 ; i < 2 ; i++) {
					var oneActive = build.actives[i];
					name = oneActive.name;
					cost = [];
					for(j = 3; j > 0; --j) {
						if(typeof oneActive.prices[j] === "undefined")
							continue;
						hLength = cost.length;
						cost[hLength] = curCost = oneActive.prices[j];
						for(h = 0; h < hLength; ++h)
							cost[h] += curCost;
					}
					activeLi = activesUl.children("li:eq(" + i + ")");
					activeLi.children("img")
						.attr("src", oneActive.picture)
						.attr("alt", name);
					activeLi.children("h3").text(name);
					divCost = activeLi.children("div.cost");
					for(j = 0 ; j < cost.length ; j++)
						divCost.children("div.r" + j).text(cost[j]);
					// Fixing cost for actives with less than 3 ranks
					for(j ; j < 3 ; j++) 
						divCost.children("div.r" + j).text("");
				}
				// Total cost
				$("div#resultTotalBuildCost").text("Total cost: " + build.cost + " gold");
			}
			// Permalink
			 $("input#resultPermalink").val(result.permalink);
//			 Displaying result sheet and scrolling to it
			divGodNoContent[isGod ? "hide" : "show"]();
			divGodContent[isGod ? "show" : "hide"]();
			divSkillsNoContent[isSkills ? "hide" : "show"]();
			divSkillsContent[isSkills ? "show" : "hide"]();
			divItemsNoContent[isBuild ? "hide" : "show"]();
			divItemsContent[isBuild ? "show" : "hide"]();
			sectionResult.show();
			$(document).scrollTop($("div#resultFrame").offset().top);
		},
		storage: {
			config: {
				save: function() {
					if(data.loadingState === "loading" || data.storageMethod === null) return;
					var toSave = {
						toCompute: data.cfg.toCompute,
						gods: data.cfg.gods,
						skills: data.cfg.skills,
						items: data.cfg.items
					};
					if(data.storageMethod === "localStorage") localStorage.availableGodList = JSON.stringify(toSave);
					else {
						var expiration = new Date();
						expiration.setTime(expiration.getTime() + 7776000000);
						ut.cookie.save([{
								name: "smitroll-cfg-tocompute",
								value: JSON.stringify(toSave.toCompute),
								expires: expiration
							},{
								name: "smitroll-cfg-gods",
								value: JSON.stringify(toSave.gods),
								expires: expiration
							},{
								name: "smitroll-cfg-skills",
								value: JSON.stringify(toSave.skills),
								expires: expiration
							},{
								name: "smitroll-cfg-items",
								value: JSON.stringify(toSave.items),
								expires: expiration
						}]);
					} 
				},
				load: function() {
					var dataLoaded = {};
					try {
						if(data.storageMethod === "localStorage") {
							dataLoaded = localStorage.availableGodList || null;
							if(dataLoaded !== null) dataLoaded = JSON.parse(dataLoaded);
						}
						else if(data.storageMethod === "cookie") {
							var cookieData = ut.cookie.load();
							dataLoaded.toCompute = JSON.parse(cookieData["smitroll-cfg-tocompute"]) || null;
							dataLoaded.gods = JSON.parse(cookieData["smitroll-cfg-gods"]) || null;
							dataLoaded.skills = JSON.parse(cookieData["smitroll-cfg-skills"]) || null;
							dataLoaded.items = JSON.parse(cookieData["smitroll-cfg-items"]) || null;
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
		$("a#selectAllGods").on("click", handlers.pickGod.selectAll);
		$("a#deselectAllGods").on("click", handlers.pickGod.deselectAll);
		$("ul#configurationList > li.god > header > a").on("click", function() { handlers.views.switchFor("god"); });
		$("ul#configurationList > li.skills > header > a").on("click", function() { handlers.views.switchFor("skills"); });
		$("ul#configurationList > li.items > header > a").on("click", function() { handlers.views.switchFor("items"); });
		$("ul#selectSkillsLvl > li > a").on("click", handlers.radioList.skills);
		$("ul#selectItemsLvl > li > a").on("click", handlers.radioList.items);
		$("button#apocalypshitIsComing").on("click", handlers.process);
		
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
		
		// Load stuff
		data.loadingState = "loading";
		load.configuration();
		// data.loadingState = "result";
		data.loadingState = "complete";
	});
})();