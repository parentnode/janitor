	</div>

	<div id="navigation">
		<ul>
			<?= $HTML->link("Posts", "/janitor/post/list", array("wrapper" => "li.post")) ?>

			<?= $HTML->link("Navigations", "/janitor/admin/navigation/list", array("wrapper" => "li.navigation")) ?>
			<?= $HTML->link("Users", "/janitor/admin/user/list", array("wrapper" => "li.user")) ?>
			<?= $HTML->link("Tags", "/janitor/admin/tag/list", array("wrapper" => "li.tags")) ?>

			<?= $HTML->link("Profile", "/janitor/admin/profile", array("wrapper" => "li.profile")) ?>
		</ul>
	</div>

	<div id="footer">
		<ul class="servicenavigation">
			<li class="totop"><a href="#header">To top</a></li>
		</ul>

		<p class="copyright">Copyright 2016, parentNode.dk</p>
	</div>
</div>

</body>
</html>