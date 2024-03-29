// Add images form
Util.Modules["addMedia"] = new function() {
	this.init = function(div) {
		// u.bug("addMedia init:", div);

		div.form = u.qs("form.upload", div);
		div.form.div = div;


		// base media info
		div.item_id = u.cv(div, "item_id");
		div.variant = u.cv(div, "variant");


		u.f.init(div.form);


		div.csrf_token = div.form.inputs["csrf-token"].val();
		div.delete_url = div.getAttribute("data-media-delete");
		div.update_name_url = div.getAttribute("data-media-name");
		div.save_order_url = div.getAttribute("data-media-order");


		// Create easy reference to file input
		div.media_input = div.form.inputs[div.variant+"[]"];
		div.filelist = div.media_input.field.filelist;
		div.previewlist = u.ae(div, "ul", {class:"previewlist"});
		div.previewlist.div = div;


		// Submit on change
		div.form.changed = function() {
			this.submit();
		}

		// upload form submitted
		div.form.submitted = function() {

			this.div.media_input.blur();
			// Enter loading state
			u.ac(this.div.media_input.field, "loading");


			this.response = function(response) {
				page.notify(response);

				// inject/update image if everything went well
				if(response.cms_status == "success" && response.cms_object) {

					// Update file list status
					u.f.updateFilelistStatus(this, response);

					// update preview
					this.div.updatePreviews();

				}

				// Leave loading state
				u.rc(this.div.media_input.field, "loading");
				// Reset file queue
				this.div.media_input.val("");

			}
			u.request(this, this.action, {"method":"post", "data":this.getData()});

		}


		// set media type for form
		// previews are different for different types
		// add preview based on current media format
		div.updatePreviews = function() {

			// Look for something to preview
			var nodes = u.qsa("li.uploaded,li.new", this.filelist);
			if(nodes) {

				var i, node, li;
				for(i = 0; i < nodes.length; i++) {

					node = nodes[i];

					// Only do something if node doesn't already have preview
					if(!node.li_preview) {


						// Get base properties
						node.media_format = u.cv(node, "format");
						node.media_variant = u.cv(node, "variant");
						node.media_id = u.cv(node, "media_id");
						node.media_name = node.innerHTML;
						node.div = this;


						node.li_preview = u.ae(this.previewlist, "li", {class: "preview media_id:" + node.media_id});
						node.preview = u.ae(node.li_preview, "div", {class: "preview " + node.media_format});
						node.preview.node = node;


						// set media type
						if(node.media_format.match(/^(jpg|png|gif)$/i)) {

							u.addImagePreview(node);
						}
						else if(node.media_format.match(/^(mp3|ogg|wav|aac)$/i)) {
					
							u.addAudioPreview(node);
						}
						else if(node.media_format.match(/^(mov|mp4|ogv|3gp)$/i)) {

							u.addVideoPreview(node);
						}
						else if(node.media_format.match(/^zip$/i)) {
					
							u.addZipPreview(node);
						}
						else if(node.media_format.match(/^pdf$/i)) {

							u.addPdfPreview(node);
						}


						// Add rename form
						u.addRenameMediaForm(div, node);

						// Add delete form
						u.addDeleteMediaForm(div, node);


						// Callback on successful delete
						node.delete_form.deleted = function(response) {

							// Remove from filelist
							this.node.div.filelist.removeChild(this.node);

							// Remove from preview list
							this.node.div.previewlist.removeChild(this.node.li_preview);

							// Update uploaded_files list
							this.node.div.media_input.field.uploaded_files = u.qsa("li.uploaded", this.node.div.filelist);

							// Update sortable
							if(fun(this.node.div.previewlist.updateDraggables)) {
								this.node.div.previewlist.updateDraggables();
							}

						}

					}
					// Maintain order
					else {
						u.ae(this.previewlist, node.li_preview);
					}

				}

			}

			// Update sortable
			if(fun(this.previewlist.updateDraggables)) {
				this.previewlist.updateDraggables();
			}

		}



		div.updatePreviews();


		// sortable list
		if(u.hc(div, "sortable") && div.save_order_url) {

			u.sortable(div.previewlist);

			div.previewlist.picked = function(node) {}
			div.previewlist.dropped = function(node) {

				// Get node order
				var order = this.getNodeOrder({class_var:"media_id"});

				this.response = function(response) {
					// Notify of event
					page.notify(response);
				}
				u.request(this, this.div.save_order_url+"/"+this.div.item_id, {
					"method":"post", 
					"data":"csrf-token=" + this.div.csrf_token + "&order=" + order.join(",")
				});
			}
		}
		// no save url
		else {
			u.rc(div, "sortable");
		}

	}
}

// Add images form
Util.Modules["addMediaSingle"] = new function() {
	this.init = function(div) {
		// u.bug("addMediaSingle init:", div);

		div.form = u.qs("form.upload", div);
		div.form.div = div;

		// base media info
		div.item_id = u.cv(div, "item_id");
		div.variant = u.cv(div, "variant");


		u.f.init(div.form);


		div.csrf_token = div.form.inputs["csrf-token"].val();
		div.delete_url = div.getAttribute("data-media-delete");
		div.update_name_url = div.getAttribute("data-media-name");


		// Create easy reference to file input
		div.media_input = div.form.inputs[div.variant+"[]"];
		div.filelist = div.media_input.field.filelist;

		// Submit on change
		div.form.changed = function() {
			this.submit();
		}

		// Handle upload
		div.form.submitted = function() {

			this.div.media_input.blur();
			// Enter loading state
			u.ac(this.div.media_input.field, "loading");


			// hide existing preview while waiting for response
			if(this.div.preview) {
				u.as(this.div.preview, "display", "none");
			}

			this.response = function(response) {
				page.notify(response);

				// inject/update preview if everything went well
				if(response.cms_status == "success" && response.cms_object) {

					// Update file list status
					u.f.updateFilelistStatus(this, response);

					// update preview
					this.div.updatePreview();

				}

				// Leave loading state
				u.rc(this.div.media_input.field, "loading");
				// Reset file queue
				this.div.media_input.val("");

			}
			u.request(this, this.action, {"method":"post", "data":this.getData()});
		}


		// set media type for form
		// previews are different for different types
		// add preview based on current media format
		div.updatePreview = function() {

			// remove existing preview
			if(this.preview) {
				this.preview.parentNode.removeChild(this.preview);
				delete this.preview;
			}

			// Look for something to preview
			var node = u.qs("li.uploaded", this.filelist);
			if(node) {


				// Get base properties
				node.media_format = u.cv(node, "format");
				node.media_variant = u.cv(node, "variant");
				node.media_name = node.innerHTML;
				node.div = this;

				// Create preview node
				node.preview = u.ae(this, "div", {"class": "preview " + node.media_format});
				node.preview.node = node;
				// map current preview to div
				this.preview = node.preview;

				// Adjust preview width
				u.ass(node.preview, {
					"width": this.filelist.offsetWidth + "px"
				});


				// set media type
				if(node.media_format.match(/^(jpg|png|gif)$/i)) {

					u.addImagePreview(node);
				}
				else if(node.media_format.match(/^(mp3|ogg|wav|aac)$/i)) {

					u.addAudioPreview(node);
				}
				else if(node.media_format.match(/^(mov|mp4|ogv|3gp)$/i)) {

					u.addVideoPreview(node);
				}
				else if(node.media_format.match(/^zip$/i)) {

					u.addZipPreview(node);
				}
				else if(node.media_format.match(/^pdf$/i)) {

					u.addPdfPreview(node);
				}


				// Add rename form
				u.addRenameMediaForm(div, node);

				// Add delete form
				u.addDeleteMediaForm(div, node);

				// Callback on successful delete
				node.delete_form.deleted = function(response) {

					// Reset uploaded files list
					this.node.div.media_input.field.uploaded_files = false;

					// Force validation
					this.node.div.media_input.val("");

					// Update preview
					this.node.div.updatePreview();

				}

			}

		}

		// add start preview
		div.updatePreview();

	}
}


// IMAGE preview
u.addImagePreview = function(node) {

	u.ac(node, "preview_image");

	// Get additional image properties
	node.media_width = u.cv(node, "width");
	node.media_height = u.cv(node, "height");


	// Adjust preview height
	u.ass(node.preview, {
		"width": ((node.preview.offsetHeight/node.media_height) * node.media_width) + "px",
		"backgroundImage": "url(/images/"+node.div.item_id+"/"+node.media_variant+"/x"+node.preview.offsetHeight+"."+node.media_format+"?"+u.randomString(4)+")"
	});

}

// PDF preview
u.addPdfPreview = function(node) {

	u.ac(node, "preview_pdf");

	// Adjust preview height
	u.ass(node.preview, {
		"backgroundImage": "url(/images/0/pdf/30x.png)"
	});

}

// ZIP preview
u.addZipPreview = function(node) {

	u.ac(node, "preview_zip");

	// Adjust preview height
	u.ass(node.preview, {
		"backgroundImage": "url(/images/0/zip/30x.png)"
	});

}

// AUDIO preview
u.addAudioPreview = function(node) {

	u.ac(node, "preview_audio");

	// enable playback
	node.preview.audio_url = "/audios/"+node.div.item_id+"/"+node.media_variant+"/128."+node.media_format+"?"+u.randomString(4);

	// Add play button
	u.addPlayMedia(this, node);

}

// VIDEO preview
u.addVideoPreview = function(node) {

	u.ac(node, "preview_video");

	// Get additional image properties
	node.media_width = u.cv(node, "width");
	node.media_height = u.cv(node, "height");

	// Adjust preview height
	u.ass(node.preview, {
		"width": ((node.preview.offsetHeight/node.media_height) * node.media_width) + "px",
	});


	// enable playback
	node.preview.video_url = "/videos/"+node.div.item_id+"/"+node.media_variant+"/"+node.div.filelist.offsetWidth+"x."+node.media_format+"?"+u.randomString(4);

	// Add play button
	u.addPlayMedia(node.div, node);

	u.e.hover(node.preview);
	node.preview.over = function() {
		this.node.play_preview();
	}
	node.preview.out = function() {
		this.node.pause_preview();
	}

}



// add play form
u.addPlayMedia = function(div, node) {


	var controls = u.ae(node.preview, "div", {"class":"controls"});

	// Create play button
	node.bn_play = u.ae(controls, "div", {"class":"play"});
	// node.bn_play.preview = node.preview;
	node.bn_play.node = node;

	if(!node.player) {
		node.player = node.preview.audio_url ? u.audioPlayer() : u.videoPlayer({"muted":true, "loop":true, "preload":"metadata"});
		node.player.node = node;

		// inject player
		u.ae(node.preview, node.player);

		// Load url to show first frame
		node.player.load(node.preview.audio_url ? node.preview.audio_url : node.preview.video_url);
	}

	u.ce(node.bn_play);
	node.bn_play.clicked = function(event) {
		if(!u.hc(this.node.preview, "playing")) {
			this.node.play_preview();
		}
		else {
			this.node.pause_preview();
		}
	}
	node.play_preview = function() {
		this.player.play();
		u.ac(this.preview, "playing");
	}
	node.pause_preview = function() {
		this.player.pause();
		u.rc(this.preview, "playing");
	}

	// Create mute button
	node.bn_mute = u.ae(controls, "div", {"class":"mute"});
	// node.bn_play.preview = node.preview;
	node.bn_mute.node = node;

	u.ce(node.bn_mute);
	node.bn_mute.clicked = function(event) {
		if(!u.hc(this.node.preview, "muted")) {
			this.node.player.mute();
			u.ac(this.node.preview, "muted");
		}
		else {
			this.node.player.unmute();
			u.rc(this.node.preview, "muted");
		}
	}
	u.ac(node.preview, "muted");

}

// add delete form
u.addDeleteMediaForm = function(div, node) {

	// Create delete form
	node.delete_form = u.f.addForm(node.preview, {
		"action":div.delete_url+"/"+div.item_id+"/"+node.media_variant, 
		"class":"delete"
	});
	node.delete_form.node = node;

	// Add csrf-token
	u.f.addField(node.delete_form, {
		"type":"hidden",
		"name":"csrf-token", 
		"value":div.csrf_token
	});
	// Add button
	u.f.addAction(node.delete_form, {
		"class":"button delete"
	});

	// Add oneButtonForm properties
	node.delete_form.setAttribute("data-confirm-value", "Confirm");
	node.delete_form.setAttribute("data-success-function", "deleted");

	// Initialize oneButtonForm
	u.m.oneButtonForm.init(node.delete_form);

}

// add delete form
u.addRenameMediaForm = function(div, node) {

	// add update form
	node.update_name_form = u.f.addForm(node.preview, {
		"action":div.update_name_url+"/"+div.item_id+"/"+node.media_variant, 
		"class":"edit"
	});
	node.update_name_form.node = node;

	// Add csrf-token
	u.f.addField(node.update_name_form, {
		"type":"hidden",
		"name":"csrf-token", 
		"value":div.csrf_token
	});
	// Add name input
	u.f.addField(node.update_name_form, {
		"type":"string",
		"name":"name", 
		"value":node.media_name, 
		"required":true
	});

	// init form
	u.f.init(node.update_name_form);

	// enable editing activation on whole update form
	u.ce(node.update_name_form);

	// eliminate dragging if sorting is also enable
	node.update_name_form.inputStarted = function(event) {
		if(!u.hc(this.node.preview, "edit")) {

			u.e.kill(event);
		}

	}
	// Enter edit state
	node.update_name_form.clicked = function(event) {
		u.ac(this.node.preview, "edit");

		this.inputs["name"].focus();
	}

	// submit on blur
	node.update_name_form.inputs["name"].blurred = function() {

		// Update if input validates
		if(this.is_correct) {
			this._form.updateName();
		}
	}

	// submit is handled on blur
	node.update_name_form.submitted = function() {

		this.inputs["name"].blur();
	}

	// update name
	node.update_name_form.updateName = function() {

		u.rc(this.node.preview, "edit");

		// submit new image name
		this.response = function(response) {

			page.notify(response);

			// reset media name back to starting value if something went wrong
			if(response.cms_status !== "success") {
				this.inputs["name"].val(this.node.media_name);
			}

		}
		u.request(this, this.action, {"method":this.method, "data":this.getData()});

	}
}

