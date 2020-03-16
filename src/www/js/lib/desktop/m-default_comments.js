Util.Modules["defaultComments"] = new function() {
	this.init = function(div) {

		div.item_id = u.cv(div, "item_id");

		// CMS interaction urls
		div.delete_comment_url = div.getAttribute("data-comment-delete");
		div.update_comment_url = div.getAttribute("data-comment-update");
		div.csrf_token = div.getAttribute("data-csrf-token");

		// add tag form
		div._comments_form = u.qs("form", div);

		div._comments_list = u.qs("ul.comments", div);


		if(div._comments_form) {
			div._comments_form.div = div;

			u.f.init(div._comments_form);


			div.add_comment_url = div._comments_form.action;



			// new comment submitted
			div._comments_form.submitted = function(iN) {

				this.response = function(response) {
					page.notify(response);

					if(response.cms_status == "success" && response.cms_object) {

						var comment_li = u.ae(this.div._comments_list, "li", {"class":"comment comment_id:"+response.cms_object["id"]});
						var info = u.ae(comment_li, "ul", {"class":"info"});
						u.ae(info, "li", {"class":"user", "html":response.cms_object["nickname"]});
						u.ae(info, "li", {"class":"created_at", "html":response.cms_object["created_at"]});
						u.ae(comment_li, "p", {"class":"comment", "html":response.cms_object["comment"]})

						this.div.initComment(comment_li);

						// reset form input
						this.inputs["item_comment"].val("");

					}
				}
				u.request(this, this.action, {"method":"post", "params" : u.f.getParams(this)});
			}
			
		}

		// add edit+delete form to comment
		div.initComment = function(node) {

			node.div = this;

			if(this.delete_comment_url || this.update_comment_url) {

				var actions = u.ae(node, "ul", {"class":"actions"});
				var li;

				if(this.update_comment_url) {
					li = u.ae(actions, "li", {"class":"edit"});
					bn_edit = u.ae(li, "a", {"html":"Edit", "class":"button edit"});
					bn_edit.node = node;

					u.ce(bn_edit);
					bn_edit.clicked = function() {

						var actions, bn_cancel, bn_update, form;

						form = u.f.addForm(this.node, {"action":this.node.div.update_comment_url+"/"+this.node.div.item_id+"/"+u.cv(this.node, "comment_id"), "class":"edit"});
						u.ae(form, "input", {"type":"hidden","name":"csrf-token", "value":this.node.div.csrf_token});
						u.f.addField(form, {"type":"text", "name":"item_comment", "value": u.qs("p.comment", this.node).innerHTML});
						form.node = node;

						actions = u.ae(form, "ul", {"class":"actions"});

						bn_update = u.f.addAction(actions, {"value":"Update", "class":"button primary update", "name":"update"});
						bn_cancel = u.f.addAction(actions, {"value":"Cancel", "class":"button cancel", "type":"button", "name":"cancel"});

						u.f.init(form);

						form.submitted = function() {

							this.response = function(response) {
								page.notify(response);

								if(response.cms_status == "success") {

									u.qs("p.comment", this.node).innerHTML = this.inputs["item_comment"].val();
									this.parentNode.removeChild(this);
								}
							}
							u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
							
						}

						u.ce(bn_cancel);
						bn_cancel.clicked = function(event) {
							u.e.kill(event);
							this.form.parentNode.removeChild(this.form);
						}
					}
				}

				if(this.delete_comment_url) {
					li = u.ae(actions, "li", {"class":"delete"});

					var form = u.f.addForm(li, {"action":this.delete_comment_url+"/"+this.item_id+"/"+u.cv(node, "comment_id"), "class":"delete"});
					u.ae(form, "input", {"type":"hidden","name":"csrf-token", "value":this.csrf_token});
					form.node = node;
					bn_delete = u.f.addAction(form, {"value":"Delete", "class":"button delete", "name":"delete"});

					u.f.init(form);

					form.restore = function(event) {
						this.actions["delete"].value = "Delete";
						u.rc(this.actions["delete"], "confirm");
					}

					form.submitted = function() {

						// first click
						if(!u.hc(this.actions["delete"], "confirm")) {
							u.ac(this.actions["delete"], "confirm");
							this.actions["delete"].value = "Confirm";
							this.t_confirm = u.t.setTimer(this, this.restore, 3000);
						}
						// confirm click
						else {
							u.t.resetTimer(this.t_confirm);


							this.response = function(response) {
								page.notify(response);

								if(response.cms_status == "success") {
									// check for constraint error preventing row from actually being deleted
									if(response.cms_object && response.cms_object.constraint_error) {
										this.value = "Delete";
										u.ac(this, "disabled");
									}
									else {
										this.node.parentNode.removeChild(this.node);
									}
								}
							}
							u.request(this, this.action, {"method":"post", "params":u.f.getParams(this)});
						}
					}
				}

			}

		}


		// initalize existing comments
		div.comments = u.qsa("li.comment", div._comments_list);
		var i, node;
		for(i = 0; node = div.comments[i]; i++) {
			div.initComment(node);
		}

	}
}
