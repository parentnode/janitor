Util.Modules["defaultEditors"] = new function() {
	this.init = function(div) {

		div.item_id = u.cv(div, "item_id");

		// CMS interaction urls
		div.remove_editor_url = div.getAttribute("data-editor-remove");
		div.csrf_token = div.getAttribute("data-csrf-token");

		// add tag form
		div._form_editors = u.qs("form.editors", div);

		div._list_editors = u.qs("ul.editors", div);


		if(div._form_editors) {
			div._form_editors.div = div;

			u.f.init(div._form_editors);

			// new editor submitted
			div._form_editors.submitted = function(iN) {

				this.response = function(response) {
					page.notify(response);

					if(response.cms_status == "success" && response.cms_object && response.cms_object !== true) {

						if(!this.div._list_editors) {
							this.div._list_editors = u.ae(this.parentNode, "ul", {"class":"items editors"});
							this.parentNode.insertBefore(this.div._list_editors, this);

							// Remove no editors text if it exists
							var p_no_editors = u.qs("p", this.div);
							if(p_no_editors) {
								p_no_editors.parentNode.removeChild(p_no_editors);
							}
						}

						var editor_li = u.ae(this.div._list_editors, "li", {"class":"editor editor_id:"+response.cms_object["id"]});
						u.ae(editor_li, "h3", {"html":response.cms_object["nickname"]});


						this.div.initEditor(editor_li);

						// reset form input
						this.reset();

					}
					else if(response.cms_status == "success" && response.cms_object && response.cms_object === true) {
						// reset form input
						this.reset();
					}

				}
				u.request(this, this.action, {"method":"post", "data" : this.getData()});

			}

		}

		// add remove form to editor
		div.initEditor = function(node) {

			node.div = this;

			if(this.remove_editor_url) {

				node.editor_id = u.cv(node, "editor_id");
				node._ul_actions = u.ae(node, "ul", {"class":"actions"});
				node._li_remove = u.ae(node._ul_actions, "li", {"class":"remove"});


				// Create remove form
				node._form_remove = u.f.addForm(node._li_remove, {
					"action":this.remove_editor_url, 
					"class":"remove"
				});
				node._form_remove.node = node;

				// Add csrf-token
				u.f.addField(node._form_remove, {
					"type":"hidden",
					"name":"csrf-token", 
					"value":div.csrf_token
				});
				u.f.addField(node._form_remove, {
					"type":"hidden",
					"name":"editor_id", 
					"value":node.editor_id
				});
				// Add button
				u.f.addAction(node._form_remove, {
					"value":"Remove",
					"class":"button remove"
				});

				// Add oneButtonForm properties
				node._form_remove.setAttribute("data-success-function", "removed");
				node._form_remove.setAttribute("data-confirm-value", "Are you sure?");

				// Initialize oneButtonForm
				u.m.oneButtonForm.init(node._form_remove);


				node._form_remove.removed = function(response) {
					this.node.parentNode.removeChild(this.node);
				}

			}

		}


		// initalize existing comments
		div.editors = u.qsa("li.editor", div._list_editors);
		var i, node;
		for(i = 0; node = div.editors[i]; i++) {
			div.initEditor(node);
		}

	}
}
