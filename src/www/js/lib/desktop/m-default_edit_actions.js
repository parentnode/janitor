Util.Modules["defaultEditActions"] = new function() {
	this.init = function(node) {
		// u.bug("defaultEditActions:", node);

		var bn_duplicate = u.qs("li.duplicate", node);
		if(bn_duplicate) {

			bn_duplicate.duplicated = function(response) {
				console.log(response)
				
				location.href = location.href.replace(/edit\/.+/, "edit/"+response.cms_object["id"]);
			}
	
		}

		var bn_delete = u.qs("li.delete", node);
		if(bn_delete && u.hc(bn_delete, "has_dependencies")) {
			bn_delete.setAttribute("title", "This item has dependencies and cannot be deleted.");
		}
		// add autosave option

		// bn_autosave = u.ae(node, "li", {"class":"autosave on", "html":"Autosave ON"});
		// u.e.click(bn_autosave);
		// bn_autosave.clicked = function() {
		// 	if(u.hc(this, "on")) {
		//
		// 		u.rc(this, "on");
		// 		page.autosave_disabled = true;
		// 	}
		// 	else {
		//
		// 		u.ac(this, "on");
		// 		page.autosave_disabled = false;
		// 	}
		// 	u.bug("toggle autosave")
		// }

	}
}

