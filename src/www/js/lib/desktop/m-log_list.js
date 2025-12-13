Util.Modules["logList"] = new function() {
	this.init = function(div) {
		u.bug("init logList:", div);

		var i, node;

		div.list = u.qs("ul.items", div);

		div.log_files_and_lines = u.qsa("li.log_file,li.item.log_line", div.list);

		var i, log_file, li;
		for(i = 0; i < div.log_files_and_lines.length; i++) {
			li = div.log_files_and_lines[i];
			if(u.hc(li, "log_file")) {
				log_file = li;
			}
			else {
				li.log_file = log_file;				
			}
		}

		
	
		div.filtered = function() {
			u.bug
			var log_files = u.qsa("li.log_file", div.list);
			var log_lines = u.qsa("li.item.log_line:not(.hidden)", div.list);

			var i, li;

			// Hide all log file rows
			for(i = 0; i < log_files.length; i++) {
				li = log_files[i];
				li.is_shown = false;
				u.ass(li, {
					"display": "none"
				});
			}

			// Show log file rows with visible lines
			for(i = 0; i < log_lines.length; i++) {
				li = log_lines[i];
				if(!li.log_file.is_shown) {
					li.log_file.is_shown = true;
					u.ass(li.log_file, {
						"display": "block"
					});
				}
			}

		}

	}
}
