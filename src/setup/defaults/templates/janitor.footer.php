	</div>

	<div id="navigation">
		<ul>
			<?= $HTML->link("Posts", "/janitor/post/list", array("wrapper" => "li.post")) ?>

			<?= $HTML->link("Users", "/janitor/admin/user/list", array("wrapper" => "li.user")) ?>
			<?= $HTML->link("Tags", "/janitor/admin/tag/list", array("wrapper" => "li.tags")) ?>
		</ul>
	</div>

	<div id="footer">
		<ul class="servicenavigation">
			<li class="copyright">Janitor, Manipulator, Modulator - parentNode - Copyright 2014</li>
		</ul>
	</div>
</div>

</body>
</html>