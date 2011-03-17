<?php
global $store;

if(!TOPSPIN_ARTIST_ID) $store->setError('No Topspin Artist ID entered.');
else if(!TOPSPIN_API_KEY) $store->setError('No Topspin API key entered.');
else if(!TOPSPIN_API_USERNAME) $store->setError('No Topspin API username entered.');

$action = (isset($_GET['action'])) ? $_GET['action'] : 'edit';

### Set Default Value
$storeData = array(
	'id' => 0,
	'name' => '',
	'slug' => '',
	'items_per_page' => 12,
	'grid_columns' => 3,
	'default_sorting' => 'alphabetical',
	'default_sorting_by' => 'tag',
	'items_order' => '',
	'featured_item' => 0,
	'offer_types' => $store->getOfferTypes(),
	'tags' => $store->getTagList()
);

### Retrieve Store Data
if(isset($_GET['id'])) {
	$storeData = array_merge($storeData,$store->getStore($_GET['id']));
}

switch($action) {
	case "delete":
		$res = $store->deleteStore($storeData['id']);
		if($res) {
			wp_delete_post($storeData['post_id'],0);
			$success = 'Store has been deleted. <a href="'.get_bloginfo('home').'/wp-admin/admin.php?page=topspin/page/settings_viewstores">View Stores</a>';
		}
		?>
        <div class="wrap">
        	<h2>Store Setup</h2>
			<?php if($success) : ?><div class="updated settings-error"><p><strong><?php echo $success; ?></strong></p></div><?php endif; ?>
        </div>
        <?php
		break; //end delete
	default:
		if($_SERVER['REQUEST_METHOD']=='POST') {
			### Parse $_POST Checkboxes
			$_POST['show_all_items'] = (isset($_POST['show_all_items'])) ? 1 : 0;

			$storeData = array_merge($storeData,$_POST);
			
			### Offer Types/Tags Ordering
			$storeData['offer_types'] = (isset($_POST['offer_types']) && count($_POST['offer_types'])) ? $storeData['offer_types'] : array();
			$storeData['tags'] = (isset($_POST['tags']) && count($_POST['tags'])) ? $storeData['tags'] : array();
			
			### Create A New Store
			if($storeData['id']==0) {
				$page = array(
					'post_title' => $storeData['name'],
					'post_name' => $storeData['slug'],
					'post_status' => 'publish',
					'post_type' => 'page',
					'post_content' => '[topspin_featured_item]&nbsp;[topspin_buy_buttons]'
				);

				$pageID = wp_insert_post($page);
				$newPage = get_post($pageID);
				$storeData['slug'] = $newPage->post_name;
				if($pageID) {
					$storeID = $store->createStore($storeData,$pageID);
					if($storeID) {
						$storeData['id'] = $storeID;
						$success = 'Store created. <a href="'.get_permalink($pageID).'" target="_blank">View Store</a>';
					}
				}
			}
			### Update An Existing Store
			else {
				$page = array(
					'post_title' => $storeData['name'],
					'post_name' => $storeData['slug'],
					'ID' => $storeData['post_id']
				);
				$pageID = wp_update_post($page);
				if($pageID) {
					$store->updateStore($storeData,$storeData['id']);
					$success = 'Store updated. <a href="'.get_permalink($pageID).'" target="_blank">View Store</a>';
				}
			}
		}
		if(isset($storeData['id']) && $storeData['id']) { $storeData = array_merge($storeData,$store->getStore($storeData['id'])); }
		?>

		<div class="wrap">
			<h2>Store Setup</h2>
		
			<?php if($success) : ?><div class="updated settings-error"><p><strong><?php echo $success; ?></strong></p></div><?php endif; ?>

			<form name="topspin_edit_form" method="post" action="<?=$_SERVER['REQUEST_URI'];?>">
			<input type="hidden" name="id" value="<?=(isset($storeData['id']))?$storeData['id']:0;?>" />
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="topspin_name">Store Name</label></th>
						<td>
							<input id="topspin_name" class="regular-text" type="text" value="<?=$storeData['name'];?>" name="name" />
							<span class="description"><?php if($storeData['id']): ?>Shortcode: [topspin_buy_buttons id=<?=$storeData['id'];?>]<?php endif; ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="topspin_slug">Store Slug</label></th>
						<td>
							<input id="topspin_slug" class="regular-text" type="text" value="<?=$storeData['slug'];?>" name="slug" />
							<span class="description">Leave field empty to automatically generate slug based on the store's name.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="topspin_items_per_page">Items Per Page</label></th>
						<td>
							<input id="topspin_items_per_page" class="regular-text" type="text" value="<?=$storeData['items_per_page'];?>" name="items_per_page" /><br/>
							<input id="topspin_show_all_items" name="show_all_items" type="checkbox" value="1" <?=($storeData['show_all_items'])?'checked="checked"':'';?> /> <label for="topspin_show_all_items">Show all items on one page</label>
							<span class="description"></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="topspin_grid_columns">Number of Columns</label></th>
						<td>
							<select id="topspin_grid_columns" name="grid_columns">
								<?php for($i=1;$i<=5;$i++) : ?>
								<option value="<?=$i;?>" <?=($i==$storeData['grid_columns'])?'selected="selected"':'';?>><?=$i;?></option>
								<?php endfor; ?>
							</select>
							<span class="description">Specify how many columns to display.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="topspin_default_sorting">Default Product Sorting</label></th>
						<td>
							<select id="topspin_default_sorting" name="default_sorting">
								<option value="alphabetical" <?=($storeData['default_sorting']=='alphabetical')?'selected="selected"':'';?>>Alphabetical</option>
								<option value="chronological" <?=($storeData['default_sorting']=='chronological')?'selected="selected"':'';?>>Chronological</option>
							</select>
							<span class="description"></span>
						</td>
					</tr>
                    <tr valign="top">
                    	<th scope="row"><labe for="topspin_default_sorting_by">Sort By</label></th>
                        <td>
                        	<select id="topspin_default_sorting_by" name="default_sorting_by">
                            	<?php $sortByTypes = $store->getSortByTypes();
								foreach($sortByTypes as $key=>$name) : ?>
                                <option value="<?=$key;?>" <?=($key==$storeData['default_sorting_by'])?'selected="selected"':'';?>><?=$name;?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
				</tbody>
			</table>
			
			<hr/>
			
			<h3>Offer Types</h3>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="topspin_offer_types">Show/Order Offer Types:</label></th>
						<td>
							<ul id="topspin_offer_types" class="group-sortable">
								<?php
								foreach($storeData['offer_types'] as $type) : ?>
								<li id="type-<?=$type['type'];?>" class="menu-item">
									<input type="checkbox" name="offer_types[]" value="<?=$type['type'];?>" <?=($type['status'])?'checked="checked"':'';?> />
									&nbsp;&nbsp;&nbsp;<span class="item-title"><?=$type['name'];?></span>
								</li>
								<?php endforeach; ?>
							</ul>
                            <span class="description">Check to only show specific offer types.</span>
						</td>
					</tr>
				</tbody>
			</table>
            
            <hr/>
            
            <h3>Tags</h3>
            <table class="form-table">
            	<tbody>
					<tr valign="top">
						<th scope="row"><label for="topspin_tags">Show/Order Tags:</label></th>
						<td>
							<ul id="topspin_tags" class="group-sortable">
								<?php
								foreach($storeData['tags'] as $tag) : ?>
								<li id="tag-<?=$tag['name'];?>" class="menu-item">
									<input type="checkbox" name="tags[]" value="<?=$tag['name'];?>" <?=($tag['status'])?'checked="checked"':'';?> />
									&nbsp;&nbsp;&nbsp;<span class="item-title"><?=ucfirst($tag['name']);?></span>
								</li>
								<?php endforeach; ?>
							</ul>
                            <span class="description">Check to only show specific tags.</span>
						</td>
					</tr>
				</tbody>
			</table>
            
            <hr/>
            
            <h3>Featured Item</h3>

            <table class="form-table">
				<tbody>
                	<tr>
						<th scope="row"><label for="topspin_featured_item">Featured Item</label></th>
						<td>
                        	<select id="topspin_featured_item" name="featured_item">
                            	<option value="0">None</option>
								<?php
								$sortedItems = $store->getStoreItems($storeData['id']);
                                foreach($sortedItems as $item) : ?>
                                <option value="<?=$item['id'];?>" <?=($item['id']==$storeData['featured_item'])?'selected="selected"':'';?>><?=$item['name'];?></option>
                                <?php endforeach; ?>
							</select>
                            <span class="description"><?php if($storeData['id']): ?>Shortcode: [topspin_featured_item id=<?=$storeData['id'];?>]<?php endif; ?></span>
						</td>
					</tr>
				</tbody>
            </table>

			<div id="topspin_manual_sorting">
	            <hr/>
                <h3>Manual Item Selection</h3>
                <input type="hidden" name="items_order" value="" />
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <td colspan="2">
                                <ul id="topspin-manual-item-sorting" class="item-sortable">
                                <?php
                                $sortedItems = $store->getStoreItems($storeData['id']);
                                if(count($sortedItems)) : ?>
                                    <?php foreach($sortedItems as $item) : ?>
                                    <?php $item['is_public'] = (strlen($storeData['items_order'])) ? $item['is_public'] : 1; ?>
                                    <li id="item-<?=$item['id'];?>:<?=($item['is_public'])?$item['is_public']:0;?>">
                                    	<div class="item-offer-type"><?=$item['offer_type_name'];?></div>
                                        <div class="item-canvas <?=($item['is_public'])?'':'faded';?>">
                                            <?php
                                            $campaign = unserialize($item['campaign']);
                                            $product = $campaign->product;
                                            ?>
                                            <img src="<?=$item['poster_image'];?>" width="150" alt="<?=$item['name'];?>" /><br/>
                                            <?=$item['name'];?>
                                        </div>
                                        <div class="item-hide">Hide</div>
                                    </li>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                	<li>There are no items to be displayed.</li>
                                <?php endif; ?>
                                </ul>
                            </td>
                        </tr>
                    </tbody>
                </table>
			</div>
		
			<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
			</form>
		
		</div>

		<script type="text/javascript" language="javascript">
		
		var updateItemsOrder = function() {
			var aOrder = new Array();
			var containers = jQuery('ul.item-sortable > li');
			containers.each(function() {
				var itemID = jQuery(this).attr('id');
				itemID = itemID.split('-');
				aOrder.push(itemID[1]);
			});
			jQuery('input[name=items_order]').val(aOrder.join(','));
		};
		
		var checkItemDisplayStatus = function() {
			jQuery('ul.item-sortable li .item-canvas').each(function() {
				if(jQuery(this).hasClass('faded')) {
					jQuery(this).fadeTo(200,.3);
					jQuery(this).next().html('Show');
				}
				else {
					jQuery(this).fadeTo(200,1);
					jQuery(this).next().html('Hide');
				}
			});
		}
		
		//Retrieves a comma-delimited string of the checked offer types
		var getCheckedOfferTypes = function() {
			var offer_types = '';
			jQuery.each(jQuery("input[name='offer_types[]']:checked"),function() {
				offer_types += (offer_types?',':'') + jQuery(this).val();
			});
			return offer_types;
		};
		
		//Retrieves a comma-delimited string of the checked tags
		var getCheckedTags = function() {
			var tags = '';
			jQuery.each(jQuery("input[name='tags[]']:checked"),function() {
				tags += (tags?',':'') + jQuery(this).val();
			});
			return tags;
		};
		
		// Updates the Items Display
		var updateItemDisplay = function() {
			// Begin Disable AJAX Controls
			var featuredOptions = jQuery('#topspin_featured_item');
			jQuery("input[name='offer_types[]'], input[name='tags[]']").attr('disabled','disabled');
			featuredOptions.attr('disabled','disabled');

			var manualItems = jQuery('#topspin-manual-item-sorting');
			
			// End Disable AJAX Controls
			var offer_types = getCheckedOfferTypes();
			var tags = getCheckedTags();
			jQuery.ajax({
				url : ajaxurl,
				data : {
					action : 'topspin_get_items',
					offer_types : offer_types,
					tags : tags
				},
				success : function(ret) {
					var json = jQuery.parseJSON(ret);
					
					//Featured Items Empty
					featuredOptions.empty();
					var selectedFeaturedItem = <?=$storeData['featured_item'];?>;
					var emptyOption = jQuery('<option />');
					emptyOption
						.val(0)
						.html('None')
						.appendTo(featuredOptions);

					//Manual Items Empty
					manualItems.empty();

					jQuery(json).each(function(key,data) {
						//Featured Items Append
						var option = jQuery('<option />');
						option
							.val(data.id)
							.html(data.name)
							.appendTo(featuredOptions);
						if(selectedFeaturedItem==data.id) { option.attr('selected','selected'); }

						//Manual Items Append
						var li = jQuery('<li />');
						li
							.attr('id','item-'+data.id+':1');
						var offertype = jQuery('<div />');
						offertype
							.addClass('item-offer-type')
							.html(data.offer_type_name)
							.appendTo(li);
						var canvas = jQuery('<div />');
						canvas
							.addClass('item-canvas')
							.appendTo(li);
						var img = jQuery('<img />')
						img
							.attr('src',data.poster_image)
							.attr('width',150)
							.attr('alt',data.name)
							.appendTo(canvas);
						canvas.append('<br/>'+data.name);
						var hide = jQuery('<div />')
						hide
							.addClass('item-hide')
							.html('Hide')
							.appendTo(li);
						li.appendTo(manualItems);
					});

				},
				complete : function() {
					// Renabled AJAX Controls
					jQuery("input[name='offer_types[]'], input[name='tags[]']").removeAttr('disabled');
					featuredOptions.removeAttr('disabled');
					updateItemsOrder();
				}
			});
		};
		
		var toggleManualSorting = function() {
			if(jQuery('option:selected',this).val()=='manual') {
				jQuery('#topspin_manual_sorting').fadeIn();
			}
			else {
				jQuery('#topspin_manual_sorting').fadeOut();
			}
		};

		jQuery(function($) {

			//AJAX Items Update
			$('select#topspin_default_sorting_by').bind('change',updateItemDisplay);
			$("input[name='tags[]'], input[name='offer_types[]']").bind('click',updateItemDisplay);

			//AJAX Manual Sorting Toggle
			$('select#topspin_default_sorting_by').bind('change',toggleManualSorting);

			//Group Sorting
			$('ul.group-sortable').sortable();

			//Item Sorting
			$('ul.item-sortable').sortable({
				stop : function(event,ui) {
					updateItemsOrder();
				}
			});
		
			//Item Hiding
			$('ul.item-sortable li .item-hide').live('click',function() {
				//"item-12345:0"	Hidden
				//"item-12345:1"	Shown
				var parentObject = $(this).parent().attr('id');
				var itemObject = parentObject.split('-');
				var idObject = itemObject[1].split(':');
				//Show Item
				if($(this).prev().hasClass('faded')) {
					$(this).prev().fadeTo(200,1);
					$(this).html('Hide');
					$(this).parent().attr('id',itemObject[0]+'-'+idObject[0]+':1');
				}
				//Hide Item
				else {
					$(this).prev().fadeTo(200,.3);
					$(this).html('Show');
					$(this).parent().attr('id',itemObject[0]+'-'+idObject[0]+':0');
				}
				$(this).prev().toggleClass('faded');
				updateItemsOrder();
			});
			
			checkItemDisplayStatus();
			updateItemsOrder();
			<?php if($storeData['id'] && $storeData['default_sorting_by']=='manual') : ?>
				$('#topspin_manual_sorting').fadeIn();
			<?php else : ?>
			$('select#topspin_default_sorting_by').change();
			<?php endif; ?>

		});
		</script>
        <?php
		break; //end default
}
?>