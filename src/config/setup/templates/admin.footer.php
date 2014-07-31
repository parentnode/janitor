	</div>

	<div id="navigation">
		<ul>
			<?= $HTML->link("Posts", "/admin/post/list", array("wrapper" => "li.post")) ?>

			<?= $HTML->link("Users", "/admin/user/list", array("wrapper" => "li.user")) ?>
			<?= $HTML->link("Tags", "/admin/tag/list", array("wrapper" => "li.tags")) ?>
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