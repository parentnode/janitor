// Add images form
Util.Objects["addMedia"] = new function() {
	this.init = function(div) {

//		u.bug("addMedia init:" + u.nodeId(div))

		div.form = u.qs("form.upload", div);
		div.form.div = div;
		div.media_list = u.qs("ul.mediae", div);

		div.item_id = u.cv(div, "item_id");


		u.f.init(div.form);


		div.csrf_token = div.form.fields["csrf-token"].val();
		div.delete_url = div.getAttribute("data-media-delete");
		div.update_name_url = div.getAttribute("data-media-name");
		div.save_order_url = div.getAttribute("data-media-order");



		div.form.file_input = u.qs("input[type=file]", div.form);
		div.form.file_input.div = div;
		div.form.file_input.changed = function() {
			this.form.submit();
		}

		// upload form submitted
		div.form.submitted = function() {

			u.ac(this.file_input.field, "loading");
			u.rc(this.file_input.field, "focus");

			var form_data = new FormData(this);
			this.response = function(response) {
				page.notify(response);

				// inject/update image if everything went well
				if(response.cms_status == "success" && response.cms_object) {

					var i, media, node, image;
					for(i = 0; media = response.cms_object[i]; i++) {

						if(u.hc(this.div, "variant")) {
							var existing_variant = u.ge("variant:"+media.variant);
							if(existing_variant) {
								existing_variant.parentNode.removeChild(existing_variant);
							}
						}

						var node = u.ie(this.div.media_list, "li", {"class":"media"});

						node.div = this.div;
						node.media_list = this.div.media_list;
						node.media_format = media.format;
						node.media_variant = media.variant;
						
						u.ac(node, "format:"+media.format);
						u.ac(node, "variant:"+media.variant);
						u.ac(node, "media_id:"+media.media_id);

						// inject preview
						this.div.addPreview(node);

						// update media name (preview makes sure name container is available)
						if(u.hc(this.div, "variant")) {
							node.media_name.innerHTML = media.variant;
						}
						else {
							node.media_name.innerHTML = media.name;
						}

						this.div.adjustMediaName(node);

						// add update form for image name
						if(!u.hc(this.div, "variant") && this.div.update_name_url) {
							this.div.addUpdateNameForm(node);
						}

					}

					if(this.div.save_order_url) {
						u.sortable(this.div.media_list);
					}
				}

				u.rc(this.file_input.field, "loading");
				this.file_input.val("");
			}
			u.request(this, this.action, {"method":"post", "params":form_data});
		}


		// add delete form
		div.addDeleteForm = function(node) {

			if(!node.delete_form) {
				node.delete_form = u.f.addForm(node, {"action":this.delete_url+"/"+this.item_id+"/"+u.cv(node, "variant"), "class":"delete"});
				node.delete_form.node = node;
				u.ae(node.delete_form, "input", {"type":"hidden", "name":"csrf-token", "value":this.csrf_token});

				var bn_delete = u.f.addAction(node.delete_form, {"class":"button delete"});

				node.delete_form.deleted = function() {
					this.node.parentNode.removeChild(this.node);

					if(u.hc(this.node.div, "sortable")) {
						u.sortable(this.node.div.media_list, {"targets":"mediae", "draggables":"media"});
					}

					this.node.delete_form = null;
				}
				u.o.deleteMedia.init(node.delete_form);
			}
		}

		// add delete form
		div.addUpdateNameForm = function(node) {

			node.media_name.node = node;

			// enable edit state
			u.ce(node.media_name);
			// eliminate dragging if sorting is also enable
			node.media_name.inputStarted = function(event) {
				u.e.kill(event);
				this.node.media_list._sorting_disabled = true;
			}
			node.media_name.clicked = function(event) {
				u.ac(this.node, "edit");

				var input = this.node.update_name_form.fields["name"];
				var field = input.field;

				input.focus();

				// set specific input width to match image
				var f_w = field.offsetWidth;
				var f_p_l = parseInt(u.gcs(field, "padding-left"));
				var f_p_r = parseInt(u.gcs(field, "padding-right"));
				var i_p_l = parseInt(u.gcs(input, "padding-left"));
				var i_p_r = parseInt(u.gcs(input, "padding-right"));
				var i_m_l = parseInt(u.gcs(input, "margin-left"));
				var i_m_r = parseInt(u.gcs(input, "margin-right"));
				var i_b_l = parseInt(u.gcs(input, "border-left-width"));
				var i_b_r = parseInt(u.gcs(input, "border-right-width"));
				u.as(input, "width", (f_w - f_p_l - f_p_r - i_p_l - i_p_r - i_m_l - i_m_r - i_b_l - i_b_r)+"px");

			}

			// add update form
			node.update_name_form = u.f.addForm(node, {"action":this.update_name_url+"/"+this.item_id+"/"+u.cv(node, "variant"), "class":"edit"});
			node.update_name_form.node = node;
			var field = u.ae(node.update_name_form, "input", {"type":"hidden", "name":"csrf-token", "value":this.csrf_token});
			var field = u.f.addField(node.update_name_form, {"type":"string","name":"name", "value":node.media_name.innerHTML});

			// init form
			u.f.init(node.update_name_form);

			// submit on blur
			node.update_name_form.fields["name"].blurred = function() {
				u.bug("blurred")
				this.form.updateName();
			}

			// do nothing on submit - it is handled on blur
			node.update_name_form.submitted = function() {}

			// update name
			node.update_name_form.updateName = function() {

				u.rc(this.node, "edit");
				this.node.media_list._sorting_disabled = false;

				// submit new image name
				this.response = function(response) {

					page.notify(response);

					// inject/update image if everything went well
					if(response.cms_status == "success" && response.cms_object) {
						this.node.media_name.innerHTML = this.fields["name"].val();
					}
					else {
						this.fields["name"].val(this.node.media_name.innerHTML);
					}

				}
				u.request(this, this.action, {"method":this.method, "params":u.f.getParams(this)});

			}
		}


		// set media type for form
		// previews are different for different types
		// add preview based on current media format
		div.addPreview = function(node) {

			// remove existing image
			if(node.image) {
				node.image.parentNode.removeChild(node.image);
				node.image = null;
			}
			// remove existing video
			if(node.video) {
				node.video.parentNode.removeChild(node.video);
				node.video = null;
			}


			// set media type
			node.is_image = node.media_format.match(/jpg|png|gif/i);
			node.is_audio = node.media_format.match(/mp3|ogg/i);
			node.is_video = node.media_format.match(/mov|mp4|ogv|3gp/i);
			node.is_zip = node.media_format.match(/zip/i);
			node.is_pdf = node.media_format.match(/pdf/i);


			// file div available
			// if(!node.media_file && node.media_format) {
			// 	node.media_file = u.ae(node, "div", {"class":"file"});
			// }

			if(node.media_format) {

				// media play button
				node.bn_player = u.qs("a", node);
				if(!node.bn_player) {
					node.bn_player = u.ie(node, "a");
				}
				node.bn_player.node = node;
				u.ce(node.bn_player);


				// media name
				node.media_name = u.qs("p", node);
				if(!node.media_name) {
					node.media_name = u.ae(node, "p");
				}
				node.media_name.node = node;
			}


			u.rc(node, "image|audio|video|pdf|zip");


			// inject previews
			if(node.is_audio) {
				u.ac(node, "audio");
				this.addAudioPreview(node);
			}
			else if(node.is_video) {
				u.ac(node, "video");
				this.addVideoPreview(node);
			}
			else if(node.is_image) {
				u.ac(node, "image");
				this.addImagePreview(node);
			}
			else if(node.is_pdf) {
				u.ac(node, "pdf");
				this.addPdfPreview(node);
			}
			else if(node.is_zip) {
				u.ac(node, "zip");
				this.addZipPreview(node);
			}

		}


		div.adjustMediaName = function(node) {
//			u.bug("adjust media name:" + u.nodeId(node) + ", " + node.media_name)

			// adjust media name width
			if(node.media_name) {

				// Set p width to match li
				var n_w = node.offsetWidth;
				var p_p_l = parseInt(u.gcs(node.media_name, "padding-left"));
				var p_p_r = parseInt(u.gcs(node.media_name, "padding-right"));
				u.as(node.media_name, "width", (n_w - p_p_l - p_p_r)+"px");

			}

		}

		// add image container (if needed)
		div.addImage = function(node) {

			if(!node.image && node.media_format) {
				node.image = u.ae(node, "img");

				var proportion = u.cv(node, "width")/u.cv(node, "height");

				u.as(node.image, "width", (node.offsetHeight*proportion)+"px");
				u.as(node.image, "height", node.offsetHeight+"px");
			}
		}

		// add video container (if needed)
		div.addVideo = function(node) {

			if(!page.videoplayer) {
				page.videoplayer = u.videoPlayer();
			}

			if(!node.video && node.media_format) {
				node.video = u.ae(node, page.videoplayer);

				var proportion = u.cv(node, "width")/u.cv(node, "height");

				u.as(node.video, "width", (node.offsetHeight*proportion)+"px");
				u.as(node.video, "height", node.offsetHeight+"px");
			}
		}


		// PDF preview
		div.addPdfPreview = function(node) {

			// add image if it does not exist and format is available
			this.addImage(node);

			if(node.media_format) {

				this.addDeleteForm(node);
				node.image.src = "/images/0/pdf/x"+node.offsetHeight+".png?"+u.randomString(4);
			}
		}

		// ZIP preview
		div.addZipPreview = function(node) {

			// add image if it does not exist and format is available
			this.addImage(node);

			if(node.media_format) {

				this.addDeleteForm(node);
				node.image.src = "/images/0/zip/x"+node.offsetHeight+".png?"+u.randomString(4);
			}
		}

		// IMAGE preview
		div.addImagePreview = function(node) {

			// add image if it does not exist and format is available
			this.addImage(node);

			if(node.media_format) {

				this.addDeleteForm(node);

				node.loaded = function(queue) {
					this.image.src = queue[0].image.src;

					this.div.adjustMediaName(this);
					
				}
				u.preloader(node, ["/images/"+this.item_id+"/"+node.media_variant+"/x"+node.offsetHeight+"."+node.media_format+"?"+u.randomString(4)]);
			}
		}

		// AUDIO preview
		div.addAudioPreview = function(node) {

			// TODO: add mp3 file "preview"-bg

			// make sure we have audioplayer available
			if(!page.audioplayer) {
				page.audioplayer = u.audioPlayer();
			}

			// if media format is available
			if(node.media_format) {

				this.addDeleteForm(node);

				node.bn_player.url = "/audios/"+this.item_id+"/"+node.media_variant+"/128."+node.media_format+"?"+u.randomString(4);
				node.bn_player.inputStarted = function(event) {
					u.e.kill(event);
					this.node.media_list._sorting_disabled = true;
				}
				node.bn_player.clicked = function(event) {
					if(!u.hc(this, "playing")) {
						page.audioplayer.loadAndPlay(this.url);
						u.ac(this, "playing");
					}
					else {
						page.audioplayer.stop();
						u.rc(this, "playing");
					}

					this.node.media_list._sorting_disabled = false;
				}
			}
		}

		// VIDEO preview
		div.addVideoPreview = function(node) {

			// TODO: add video file "preview"-bg
			this.addVideo(node);

			// if media format is available
			if(node.media_format) {

				this.addDeleteForm(node);

				node.bn_player.url = "/videos/"+this.item_id+"/"+node.media_variant+"/x"+node.offsetHeight+"."+node.media_format+"?"+u.randomString(4);
				node.bn_player.inputStarted = function(event) {
					u.e.kill(event);
					this.node.media_list._sorting_disabled = true;
				}
				node.bn_player.clicked = function(event) {
					if(!u.hc(this, "playing")) {
						this.node.video.loadAndPlay(this.url);
						u.ac(this, "playing");
					}
					else {
						this.node.video.stop();
						u.rc(this, "playing");
					}

					this.node.media_list._sorting_disabled = false;
				}
			}
		}



		// image list exists?
		if(!div.media_list) {
			u.ae(div, "ul", {"class":"mediae"});
		}

		// get media list nodes
		div.media_list.nodes = u.qsa("li.media", div.media_list);
		div.media_list.div = div;

		// inject delete forms in existing media list
		var i, node;
		for(i = 0; node = div.media_list.nodes[i]; i++) {

			node.div = div;
			node.media_list = div.media_list;
			node.image = u.qs("img", node);

			node.media_variant = u.cv(node, "variant");
			node.media_format = u.cv(node, "format");

			div.addPreview(node);

			div.adjustMediaName(node);

			// add update form for image name
			if(!u.hc(div, "variant") && div.update_name_url) {
				div.addUpdateNameForm(node);
			}


		}


		// sortable list
		if(!u.hc(div, "variant") && u.hc(div, "sortable") && div.media_list && div.save_order_url) {

			u.sortable(div.media_list, {"targets":"mediae", "draggables":"media"});
			div.media_list.picked = function() {}
			div.media_list.dropped = function() {
				var order = new Array();
				this.nodes = u.qsa("li.media", this);
				for(i = 0; node = this.nodes[i]; i++) {
					order.push(u.cv(node, "media_id"));
				}
				this.response = function(response) {
					// Notify of event
					page.notify(response);
				}
				u.request(this, this.div.save_order_url+"/"+this.div.item_id, {"method":"post", "params":"csrf-token=" + this.div.csrf_token + "&order=" + order.join(",")});
			}
		}
		else {
			u.rc(div, "sortable");
		}

	}
}

// default delete form
Util.Objects["deleteMedia"] = new function() {
	this.init = function(form) {
//		u.bug("deleteMedia init:" + u.nodeId(form));

		u.f.init(form);

		var bn_delete = u.qs("input.delete", form);
		if(bn_delete) {

			bn_delete.org_value = bn_delete.value;

			u.e.click(bn_delete);
			bn_delete.restore = function(event) {
				this.value = this.org_value;
				u.rc(this, "confirm");
			}

			bn_delete.inputStarted = function(event) {
				u.e.kill(event);
			}

			bn_delete.clicked = function(event) {
				u.e.kill(event);

				// first click
				if(!u.hc(this, "confirm")) {
					u.ac(this, "confirm");
					this.value = "Confirm";
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
								this.value = this.org_value;
								u.ac(this, "disabled");
							}
							else {
								// look for callback method on form
								if(typeof(this.form.deleted) == "function") {
									this.form.deleted();
								}
								else {
									location.reload();
								}
							}
						}
						else {
							this.restore();
						}
					}
					u.request(this, this.form.action, {"method":"post", "params" : u.f.getParams(this.form)});
				}
			}
		}

	}
}



// Add images form
Util.Objects["addMediaSingle"] = new function() {
	this.init = function(div) {

		div.form = u.qs("form.upload", div);
		div.form.div = div;

		// base media info
		div.item_id = u.cv(div, "item_id");
		div.media_variant = u.cv(div, "variant");
		div.media_format = u.cv(div, "format");
		div.media_file = u.qs("div.file", div);


		// get media size for image and video previews
		div.media_input = u.qs("input[type=file]", div.form);
		div.media_input_width = div.media_input.offsetWidth+10;
		div.media_input_height = Math.round(div.media_input_width / (div.media_input.offsetWidth/(div.media_input.offsetHeight+6)));



		// set media type for form
		// previews are different for different types
		// add preview based on current media format
		div.addPreview = function() {

			// remove existing image
			if(this.image) {
				this.image.parentNode.removeChild(this.image);
				this.image = null;
			}
			// remove existing video
			if(this.video) {
				this.video.parentNode.removeChild(this.video);
				this.video = null;
			}


			// set media type
			this.is_image = this.media_format.match(/jpg|png|gif/i);
			this.is_audio = this.media_format.match(/mp3|ogg/i);
			this.is_video = this.media_format.match(/mov|mp4|ogv|3gp/i);
			this.is_zip = this.media_format.match(/zip/i);
			this.is_pdf = this.media_format.match(/pdf/i);


			// file div available
			if(!this.media_file && this.media_format) {
				this.media_file = u.ae(this, "div", {"class":"file"});
			}

			if(this.media_file) {
				this.media_file.div = this;

				// media play button
				this.bn_player = u.qs("a", this.media_file);
				if(!this.bn_player) {
					this.bn_player = u.ie(this.media_file, "a");
				}
				this.bn_player.div = this;
				u.ce(this.bn_player);


				// media name
				this.media_name = u.qs("p", this.media_file);
				if(!this.media_name) {
					this.media_name = u.ae(this.media_file, "p");
				}
				this.media_name.div = this;
			}


			u.rc(this, "image|audio|video|pdf|zip");


			// inject previews
			if(this.is_audio) {
				u.ac(this, "audio");
				this.addAudioPreview();
			}
			else if(this.is_video) {
				u.ac(this, "video");
				this.addVideoPreview();
			}
			else if(this.is_image) {
				u.ac(this, "image");
				this.addImagePreview();
			}
			else if(this.is_pdf) {
				u.ac(this, "pdf");
				this.addPdfPreview();
			}
			else if(this.is_zip) {
				u.ac(this, "zip");
				this.addZipPreview();
			}
		}

		// add image container (if needed)
		div.addImage = function() {

			if(!this.image && this.media_format) {
				this.image = u.ae(this, "img");
				u.as(this.image, "width", div.media_input_width+"px");
				u.as(this.image, "height", div.media_input_height+"px");
			}
		}

		// add video container (if needed)
		div.addVideo = function() {

			if(!page.videoplayer) {
				page.videoplayer = u.videoPlayer();
			}

			if(!this.video && this.media_format) {
				this.video = u.ae(this, page.videoplayer);
				u.as(this.video, "width", div.media_input_width+"px");
				u.as(this.video, "height", div.media_input_height+"px");
			}
		}



		u.f.init(div.form);


		div.csrf_token = div.form.fields["csrf-token"].val();
		div.delete_url = div.getAttribute("data-media-delete");


		div.form.file_input = u.qs("input[type=file]", div.form);
		div.form.file_input.div = div;
		div.form.file_input.changed = function() {
			this.form.submit();
		}

		div.form.submitted = function() {

			u.ac(this.file_input.field, "loading");
			u.rc(this.file_input.field, "focus");

				// hide existing image while waiting for response
			if(this.div.image) {
				u.as(this.div.image, "display", "none");
			}
			if(this.div.video) {
				u.as(this.div.video, "display", "none");
			}
			if(this.div.media_file) {
				u.as(this.div.media_file, "display", "none");
			}


			var form_data = new FormData(this);
			this.response = function(response) {
				page.notify(response);

				// show existing previews (will be updated sutomatically if response is valid)
				if(this.div.image) {
					u.as(this.div.image, "display", "block");
				}
				if(this.div.video) {
					u.as(this.div.video, "display", "block");
				}
				if(this.div.media_file) {
					u.as(this.div.media_file, "display", "block");
				}


				// inject/update preview if everything went well
				if(response.cms_status == "success" && response.cms_object) {

					this.div.media_format = response.cms_object.format;

					u.rc(this.div, "format:[a-z]*");
					u.ac(this.div, "format:"+this.div.media_format);

					// inject preview
					this.div.addPreview();

					// update media name (preview makes sure name container is available)
					this.div.media_name.innerHTML = response.cms_object.name;
				}

				u.rc(this.file_input.field, "loading");
				this.file_input.val("");
			}
			u.request(this, this.action, {"method":"post", "params":form_data});
		}

		// add delete form
		div.addDeleteForm = function() {

			if(!this.delete_form) {
				this.delete_form = u.f.addForm(this, {"action":this.delete_url+"/"+this.item_id+"/"+this.media_variant, "class":"delete"});
				this.delete_form.div = this;
				u.ae(this.delete_form, "input", {"type":"hidden", "name":"csrf-token", "value":this.csrf_token});
				this.bn_delete = u.f.addAction(this.delete_form, {"class":"button delete"});

				this.delete_form.deleted = function() {

					if(this.div.video) {
						this.div.video.parentNode.removeChild(this.div.video);
						this.div.video = false;
					}
					if(this.div.image) {
						this.div.image.parentNode.removeChild(this.div.image);
						this.div.image = false;
					}
					if(this.div.media_file) {
						this.div.media_file.parentNode.removeChild(this.div.media_file);
						this.div.media_file = false;
					}

					// remove delete_form
					this.parentNode.removeChild(this);
					this.div.delete_form = null;
				}
				u.o.deleteMedia.init(this.delete_form);
			}
		}



		// PDF preview
		div.addPdfPreview = function() {

			// add image if it does not exist and format is available
			this.addImage();

			if(this.media_format) {

				this.addDeleteForm();
				this.image.src = "/images/0/pdf/x"+this.media_input_height+".png?"+u.randomString(4);
			}
		}

		// ZIP preview
		div.addZipPreview = function() {

			// add image if it does not exist and format is available
			this.addImage();

			if(this.media_format) {

				this.addDeleteForm();
				this.image.src = "/images/0/zip/x"+this.media_input_height+".png?"+u.randomString(4);
			}
		}

		// IMAGE preview
		div.addImagePreview = function() {

			// add image if it does not exist and format is available
			this.addImage();

			if(this.media_format) {

				this.addDeleteForm();
				this.image.src = "/images/"+this.item_id+"/"+this.media_variant+"/x"+this.media_input_height+"."+this.media_format+"?"+u.randomString(4);
			}
		}

		// AUDIO preview
		div.addAudioPreview = function() {

			// TODO: add mp3 file "preview"-bg

			// make sure we have audioplayer available
			if(!page.audioplayer) {
				page.audioplayer = u.audioPlayer();
			}

			// if media format is available
			if(this.media_format) {

				this.addDeleteForm();

				this.bn_player.url = "/audios/"+this.item_id+"/"+this.media_variant+"/128."+this.media_format+"?"+u.randomString(4);
				this.bn_player.clicked = function(event) {
					if(!u.hc(this, "playing")) {
						page.audioplayer.loadAndPlay(this.url);
						u.ac(this, "playing");
					}
					else {
						page.audioplayer.stop();
						u.rc(this, "playing");
					}
				}
			}
		}

		// VIDEO preview
		div.addVideoPreview = function() {

			// TODO: add video file "preview"-bg
			this.addVideo();

			// if media format is available
			if(this.media_format) {

				this.addDeleteForm();

				this.bn_player.url = "/videos/"+this.item_id+"/"+this.media_variant+"/x"+this.media_input_height+"."+this.media_format+"?"+u.randomString(4);
				this.bn_player.clicked = function(event) {
					if(!u.hc(this, "playing")) {
						this.div.video.loadAndPlay(this.url);
						u.ac(this, "playing");
					}
					else {
						this.div.video.stop();
						u.rc(this, "playing");
					}
				}
			}
		}


		// add start preview
		div.addPreview();

	}
}