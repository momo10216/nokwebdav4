<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nokwebdav
 *
 * @copyright   Copyright (c) 2022 Norbert Kümin. All rights reserved.
 * @license     GNU General Public License version 2 or higher; see LICENSE
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Banners\Administrator\View\Banner\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');
?>

<form action="<?php echo Route::_('index.php?option=com_nokwebdav&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="container-form" aria-label="<?php echo Text::_('COM_NOKWEBDAV_CONTAINER_FORM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">

	<div class="row">
		<div class="span12">
			<div class="row-fluid form-horizontal-desktop">
				<div class="span12">
					<?php echo $this->form->renderField('name'); ?>
				</div>
			</div>
		</div>
	</div>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_NOKWEBDAV_CONTAINER_TAB_COMMON')); ?>
        <div class="row">
            <div class="col-lg-6">
                <?php echo $this->form->renderField('type'); ?>
                <?php echo $this->form->renderField('filepath'); ?>
                <?php echo $this->form->renderField('published'); ?>
            </div>
            <div class="col-lg-6">
                <?php echo $this->form->renderField('quotaValue'); ?>
                <?php echo $this->form->renderField('quotaExp'); ?>
                <?php echo $this->form->renderField('query'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'record', Text::_('COM_NOKWEBDAV_CONTAINER_TAB_RECORDINFO')); ?>
        <div class="row">
            <div class="col-lg-6">
                <?php echo $this->form->renderField('createdby'); ?>
                <?php echo $this->form->renderField('createddate'); ?>
            </div>
            <div class="col-lg-6">
                <?php echo $this->form->renderField('modifiedby'); ?>
                <?php echo $this->form->renderField('modifieddate'); ?>
                <?php echo $this->form->renderField('id'); ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
