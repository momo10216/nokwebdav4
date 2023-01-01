<?php
/**
* @version	$Id$
* @package	Joomla
* @subpackage	NoKWebDAV
* @copyright	Copyright (c) 2022 Norbert Kümin. All rights reserved.
* @license	http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE
* @author	Norbert Kuemin
* @authorEmail	momo_102@bluemail.ch
*/

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

$listDirn = '';
$listOrder = '';
/*
$listDirn	= $this->escape($this->state->get('list.direction'));
$listOrder	= $this->escape($this->state->get('list.ordering'));
*/

$script = <<<EOD
/* <![CDATA[ */
Joomla.submitbutton = function(pressbutton) {
	Joomla.submitform(pressbutton);
	return true;
}
/* ]]> */
EOD;
Factory::getDocument()->addScriptDeclaration($script);
$script = "/* <![CDATA[ */
Joomla.orderTable = function()
{
	table = document.getElementById(\"sortTable\");
	direction = document.getElementById(\"directionTable\");
	order = table.options[table.selectedIndex].value;
	if (order != '".$listOrder."') {
		dirn = 'asc';
	} else {
		dirn = direction.options[direction.selectedIndex].value;
	}
	Joomla.tableOrdering(order, dirn, '');
}";
Factory::getDocument()->addScriptDeclaration($script);
?>
<form action="<?php echo Route::_('index.php?option=com_nokwebdav&view=containers'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
<?php echo $this->loadTemplate('filter');?>
		<div class="clearfix"> </div>
		<table class="table adminlist">
		        <thead><?php echo $this->loadTemplate('head');?></thead>
		        <tbody><?php echo $this->loadTemplate('body');?></tbody>
		        <tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
		</table>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>