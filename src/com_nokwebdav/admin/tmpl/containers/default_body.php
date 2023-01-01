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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

function calcQuota($value, $exp, $noForEmpty=true) {
	if ($noForEmpty && (empty($value) || ($value <= 0))) { return JText::_('JNO'); }
	if (strpos(strval($value),'.')) {
		$result = rtrim(rtrim($value,'0'),'.').' ';
	} else {
		$result = $value.' ';
	}
	switch($exp) {
		case 0:
			$result .= Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_QUOTA_EXP_BYTES');
			break;
		case 1:
			$result .= Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_QUOTA_EXP_KBYTES');
			break;
		case 2:
			$result .= Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_QUOTA_EXP_MBYTES');
			break;
		case 3:
			$result .= Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_QUOTA_EXP_GBYTES');
			break;
		case 4:
			$result .= Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_QUOTA_EXP_TBYTES');
			break;
		case 5:
			$result .= Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_QUOTA_EXP_PBYTES');
			break;
		case 6:
			$result .= Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_QUOTA_EXP_EBYTES');
			break;
	}
	return $result;
}

function getSize($path) {
	if (is_dir(rtrim($path,DIRECTORY_SEPARATOR))) {
		$total_size = 0;
		$files = scandir($path);
		foreach($files as $file) {
			if (($file != '.') && ($file != '..')) {
				$total_size += getSize(rtrim($path,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file);
			}
		}
		return $total_size+filesize($path);
	} else {
		return filesize($path);
	}
}

function getFormatedSize($path) {
	$exp = 0;
	if ((strlen($path) < 1) || (substr($path,0,1) != DIRECTORY_SEPARATOR)) {
		// relative path
		$path = rtrim(JPATH_SITE,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$path;
        if (!is_readable($path)) {
            return Text::_('COM_NOKWEBDAV_CONTAINER_NOT_EXISTING');
        }
	}
	$value = getSize($path);
	while ($value > 1023) {
		$exp++;
		$value = $value/1024;
	}
	return calcQuota(round($value,2), $exp, false);
}

function getStateText($state) {
    switch ($state) {
        case -2:
            return Text::_('JTRASHED');
        case 0:
            return Text::_('JUNPUBLISHED');
        case 1:
            return Text::_('JPUBLISHED');
        case 2:
            return Text::_('JARCHIVED');
    }
    return $state;
}

$user = Factory::getUser();
$canCreate = $user->authorise('core.create', 'com_nokwebdav');
$canEdit = $user->authorise('core.edit', 'com_nokwebdav');
?>
<?php foreach($this->items as $i => $item): ?>
        <tr class="row<?php echo $i % 2; ?>">
                <td>
                        <?php echo JHtml::_('grid.id', $i, $item->id); ?>
                </td>
                <td>
                        <?php if ($canEdit) : ?>
                            <a href="<?php echo Route::_('index.php?option=com_nokwebdav&task=Container.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>">
                                <?php echo $this->escape($item->name); ?></a>
                        <?php else : ?>
                            <?php echo $this->escape($item->name); ?>
                        <?php endif; ?>
                </td>
                <td>
                        <?php echo Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_TYPE_'.strtoupper($item->type)); ?>
                </td>
                <td class="hidden-phone">
                        <?php echo $item->filepath; ?>
                </td>
                <td class="hidden-phone">
                        <?php echo calcQuota($item->quotaValue, $item->quotaExp); ?>
                </td>
                <td class="hidden-phone">
                        <?php echo getFormatedSize($item->filepath); ?>
                </td>
                <td class="hidden-phone">
                        <?php echo getStateText($item->published); ?>
                </td>
        </tr>
<?php endforeach; ?>
