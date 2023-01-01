<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nokwebdav
 *
 * @copyright   Copyright (c) 2022 Norbert Kümin. All rights reserved.
 * @license     GNU General Public License version 2 or higher; see LICENSE
 */

namespace NKuemin\Component\NoKWebDAV\Administrator\View\Container;

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Banners\Administrator\Model\BannerModel;

\defined('_JEXEC') or die;

/**
 * View to edit a banner.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView {
    /**
     * The Form object
     *
     * @var    Form
     * @since  1.5
     */
    protected $form;

    /**
     * The active item
     *
     * @var    object
     * @since  1.5
     */
    protected $item;

    /**
     * The model state
     *
     * @var    object
     * @since  1.5
     */
    protected $state;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.5
     *
     * @throws  Exception
     */
    public function display($tpl = null): void {
        $this->form  = $this->get('Form');
        $this->item  = $this->get('Item');
        $this->state = $this->get('State');

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     * @throws  Exception
     */
    protected function addToolbar(): void
    {
        Factory::getApplication()->input->set('hidemainmenu', true);

        $user       = $this->getCurrentUser();
        $userId     = $user->id;
        $isNew      = ($this->item->id == 0);

        // Since we don't track these assets at the item level, use the category id.
        $canDo = ContentHelper::getActions('com_nokwebdav');

        ToolbarHelper::title($isNew ? Text::_('COM_NOKWEBDAV_MANAGER_CONTAINER_NEW') : Text::_('COM_NOKWEBDAV_MANAGER_CONTAINER_EDIT'), 'container');

        $toolbarButtons = [];

        // If not checked out, can save the item.
        if ($canDo->get('core.edit') || \count($user->getAuthorisedCategories('com_nokwebdav', 'core.create')) > 0) {
            ToolbarHelper::apply('container.apply');
            $toolbarButtons[] = ['save', 'container.save'];

            if ($canDo->get('core.create')) {
                $toolbarButtons[] = ['save2new', 'container.save2new'];
            }
        }

        // If an existing item, can save to a copy.
        if (!$isNew && $canDo->get('core.create')) {
            $toolbarButtons[] = ['save2copy', 'container.save2copy'];
        }

        ToolbarHelper::saveGroup(
            $toolbarButtons,
            'btn-success'
        );

        if (empty($this->item->id)) {
            ToolbarHelper::cancel('container.cancel');
        } else {
            ToolbarHelper::cancel('container.cancel', 'JTOOLBAR_CLOSE');
        }

        ToolbarHelper::divider();
        ToolbarHelper::help('Containers:_Edit');
    }
}
