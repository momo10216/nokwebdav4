<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_nokwebdav
 *
 * @copyright   Copyright (c) 2022 Norbert Kümin. All rights reserved.
 * @license     GNU General Public License version 2 or higher; see LICENSE
 */

namespace NKuemin\Component\NoKWebDAV\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Container controller class.
 *
 * @since  4.0.0
 */
class ContainerController extends FormController {
    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'COM_NOKWEBDAV_CONTAINER';
}