<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_nokwebdav
 *
 * @copyright   Copyright (c) 2022 Norbert Kümin. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace NKuemin\Component\NoKWebDAV\Site\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of container records.
 *
 * @since  1.6
 */
class ContainersModel extends ListModel {
    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface  $factory  The factory.
     *
     * @see    \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null) {
        if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'c.id',
				'name', 'c.name',
				'type', 'c.type',
				'filepath', 'c.filepath',
				'published', 'c.published',
				'quotaValue', 'c.quotaValue',
				'quotaExp', 'c.quotaExp',
				'createddate', 'c.createddate',
				'createdby', 'c.createdby',
				'modifieddate', 'c.modifieddate',
				'modifiedby', 'c.modifiedby'
			);
        }
        parent::__construct($config, $factory);
    }

    /**
     * Method to get all fields whit their database field name and label.
     *
     * @return  DatabaseQuery   A DatabaseQuery object to retrieve the data set.
     *
     * @since   4.0.0
     */
	protected function getFields() {
		return array (
			'id' => array(Text::_('COM_NOKWEBDAV_COMMON_FIELD_ID_LABEL',true),'c.id'),
			'name' => array(Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_NAME_LABEL',true),'c.name'),
			'type' => array(Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_TYPE_LABEL',true),'c.type'),
			'filepath' => array(Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_FILEPATH_LABEL',true),'c.filepath'),
			'query' => array(Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_QUERY_LABEL',true),'c.query'),
			'published' => array(Text::_('COM_NOKWEBDAV_COMMON_FIELD_STATE_LABEL',true),'c.published'),
			'quotaValue' => array(Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_QUOTA_VALUE_LABEL',true),'c.quotaValue'),
			'quotaExp' => array(Text::_('COM_NOKWEBDAV_CONTAINER_FIELD_QUOTA_EXP_LABEL',true),'c.quotaExp'),
			'createdby' => array(Text::_('COM_NOKWEBDAV_COMMON_FIELD_CREATEDBY_LABEL',true),'c.createdby'),
			'createddate' => array(Text::_('COM_NOKWEBDAV_COMMON_FIELD_CREATEDDATE_LABEL',true),'c.createddate'),
			'modifiedby' => array(Text::_('COM_NOKWEBDAV_COMMON_FIELD_MODIFIEDBY_LABEL',true),'c.modifiedby'),
			'modifieddate' => array(Text::_('COM_NOKWEBDAV_COMMON_FIELD_MODIFIEDDATE_LABEL',true),'c.modifieddate')
		);
	}

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'c.name', $direction = 'asc') {
        $app = Factory::getApplication();

        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.' . $forcedLanguage;
        }

        // Load the parameters.
        $params = ComponentHelper::getParams('com_nokwebdav');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     */
    protected function getStoreId($id = '') {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.level');
        return parent::getStoreId($id);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  \Joomla\Database\DatabaseQuery
     */
    protected function getListQuery() {
        // Create a new query object.
        $db = $this->getDatabase();
        $query = $db->getQuery(true);
        $user = Factory::getUser();

		// Calculate field list
		$allFields = $this->getFields();
		$fields = array();
		foreach (array_keys($allFields) as $key) {
			if (isset($allFields[$key]) && !empty($allFields[$key])) {
				$field = $allFields[$key];
				array_push($fields,$db->quoteName($field[1])." AS ".$db->quoteName($key));
			}
		}

        // Select the required fields from the table.
        $query->select($fields)
            ->from($db->quoteName('#__nokWebDAV_containers', 'c'));

        // Filter by published state.
        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $published = (int) $published;
            $query->where($db->quoteName('c.published') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where($db->quoteName('c.published') . ' IN (0, 1)');
        }

        // Filter by search in name
        if ($search = $this->getState('filter.search')) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('c.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where('(' . $db->quoteName('c.name') . ' LIKE :search)')
                    ->bind([':search'], $search);
            }
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'c.name');
        $orderDirn = $this->state->get('list.direction', 'ASC');
        $ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);
        $query->order($ordering);
        return $query;
    }
}