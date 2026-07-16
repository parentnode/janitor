<? $navigation = navigation()->get("main-janitor"); ?>
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
					<?= $HTML->link("Orders", "/janitor/shop/order/list", array("wrapper" => "li.orders")) ?>
					<?= $HTML->link("Carts", "/janitor/shop/cart/list", array("wrapper" => "li.carts")) ?>
					<?= $HTML->link("Payments", "/janitor/shop/payment/list", array("wrapper" => "li.payments")) ?>
				</ul>
			</li>
<? endif; ?>
<? if(defined("SITE_MEMBERS") && SITE_MEMBERS): ?>
			<li class="members">
				<h3>Members</h3>
				<ul class="subjects">
					<?= $HTML->link("Memberships", "/janitor/membership/list", array("wrapper" => "li.membership")) ?>
					<?= $HTML->link("Members", "/janitor/member/list", array("wrapper" => "li.members")) ?>
				</ul>
			</li>
<? endif; ?>
			<li class="site">
				<h3>Site</h3>
				<ul class="subjects">
					<?= $HTML->link("Navigations", "/janitor/admin/navigation/list", array("wrapper" => "li.navigation")) ?>
					<?= $HTML->link("Tags", "/janitor/admin/tag/list", array("wrapper" => "li.tags")) ?>
					<?= $HTML->link("Taglists", "/janitor/admin/taglist/list", array("wrapper" => "li.taglists")) ?>
				</ul>
			</li>
			<li class="system">
				<h3>System</h3>
				<ul class="subjects">
					<?= $HTML->link("Countries", "/janitor/admin/system/countries", array("wrapper" => "li.countries")) ?>
					<?= $HTML->link("Languages", "/janitor/admin/system/languages", array("wrapper" => "li.languages")) ?>
					<?= $HTML->link("Currencies", "/janitor/admin/system/currencies", array("wrapper" => "li.currencies")) ?>
					<?= $HTML->link("Vatrates", "/janitor/admin/system/vatrates", array("wrapper" => "li.vatrates")) ?>
					<?= $HTML->link("Payment methodss", "/janitor/admin/system/payment_methods", array("wrapper" => "li.payment_methods")) ?>
					<?= $HTML->link("Subscription methodss", "/janitor/admin/system/subscription_methods", array("wrapper" => "li.subscription_methods")) ?>
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
					<?= $HTML->link("Messages", "/janitor/admin/message", array("wrapper" => "li.message")) ?>
					<?= $HTML->link("Profile", "/janitor/admin/profile", array("wrapper" => "li.profile")) ?>
				</ul>
			</li>
		</ul>
	</div>

	<div id="footer">
		<ul class="servicenavigation">
			<li class="copyright">Copyright 2026, parentNode.dk</li>
		</ul>
	</div>

</div>

</body>
</html>