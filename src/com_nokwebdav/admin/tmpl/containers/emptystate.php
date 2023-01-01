<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nokwebdav
 *
 * @copyright   Copyright (c) 2022 Norbert Kümin. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;

$displayData = [
    'textPrefix' => 'COM_NOKWEBDAV',
    'formURL'    => 'index.php?option=com_nokwebdav&view=containers',
    'icon'       => 'icon-file',
];

$user = Factory::getApplication()->getIdentity();

if ($user->authorise('core.create', 'com_nokwebdav') || count($user->getAuthorisedCategories('com_nokwebdav', 'core.create')) > 0) {
    $displayData['createURL'] = 'index.php?option=com_nokwebdav&task=container.add';
}

echo LayoutHelper::render('joomla.content.emptystate', $displayData);
