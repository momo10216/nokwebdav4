<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_nokwebdav
 *
 * @copyright   Copyright (c) 2022 Norbert Kümin. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace NKuemin\Component\NoKWebDAV\Site\View\Containers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * View for the containers list
 */
class HtmlView extends BaseHtmlView {
    /**
     * The model state
     *
     * @var   \Joomla\CMS\Object\CMSObject
     */
    protected $state = null;

    /**
     * An array containing archived articles
     *
     * @var   \stdClass[]
     */
    protected $items = array();

    /**
     * The page parameters
     *
     * @var    \Joomla\Registry\Registry|null
     *
     * @since  4.0.0
     */
    protected $paramsGlobal = null;
    protected $paramsMenu = null;

    /**
     * The user object
     *
     * @var    \Joomla\CMS\User\User
     *
     * @since  4.0.0
     */
    protected $user = null;

    /**
     * Display the view
     *
     * @param   string  $template  The name of the layout file to parse.
     * @return  void
     */
    public function display($template = null) {
        $app = Factory::getApplication();
        $this->user = $this->getCurrentUser();
        $this->state = $this->get('State');
        $this->items = $this->get('Items');

        if ($errors = $this->getModel()->getErrors()) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Get and merge parameters
        $this->paramsGlobal = $this->state->get('params');
        $currentMenu = $app->getMenu()->getActive();
		if (is_object($currentMenu)) {
            $this->paramsMenu = $currentMenu->getParams();
		}

        // Call the parent display to display the layout file
        parent::display($template);
    }

    public function decodeType($type) {
        switch($type) {
            case 'files': return Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_TYPE_FILES');
            case 'contacts': return Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_TYPE_CONTACTS');
            case 'events': return Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_TYPE_EVENTS');
            default: return $type;
        }
    }

    public function decodeBoolean($value) {
        if ($value) { return Text::_('JYES'); }
        return Text::_('JNO');
    }

    public function canDo($access, $id) {
        if (!$this->user) {
            return Text::_('JNO');
        }
        return $this->decodeBoolean($this->user->authorise('content.read', 'com_nokwebdav.container.'.$id));
    }

    public function hasAccess($id) {
        if (!$this->user) {
            return false;
        }
        return ($this->paramsMenu->get('filter_access') == '0') ||
            $this->user->authorise('content.read', 'com_nokwebdav.container.'.$id) ||
            $this->user->authorise('content.create', 'com_nokwebdav.container.'.$id) ||
            $this->user->authorise('content.change', 'com_nokwebdav.container.'.$id) ||
            $this->user->authorise('content.delete', 'com_nokwebdav.container.'.$id);
    }

    public function calculateUrl($item) {
        global $_SERVER;
        $url = explode('index.php',(isset($_SERVER['HTTPS']) ? "https" : "http").'://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'])[0];
        $url .= 'components/com_nokwebdav/connector.php/'.$item->name.'/';
        return $url;
    }
}