Util.Objects["pull"] = new function() {
	this.init = function(scene) {
		console.log("pull init")

		scene.p_pull_result = u.qs("p.pull_result", scene);
		scene.bn_pull = u.qs("li.pull", scene);

		if(scene.p_pull_result && scene.bn_pull) {
			scene.bn_pull.scene = scene;

			console.log(scene.bn_pull);
			
			scene.bn_pull.confirmed = function(response) {
				this.scene.p_pull_result.innerHTML = response.cms_object;
				console.log("callback received");
			}

			scene.bn_pull.confirmedError = function() {
				this.scene.p_pull_result.innerHTML = "Source code could not be pulled. Contact system admin.";
				u.ac(this.scene.p_pull_result, "system_error");
				this.parentNode.removeChild(this);
				console.log("error callback received");
			}
		}

	}

}
