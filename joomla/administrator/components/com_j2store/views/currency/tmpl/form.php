<?php
/*------------------------------------------------------------------------
 # com_j2store - J2Store
# ------------------------------------------------------------------------
# author    Sasi varna kumar - Weblogicx India http://www.weblogicxindia.com
# copyright Copyright (C) 2014 - 19 Weblogicxindia.com. All Rights Reserved.
# @license - http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Websites: http://j2store.org
# Technical Support:  Forum - http://j2store.org/forum/index.html
-------------------------------------------------------------------------*/
// No direct access
defined ( '_JEXEC' ) or die ();
JHtml::_('script', 'media/j2store/js/j2store.js', false, false);
?>
<div class="j2store_currency_edit">
<?php

$viewTemplate = $this->getRenderedForm();
echo $viewTemplate;
?>
</div>
