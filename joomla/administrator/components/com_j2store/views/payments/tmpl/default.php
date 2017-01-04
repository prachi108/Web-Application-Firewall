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

<div class="payment-content inline-content">
	<div class="row-fluid">

		<div class="span4">

			<div class="hero-unit">
				<h2>Need help in setting up payment methods ?</h2>
				<p class="lead">
					Check our comprehensive user guide
				</p>
				<a target="_blank" class="btn btn-large btn-warning" href="<?php echo J2Store::buildHelpLink('support/user-guide.html', 'gateways'); ?>">User guide</a>
				<a target="_blank" class="btn btn-large btn-info" href="<?php echo J2Store::buildHelpLink('support.html', 'gateways'); ?>">Support center</a>
			</div>

		</div>
		<div class="span5">
			<div class="hero-unit">
				<h2>Looking for more payment options? Check our extensions directory</h2>
				<p class="lead">
					J2Store is integrated with 65+ payment gateways across the world.
					<br />
					Find more at our extensions directory
				</p>
				<a target="_blank" class="btn btn-large btn-success" href="<?php echo J2Store::buildHelpLink('extensions/payment-plugins.html', 'gateways'); ?>">Get more payment plugins</a>
			</div>
		</div>

	</div>
</div>