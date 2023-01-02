<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_nokwebdav
 *
 * @copyright   Copyright (c) 2022 Norbert Kümin. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace NKuemin\Component\NoKWebDAV\Api\Model;

use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Container model for the NoKWebDAV component.
 *
 * @since  4.0.0
 */
class ContainerModel extends BaseDatabaseModel {
    /**
     * Cached item object
     *
     * @var    object
     * @since  4.0.0
     */
    protected $_item;

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
     * Get the data for a container.
     *
     * @return  object
     *
     * @since   4.0.0
     */
    public function &getItem() {
        if (!isset($this->_item)) {
            /** @var \Joomla\CMS\Cache\Controller\CallbackController $cache */
            $cache = Factory::getCache('com_nokwebdav', 'callback');

            $id = (int) $this->getState('container.id');

            // For PHP 5.3 compat we can't use $this in the lambda function below, so grab the database driver now to use it
            $db = $this->getDatabase();

            $loader = function ($id) use ($db) {
                $query = $db->getQuery(true);

                // Calculate field list
                $allFields = $this->getFields();
                $fields = array();
                foreach (array_keys($allFields) as $key) {
                    if (isset($allFields[$key]) && !empty($allFields[$key])) {
                        $field = $allFields[$key];
                        array_push($fields,$db->quoteName($field[1])." AS ".$db->quoteName($key));
                    }
                }

                $query->select($fields)
                    ->from($db->quoteName('#__nokWebDAV_containers', 'c'))
                    ->where($db->quoteName('c.id') . ' = :id')
                    ->bind(':id', $id, ParameterType::INTEGER);

                $db->setQuery($query);

                return $db->loadObject();
            };

            try {
                $this->_item = $cache->get($loader, array($id), md5(__METHOD__ . $id));
            } catch (CacheExceptionInterface $e) {
                $this->_item = $loader($id);
            }
        }

        return $this->_item;
    }
}
