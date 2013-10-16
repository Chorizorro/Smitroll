var ut = {	
	log: {
		print: function (message, type) {
			if (!cfg.debug) return;
			if (!(window.console && console.log)) return;
			if(!(typeof type === "string" && type !== "")) {
				console.log(message);
				return;
			}
			console.log("[" + type + "] " + message);
		},
		info: function (message) {
			ut.log.print(message, "INFO");
		},
		warn: function (message) {
			ut.log.print(message, "WARN");
		},
		error: function (message) {
			ut.log.print(message, "ERROR");
		}
	},
	dom: {
		newElement: function(tag) {
			return $(document.createElement(tag));
		}
	},
	cookie: {
		enabled: function() {
			try {
				if(!navigator.cookieEnabled) throw new Exception();
				var now = new Date();
				now = now.setTime(now.getTime() + 10000);
				ut.cookie.save({
					name: "smitroll-test",
					value: "test"
				});
				return true;
			}
			catch(e) {
				ut.log.warn('The browser does not support cookies ("' + e.message + '")');
			}
			return false;
		},
		load: function() {
			var cookie = document.cookie.split(";");
			var o = {};
			for(var i = 0, iLength = cookie.length ; i < iLength ; i++) {
				var parts = cookie[i].match(/^([^=]+)=(.+)$/i);
				if(!(parts && parts.length == 3)) continue;
				o[$.trim(parts[1])] = unescape($.trim(parts[2]));
			}
			return o;
		},
		save: function(o) {
			if(typeof o !== "object") return false;
			if(!(o instanceof Array)) o = [o];
			for(var i = 0, iLength = o.length ; i < iLength ; i++) {
				var data = o[i];
				if(!(data.name && data.value)) continue;
				var cookieString = data.name + "=" + escape(data.value);
				if(data.expires) cookieString += "; expires=" + (data.expires instanceof Date ? data.expires.toGMTString() : data.expires);
				if(data.path) cookieString += "; path=" + data.path;
				if(data.domain) cookieString += "; domain=" + data.domain;
				if(data.secure) cookieString += "; secure=" + data.secure;
				document.cookie = cookieString;
			}
			return true;
		}
	},
	image: {
		preload: function(img, callback) {
			var type = typeof img;
			if(type === "undefined" || !img) return;
			if(type === "string") {
				img = img.split(/,/gi);
			}
			if(!(img instanceof Array)) {
				ut.log.error('ut.image.preload(): img must be valid string of image path or a valid array of image path ("' + type + '" given)');
				return;
			}
			// Preloading with jQuery
			var	tmpImg = $(ut.dom.newElement("img")).attr("src", img);
			if(typeof callback === "function") tmpImg.on("load", callback);
		}
	},
	data: {
		findId: function(arr, id) {
			for(var i = 0, iLength = arr.length ; i < iLength ; i++) {
				if(arr[i].id == id) return i;
			}
			return -1;
		}
	},
	random: {
		integer: function(n1, n2) {
			if(typeof n2 == "undefined" || n2 === null) {
				n2 = n1;
				n1 = 0;
			}
			return n1 + Math.floor(Math.random() * (n2 - n1 + 1));
		},
		inArray: function(arr) {
			return arr[ut.random.integer(arr.length-1)];
		},
		elementsInArray: function(arr) {
			var result = [], range = arr.length - 1;
			while(range > 0) result.push(arr.splice(ut.random.integer(range--), 1)[0]);
			result.push(arr.pop());
			return result;
		}
	},
	rainbowfy: function() {
		function iSeeYourTrueColors (ratio) {
			var r, g, b;
			if(ratio < 0.2) {
				r = 255;
				g = Math.floor(1275 * ratio); // 255 * (ratio * 5) pre-computed
				b = 0;
			}
			else if(ratio < 0.4) {
				r = Math.floor(510 - 1275 * ratio); // 255 * (1 - (ratio - 0.2) * 5) pre-computed
				g = 255;
				b = 0;
			}
			else if(ratio < 0.6) {
				r = 0;
				g = 255;
				b = Math.floor(1275 * (ratio - 0.4));
			}
			else if(ratio < 0.8) {
				r = 0;
				g = Math.floor(1020 - 1275 * ratio); // 255 * (1 - (ratio - 0.6) * 5) pre-computed
				b = 255;
			}
			else if(ratio <= 1) {
				r = Math.floor(1275 * (ratio - 0.8)); // 255 * ((ratio - 0.8) * 5) pre-computed
				g = 0;
				b = 255;
			}
			else {
				ut.log.error("ut.rainbowfy:iSeeYourTrueColors() - ratio must be a number between 0 and 1");
				return "pink";
			}
			
			function doubleRainbow(c) {
				c = c.toString(16);
				while(c.length < 2) c = "0" + c;
				return c;
			}
			
			return "#" + doubleRainbow(r) + doubleRainbow(g) + doubleRainbow(b);
		}
		$(".rainbowfy").each(function(i, elt) {
			var jElt = $(elt);
			var text = jElt.text();
			var indexes = [], j, jLength;
			jElt.empty();
			// This will revert the string
			for(j = text.length - 1 ; j >= 0 ; j--) {
				var c = text[j];
				if(c !== " ") indexes.push(c);
				else indexes[indexes.length - 1] = " " + indexes[indexes.length - 1];
			}
			// This will revert it one more time
			for(j = 0, jLength = indexes.length ; j < jLength ; j++) {
				var color = iSeeYourTrueColors(1 - j / (jLength - 1));
				// $("div#result").append(ut.dom.newElement("span").text(indexes[j]).css("color", color));
				jElt.after(ut.dom.newElement("span").text(indexes[j]).css({
					"color": color,
					"font-weight": "bold",
					"text-transform": "uppercase"
				}));
			}
			// Remove the origin fucker
			jElt.remove();
		});
	}
};

$(document).ready(function() {
	ut.rainbowfy();
});
