<? $navigation = $this->navigation("main-janitor"); ?>
	</div>

	<div id="navigation">
		<ul class="navigation">
			<li class="content">
				<h3>Content</h3>
				<ul class="subjects">
<? if($navigation):
	foreach($navigation["nodes"] as $node): ?>
					<?= $HTML->navigationLink($node); ?>
<? 	endforeach;
endif; ?>
				</ul>
			</li>
<? if(defined("SITE_SHOP") && SITE_SHOP): ?>
			<li class="shop">
				<h3>Shop</h3>
				<ul class="subjects">
					<?= $HTML->link("Orders", "/janitor/admin/shop/order/list", array("wrapper" => "li.orders")) ?>
					<?= $HTML->link("Carts", "/janitor/admin/shop/cart/list", array("wrapper" => "li.carts")) ?>
					<?= $HTML->link("Payments", "/janitor/admin/shop/payment/list", array("wrapper" => "li.payments")) ?>
				</ul>
			</li>
<? endif; ?>
			<li class="site">
				<h3>Site</h3>
				<ul class="subjects">
					<?= $HTML->link("Navigations", "/janitor/admin/navigation/list", array("wrapper" => "li.navigation")) ?>
					<?= $HTML->link("Tags", "/janitor/admin/tag/list", array("wrapper" => "li.tags")) ?>
				</ul>
			</li>
			<li class="system">
				<h3>System</h3>
				<ul class="subjects">
					<?= $HTML->link("Log", "/janitor/admin/log/list", array("wrapper" => "li.logs")) ?>
					<?= $HTML->link("Cache", "/janitor/admin/system/cache", array("wrapper" => "li.cache")) ?>
					<?= $HTML->link("Setup", "/janitor/admin/setup", array("wrapper" => "li.setup")) ?>
				</ul>
			</li>
			<li class="users">
				<h3>Users</h3>
				<ul class="subjects">
					<?= $HTML->link("Users", "/janitor/admin/user/list", array("wrapper" => "li.user")) ?>
					<?= $HTML->link("Groups", "/janitor/admin/user/group/list", array("wrapper" => "li.usergroup")) ?>
<? if(defined("SITE_SHOP") && SITE_SHOP): ?>
					<?= $HTML->link("Members", "/janitor/admin/user/members/list", array("wrapper" => "li.members")) ?>
<? endif; ?>
					<?= $HTML->link("Newsletters", "/janitor/admin/user/newsletters/list", array("wrapper" => "li.newsletters")) ?>
					<?= $HTML->link("Profile", "/janitor/admin/profile", array("wrapper" => "li.profile")) ?>
				</ul>
			</li>
		</ul>
	</div>

	<div id="footer">
		<ul class="servicenavigation">
			<li class="copyright">Copyright 2017, parentNode.dk</li>
		</ul>
	</div>

</div>

</body>
</html>