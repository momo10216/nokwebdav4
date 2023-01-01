<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$user = Factory::getUser();
?>
    <?php foreach ($this->items as $i => $item) : ?>
        <?php if ($this->hasAccess($item->id)) : ?>
	    <tr>
            <td><?php echo $item->name; ?></td>
			<td><?php echo $this->decodeType($item->type); ?></td>
			<td><a href="<?php echo $this->calculateUrl($item); ?>"><?php echo $this->calculateUrl($item); ?></a></td>
            <?php if ($this->paramsMenu->get('show_access') == '1') : ?>
            <td align="center"><?php echo $this->canDo('content.read', $item->id); ?></td>
            <td align="center"><?php echo $this->canDo('content.create', $item->id); ?></td>
            <td align="center"><?php echo $this->canDo('content.change', $item->id); ?></td>
            <td align="center"><?php echo $this->canDo('content.delete', $item->id); ?></td>
            <?php endif; ?>
		</tr>
        <?php endif; ?>
    <?php endforeach; ?>