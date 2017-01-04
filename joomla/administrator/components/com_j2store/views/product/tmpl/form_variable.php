<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;
?>
	<div class="row-fluid">
		   <div class="span12">

		   <div class="alert alert-block alert-info">
				<h4><?php echo JText::_('J2STORE_QUICK_HELP'); ?></h4>
				<?php echo JText::_('J2STORE_VARIANT_PRODUCT_HELP_TEXT'); ?>
			</div>

	          <div class="tabbable tabs-left">
                <ul class="nav nav-tabs">
                  <li class="active">
                  	<a href="#generalTab" data-toggle="tab"><i class="fa fa-home"></i>
                  		 <?php echo JText::_('J2STORE_PRODUCT_TAB_GENERAL'); ?>
                  	</a>
                  </li>
				  <li><a href="#imagesTab" data-toggle="tab"><i class="fa fa-file-image-o"></i> <?php echo JText::_('J2STORE_PRODUCT_TAB_IMAGES'); ?></a></li>
                  <li><a href="#variantsTab" data-toggle="tab"><i class="fa fa-sitemap"></i> <?php echo JText::_('J2STORE_PRODUCT_TAB_VARIANTS'); ?></a></li>
                  <li><a href="#filterTab" data-toggle="tab"><i class="fa fa-filter"></i> <?php echo JText::_('J2STORE_PRODUCT_TAB_FILTER'); ?></a></li>
                  <li><a href="#relationsTab" data-toggle="tab"><i class="fa fa-group"></i> <?php echo JText::_('J2STORE_PRODUCT_TAB_RELATIONS'); ?></a></li>
                  <li><a href="#appsTab" data-toggle="tab"><i class="fa fa-group"></i> <?php echo JText::_('J2STORE_PRODUCT_TAB_APPS'); ?></a></li>

                </ul>
				<!-- / Tab content starts -->
                <div class="tab-content">
                  <div class="tab-pane active" id="generalTab">
                  <input type="hidden" name="<?php echo $this->form_prefix.'[j2store_variant_id]'; ?>" value="<?php echo $this->item->variant->j2store_variant_id; ?>" />
                	<?php echo $this->loadTemplate('variable_general');?>
                  </div>
                  <div class="tab-pane" id="imagesTab">
						<?php echo $this->loadTemplate('images');?>
                  </div>
                  
                  <div class="tab-pane" id="variantsTab">
					<?php echo $this->loadTemplate('variable_options');?>
					<?php echo $this->loadTemplate('variants');?>
                  </div>
                  <div class="tab-pane" id="filterTab">
						<?php echo $this->loadTemplate('filters');?>
                  </div>
                  <div class="tab-pane" id="relationsTab">
						<?php echo $this->loadTemplate('relations');?>
                  </div>
                  <div class="tab-pane" id="appsTab">
						<?php echo $this->loadTemplate('apps');?>
                  </div>
                </div>
                <!-- / Tab content Ends -->
              </div> <!-- /tabbable -->
          </div>
          </div>
