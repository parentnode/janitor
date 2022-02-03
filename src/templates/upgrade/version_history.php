<?php
global $model;
global $upgrade_model;


$query = new Query();
$query->sql("SELECT TABLE_NAME AS tables FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '".SITE_DB."' AND TABLE_NAME LIKE '%_versions'");
$tables = $query->results("tables");


?>
<div class="scene i:scene defaultList">
	<h1>Version history overview</h1>

	<p>Version history may grow over time – and it might also be ok to delete old versions with enough time passed.</p>
	<p>You decide.</p>
	
	<p class="note">This is a preliminary version cleaner – ready for onwards refinement :-)</p>

	<h2>Archived versions</h2>
	<? if($tables): ?>

	<div class="all_items">
		<ul class="items tables">
			<? foreach($tables as $table):
			$sql = "SELECT count(*) AS row_count FROM ".SITE_DB.".$table";
			$query->sql($sql);
			$row_count = $query->result(0, "row_count");

			$sql = "SELECT count(*) AS row_count FROM ".SITE_DB.".$table WHERE versioned < '".date("Y-m-d", strtotime("-1 year"))."'";
			$query->sql($sql);
			$row_count_year_old = $query->result(0, "row_count");

			$sql = "SELECT count(*) AS row_count FROM ".SITE_DB.".$table WHERE versioned < '".date("Y-m-d", strtotime("-1 month"))."'";
			$query->sql($sql);
			$row_count_month_old = $query->result(0, "row_count");

			?>
			<li class="item table">
				<h3>
					<span class="table"><?= $table ?></span> <br />
					<span class="total_rows"><?= $row_count ?> total rows</span> <br />
					<span class="month_old"><?= $row_count_month_old ?> rows are more than one month old</span> <br />
					<span class="year_old"><?= $row_count_year_old ?> rows are more than one year old</span>
				</h3>
			
				<ul class="actions">
					<?= $HTML->oneButtonForm("Delete ALL", "upgrade/deleteAllVersionHistoryForTable", [
						"inputs" => [
							"table" => $table
						],
						"confirm-value" => "Are you sure?",
						"wrapper" => "li.delete_all",
						"success-location" => $this->url
					]) ?>
					<?= $HTML->oneButtonForm("Delete when older than one month", "upgrade/deleteOneMonthOldVersionHistoryForTable", [
						"inputs" => [
							"table" => $table
						],
						"confirm-value" => "Are you sure?",
						"wrapper" => "li.delete_month",
						"success-location" => $this->url
					]) ?>
					<?= $HTML->oneButtonForm("Delete when older than one year", "upgrade/deleteOneYearOldVersionHistoryForTable", [
						"inputs" => [
							"table" => $table
						],
						"confirm-value" => "Are you sure?",
						"wrapper" => "li.delete_year",
						"success-location" => $this->url
					]) ?>
				</ul>
			</li>
			<? endforeach; ?>
		</ul>
	</div>

	<? else: ?>

	<p>No versions tables in this project</p>

	<? endif; ?>

</div>