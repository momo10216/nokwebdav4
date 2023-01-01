<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
?>
<?php if ($this->paramsMenu->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1>
            <?php echo $this->escape($this->paramsMenu->get('page_heading')); ?>
        </h1>
    </div>
<?php endif; ?>

<?php if (!empty($this->paramsMenu->get('pretext'))) { echo $this->paramsMenu->get('pretext'); } ?>

    <table border="1" style="border-style:solid; border-width:1px">
        <tr>
            <th><?php echo Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_NAME_LABEL'); ?></th>
            <th><?php echo Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_TYPE_LABEL'); ?></th>
            <th><?php echo Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_URL_LABEL'); ?></th>
<?php if ($this->paramsMenu->get('show_access') == '1') : ?>
            <th><?php echo Text::_('COM_NOKWEBDAV_ACCESS_READ_CONTENT_LABEL'); ?></th>
            <th><?php echo Text::_('COM_NOKWEBDAV_ACCESS_CREATE_CONTENT_LABEL'); ?></th>
            <th><?php echo Text::_('COM_NOKWEBDAV_ACCESS_CHANGE_CONTENT_LABEL'); ?></th>
            <th><?php echo Text::_('COM_NOKWEBDAV_ACCESS_DELETE_CONTENT_LABEL'); ?></th>
<?php endif; ?>
        </tr>
