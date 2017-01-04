<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>

<?php echo $this->getRenderedForm(); ?>

<div class="shipping-content inline-content">
	<div class="row-fluid">

		<div class="span4">

			<div class="hero-unit">
				<h2>Need help in setting up shipping methods ?</h2>
				<p class="lead">
					Check our comprehensive user guide.
				</p>
				<a target="_blank" class="btn btn-large btn-warning" href="<?php echo J2Store::buildHelpLink('support/user-guide.html', 'shipping');  ?>">User guide</a>
				<br />
				<p class="lead">
					Shipping is not working?  Check the troubleshooting guide
				</p>
				<a target="_blank" class="btn btn-large btn-danger" href="<?php echo J2Store::buildHelpLink('support/user-guide/troubleshooting-shipping-methods.html', 'shipping'); ?>">Troubleshooting Guide</a>
				<a target="_blank" class="btn btn-large btn-info" href="<?php echo  J2Store::buildHelpLink('support.html', 'shipping'); ?>">Support center</a>

			</div>

		</div>
		<div class="span5">
			<div class="hero-unit">
				<h2>Need more shipping methods? Check our extensions directory</h2>
				<p class="lead">
					J2Store has integrations 10+ shipping carriers.
					<br />
					Find more at our extensions directory
				</p>
				<a target="_blank" class="btn btn-large btn-success" href="<?php echo  J2Store::buildHelpLink('extensions/shipping-plugins.html', 'shipping'); ?>">Get more shipping plugins </a>
			</div>
		</div>

	</div>



</div>