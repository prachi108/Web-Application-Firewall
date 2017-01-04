<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
$image_path = JUri::root();


?>
<div class="j2store-product-images j2store-product-images-<?php echo $this->product->j2store_product_id; ?>">

	<?php if($this->params->get('show_thumbnail_image', 0)):?>
	<div class="j2store-thumbnail-image">
		   <?php
	      $main_image="";
	      if(isset($this->product->thumb_image) && $this->product->thumb_image){
	      	$thumb_image = $this->product->thumb_image;
	      }
	      ?>
		   <?php if(isset($thumb_image) &&  JFile::exists(JPATH_SITE.'/'.$thumb_image)):?>

		   <?php if($this->params->get('category_link_image_to_product',0) == 1):?>
		   	<a class="j2store-product-image-link" href="<?php echo  $this->product->product_view_url;?>">
		   <?php endif;?>
		   	<span>
		   		<img  alt="<?php echo $this->product->product_name ;?>" class="j2store-product-thumb-image-<?php echo $this->product->j2store_product_id; ?>"  src="<?php echo $image_path.$thumb_image;?>" />
		  	 </span>
		  	 <?php if($this->params->get('category_link_image_to_product',0) == 1):?>
		  	 </a>
		   <?php endif;?>
		   <?php elseif(!empty($this->product->thumb_image)):?>
			   <?php echo J2Store::product()->displayImage($this->product,array('type'=>'ItemThumb','params' => $this->params)); ?>
		   <?php endif; ?>
	</div>
	 <?php endif; ?>

	<?php if($this->params->get('show_main_image', 0)):?>
	<div class="j2store-mainimage">
		   <?php
	      $main_image="";
	      if(isset($this->product->main_image) && $this->product->main_image){
	      	$main_image = $this->product->main_image;
	      }
	      ?>
		   <?php if($main_image &&  JFile::exists(JPATH_SITE.'/'.$main_image)):?>
		   <?php $class= $this->params->get('item_enable_image_zoom', 1) ? 'zoom': 'nozoom'; ?>

 			<?php if($this->params->get('category_link_image_to_product',0) == 1):?>
		   	<a class="j2store-product-image-link" href="<?php echo  $this->product->product_view_url;?>">
		   <?php endif;?>
		   <span class='<?php echo $class; ?>' id='j2store-item-main-image-<?php echo $this->product->j2store_product_id; ?>'>
		  	 <img alt="<?php echo $this->product->product_name ;?>" class="j2store-product-main-image-<?php echo $this->product->j2store_product_id; ?>"  src="<?php echo $image_path.$main_image;?>" />
		  	 </span>
		  	 <?php if($this->params->get('category_link_image_to_product',0) == 1):?>
		  	 	</a>
		   	<?php endif;?>
			   <script type="text/javascript">
				   var main_image="<?php echo $image_path.$main_image ;?>";
				   j2store.jQuery(document).ready(function(){
					   var enable_zoom = <?php echo $this->params->get('item_enable_image_zoom', 1);?>;
					   if(enable_zoom){
						   j2store.jQuery('#j2store-item-main-image-<?php echo $this->product->j2store_product_id; ?>').zoom();
					   }
				   });
			   </script>
		   <?php elseif(!empty($this->product->main_image)):?>
			   <?php echo J2Store::product()->displayImage($this->product,array('type'=>'ItemMain','params' => $this->params)); ?>
		   <?php endif; ?>
	</div>
	 <?php endif; ?>

	 <?php if($this->params->get('show_additional_image') && isset($this->product->additional_images) && !empty($this->product->additional_images)):?>
	 	<?php
	 		$additional_images = json_decode($this->product->additional_images);
	 		$additional_images  = array_filter((array)$additional_images);
	 		if($additional_images):
	 	?>
			<div class="j2store-product-additional-images">

				<ul class="additional-image-list">
					<?php
						$additional_images = json_decode($this->product->additional_images);
						if(isset($additional_images) && count($additional_images)):
						foreach($additional_images as $key => $image):?>
						<?php
						if(JFile::exists(JPATH_SITE.'/'.$image)):
							$image_src = $image_path.$image;
							 	?>
						<li>
							<img onmouseover="setMainPreview('addimage-<?php echo $this->product->j2store_product_id; ?>-<?php echo $key;?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
								 onclick="setMainPreview('addimage-<?php echo $this->product->j2store_product_id; ?>-<?php echo $key;?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
								 id="addimage-<?php echo $this->product->j2store_product_id; ?>-<?php echo $key;?>"
								 class="j2store-item-additionalimage-preview j2store-img-responsive"
								 src="<?php echo $image_src;?>"
								 alt="<?php echo $this->product->product_name; ?>"
								 />
						</li>
							<?php elseif(!empty($image)):?>
							<?php echo J2Store::product()->displayImage($this->product,array('type'=>'ViewAdditional','params' => $this->params,'key'=>$key,'image' => $image)); ?>
					<?php endif;?>
					<?php endforeach;?>
						<?php if($main_image &&  JFile::exists(JPATH_SITE.'/'.$main_image)):?>
						<li>
						 <img onmouseover="setMainPreview('additial-main-image-<?php echo $this->product->j2store_product_id; ?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
								  onclick="setMainPreview('additial-main-image-<?php echo $this->product->j2store_product_id; ?>', <?php echo $this->product->j2store_product_id; ?>, <?php echo $this->params->get('item_enable_image_zoom', 1); ?>, 'inner')"
								  id="additial-main-image-<?php echo $this->product->j2store_product_id; ?>"
							 	  alt="<?php echo $this->product->product_name ;?>"
							 	  class="j2store-item-additionalimage-preview j2store-img-responsive additional-mainimage"
							 	  src="<?php echo $image_path.$main_image;?>"
							 	/>
						</li>
						<?php elseif (!empty($this->product->main_image)):?>
							<?php echo J2Store::product()->displayImage($this->product,array('type'=>'AdditionalMain','params' => $this->params)); ?>
						<?php endif;?>
					<?php endif;?>
				</ul>
			</div>
			<?php endif;?>
		<?php endif;?>
</div>
<?php if ($this->params->get('item_enable_image_zoom', 1)) : ?>
	<script>
		j2store.jQuery(document).ready(function(){
			j2store.jQuery( 'body' ).on( 'after_doAjaxFilter', function( e, product, response ){
				j2store.jQuery('img.zoomImg').remove();
				j2store.jQuery('#j2store-item-main-image-<?php echo $this->product->j2store_product_id; ?>').zoom();
			});
		});
	</script>
<?php endif; ?>
