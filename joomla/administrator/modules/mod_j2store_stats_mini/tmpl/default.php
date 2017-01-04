<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */
// No direct access to this file
defined ( '_JEXEC' ) or die ();
$currency = J2Store::currency();
$order_status = $params->get('order_status',array('*'));
?>

<div class="j2store_stat-mini">
	<div class="row-fluid">

			<div class="span3">
			<div class="j2store-stats-mini-badge j2store-stats-mini-today">
			<?php
			$date = new DateTime();
			$expiry = clone $date;
			$expiry->modify('+1 day');
			?>
			<span class="j2store-mini-price">
			<?php
			echo $currency->format(
				F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since( $date->format("Y-m-d") )
									->until( $expiry->format("Y-m-d") )
									->orderstatus($order_status)
									->nozero(1)
									->moneysum(1)
									->getOrdersTotal()
			);
			?>
			</span>
			<h3><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_TODAY'); ?></h3>
		</div>
	</div>

		<div class="span3">
			<div class="j2store-stats-mini-badge j2store-stats-mini-yesterday">
				<?php
				$date = new DateTime();
				$date->setDate(gmdate('Y'), gmdate('m'), gmdate('d'));
				$date->modify("-1 day");
				$yesterday = $date->format("Y-m-d");
				$date->modify("+1 day")
				?>
				<?php
					/* echo F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since( $yesterday )
									->until( $date->format("Y-m-d") )
									->orderstatus($order_status)
									->nozero(1)
									->getOrdersTotal(); */
			?>

			<span class="j2store-mini-price">
			<?php
			echo $currency->format(
				F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since( $yesterday )
									->until( $date->format("Y-m-d") )
									->orderstatus($order_status)
									->nozero(1)
									->moneysum(1)
									->getOrdersTotal()
			);
			?>
			</span>
			<h3><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_YESTERDAY'); ?></h3>
			</div>
		</div>

<div class="span3">
			<div class="j2store-stats-mini-badge j2store-stats-mini-this-month">
				<?php
							switch(gmdate('m')) {
								case 1: case 3: case 5: case 7: case 8: case 10: case 12:
									$lmday = 31; break;
								case 4: case 6: case 9: case 11:
									$lmday = 30; break;
								case 2:
									$y = gmdate('Y');
									if( !($y % 4) && ($y % 400) ) {
										$lmday = 29;
									} else {
										$lmday = 28;
									}
							}
							if($lmday < 1) $lmday = 28;
						?>
			<?php
			/* 	echo F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since(gmdate('Y').'-'.gmdate('m').'-01')
									->until(gmdate('Y').'-'.gmdate('m').'-'.$lmday.' 23:59:59')
									->orderstatus($order_status)
									->nozero(1)
									->getOrdersTotal(); */
			?>
			<span class="j2store-mini-price">
			<?php
			echo $currency->format(
				F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()
									->since(gmdate('Y').'-'.gmdate('m').'-01')
									->until(gmdate('Y').'-'.gmdate('m').'-'.$lmday.' 23:59:59')
									->orderstatus($order_status)
									->nozero(1)
									->moneysum(1)
									->getOrdersTotal()
			);
			?>
			</span>
			<h3><?php echo JText::_('J2STORE_TOTAL_CONFIRMED_ORDERS_THIS_MONTH'); ?></h3>
			</div>
		</div>

		<div class="span3">
			<div class="j2store-stats-mini-badge j2store-stats-mini-orders">
				<span class="j2store-mini-price">
					<?php echo $currency->format(F0FModel::getTmpInstance('Orders', 'J2StoreModel')->clearState()->orderstatus($order_status)->nozero(1)->moneysum(1)->getOrdersTotal());?>
				</span>
				<h3><?php echo JText::_('J2STORE_ALL_TIME'); ?></h3>
			</div>
		</div>

</div>
</div>