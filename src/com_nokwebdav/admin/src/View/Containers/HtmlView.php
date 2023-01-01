<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_nokwebdav
 *
 * @copyright   Copyright (c) 2022 Norbert Kümin. All rights reserved.
 * @license     GNU General Public License version 2 or higher; see LICENSE
 */

namespace NKuemin\Component\NoKWebDAV\Administrator\View\Containers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Main "Hello World" Admin View
 */
class HtmlView extends BaseHtmlView {
    public $activeFilters = [];
	public $filterForm;
	protected $items;
	protected $pagination;
	protected $state;
    private $isEmptyState = false;

    /**
     * Display the list of containers
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     * @return  void
     */
    function display($tpl = null) {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        if (!\count($this->items) && $this->isEmptyState = $this->get('IsEmptyState')) {
            $this->setLayout('emptystate');
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
     */
    protected function addToolbar(): void {
        $canDo = ContentHelper::getActions('com_nokwebdav', 'containers');
        $user  = Factory::getApplication()->getIdentity();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('COM_NOKWEBDAV_MANAGER_CONTAINERS'), 'containers');

        if ($canDo->get('core.create') || \count($user->getAuthorisedCategories('com_nokwebdav', 'core.create')) > 0) {
            $toolbar->addNew('container.add');
        }

        if (!$this->isEmptyState && ($canDo->get('core.edit.state') || ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')))) {
            $dropdown = $toolbar->dropdownButton('status-group')
                ->text('JTOOLBAR_CHANGE_STATUS')
                ->toggleSplit(false)
                ->icon('icon-ellipsis-h')
                ->buttonClass('btn btn-action')
                ->listCheck(true);
            $childBar = $dropdown->getChildToolbar();

            if ($canDo->get('core.edit.state')) {
                if ($this->state->get('filter.published') != 2) {
                    $childBar->publish('containers.publish')->listCheck(true);
                    $childBar->unpublish('containers.unpublish')->listCheck(true);
                }

                if ($this->state->get('filter.published') != -1) {
                    if ($this->state->get('filter.published') != 2) {
                        $childBar->archive('containers.archive')->listCheck(true);
                    } elseif ($this->state->get('filter.published') == 2) {
                        $childBar->publish('publish')->task('containers.publish')->listCheck(true);
                    }
                }

                if ($this->state->get('filter.published') != -2) {
                    $childBar->trash('containers.trash')->listCheck(true);
                }
            }

            if ($this->state->get('filter.published') == -2 && $canDo->get('core.delete')) {
                $toolbar->delete('containers.delete')
                    ->text('JTOOLBAR_EMPTY_TRASH')
                    ->message('JGLOBAL_CONFIRM_DELETE')
                    ->listCheck(true);
            }
        }

        if ($user->authorise('core.admin', 'com_nokwebdav') || $user->authorise('core.options', 'com_nokwebdav')) {
            $toolbar->preferences('com_nokwebdav');
        }

        $toolbar->help('Containers');
    }
}