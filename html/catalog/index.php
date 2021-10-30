<?php

$body = <<<EOT
<div class="container">
	<div class="row">
		<div class="col-sm">
			<h5>Catalog</h5>
		</div>
		<div class="col-sm mb-2">
			<div class="input-group">
				<input autocomplete="off" class="form-control" type="text" id="keyword_input" placeholder="Search">
				<div class="input-group-append">
					<button disabled type="button" class="btn btn-secondary" data-toggle="modal" data-target="#CatalogFiltersModal">Filters</button>
					<button onclick="catalogPage(1, assetTypeId, $('#keyword_input').val())" class="btn btn-danger">Search</button>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-sm mb-2">
			<div class="card">
				<div class="card-body">
					<div>
						<h6 class="text-center">Item Type</h6>
					</div>
					<div class="catalog-itemstypea" style="color:red;cursor: pointer;">
						<a onclick="catalogPage(1,8)"><p id="type8">Hats</p></a>
						<a onclick="catalogPage(1,2)"><p id="type2">T-Shirts</p></a>
						<a onclick="catalogPage(1,11)"><p id="type11">Shirts</p></a>
						<a onclick="catalogPage(1,12)"><p id="type12">Pants</p></a>
						<a onclick="catalogPage(1,18)"><p id="type18">Faces</p></a>
						<a onclick="catalogPage(1,19)"><p id="type19">Gears</p></a>
						<a onclick="catalogPage(1,17)"><p id="type17">Heads</p></a>
						<a onclick="catalogPage(1,32)"><p id="type32">Packages</p></a>
						<a onclick="catalogPage(1,3)"><p id="type3">Audios</p></a>
						<a onclick="catalogPage(1,10)"><p id="type10">Models</p></a>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-9">
			<div class="card">
				<div class="card-body">
					<div class="row">
						<div class="col-sm">
							<div class="catalog-container">
								<ul id = "catalogitems">
									
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="text-center mt-2">
				<div id = "catalogpages" class="btn-group">

				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="CatalogFiltersModal" tabindex="-1" role="dialog" aria-labelledby="CatalogFiltersLabel" aria-hidden="true">
	<div class="modal-dialog" role="document" style="max-width:40rem!important;">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="exampleModalLabel">Filters</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<h6>Sort by</h6>
				<select class="form-control" name="sortbycatalog">
					<option value="1">Recently Updated</option>
					<option value="2">Most Popular</option>
					<option value="3">Price: High to Low</option>
					<option value="4">Price: Low to High</option>
				</select>
				<hr>
				<div class="row">
					<div class="col-sm">
						<h6>Minimum Price</h6>
						<input type="number" min="0" name="minimumprice" value="" class="form-control">
					</div>
					<div class="col-sm">
						<h6>Maximum Price</h6>
						<input type="number" min="0" name="maximumprice" value="" class="form-control">
					</div>
				</div>
				<hr>
				<h6>Visibility Options</h6>
				<select class="form-control" name="visiblebycatalog">
					<option value="1">Show All</option>
					<option value="2">Show Only Limiteds</option>
					<option value="3">Show Only Non-Limiteds</option>
				</select>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger">Update</button>
			</div>
		</div>
	</div>
</div>
<script>
var assetTypeId = 8;
function catalogPage(page, assettype, keyword="") {
	if (assettype) {
		$("#type" + assetTypeId).removeClass("focuscatalog")
		assetTypeId = assettype;
		$("#type" + assetTypeId).addClass("focuscatalog");
	}
	getCatalogPage(assetTypeId, page, 12, keyword);
}    
function getCatalogPage(assettype, page, limit, keyword) {
	var html = '<li>';
	html += '<div class="catalog-card text-center">';
	html += '<a href="view?id={id}">';
	html += '<div class="catalog-card-img">';
	html += '<img style="width:7.6rem;height:7.6rem;" class="img-fluid" src="{thumbnail}">';
	html += '</div>';
	html += '<p class="no-overflow">{name}</p>';
	html += '<p>By: {creatorName}</p>';
	html += '<p><img src="/alphaland/cdn/imgs/alphabux-grey-1024.png"> {price}</p>';
	html += '</a>';
	html += '</div>';
	html += '</li>';
            
	multiPageHelper("catalogPage", "https://api.alphaland.cc/catalog/items", "https://api.alphaland.cc/logo", "#catalogitems", "#catalogpages", html, page, limit, keyword, "No results", "&assetTypeId="+assettype);
}

$('#keyword_input').keypress(function(event) {
    if (event.keyCode == 13 || event.which == 13) {
		catalogPage(1, assetTypeId, $('#keyword_input').val());
    }
});

catalogPage(1,assetTypeId);
</script>
EOT;
pageHandler();
$ph->pageTitle("Catalog");
$ph->body = $body;
$ph->output();