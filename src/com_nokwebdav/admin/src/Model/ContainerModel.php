<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_nokwebdav
 *
 * @copyright   Copyright (c) 2022 Norbert Kümin. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace NKuemin\Component\NoKWebDAV\Administrator\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Newsfeed model.
 *
 * @since  1.6
 */
class ContainerModel extends AdminModel {
    use VersionableModelTrait;

    /**
     * The type alias for this content type.
     *
     * @var      string
     * @since    3.2
     */
    public $typeAlias = 'com_nokwebdav.container';

    /**
     * @var     string    The prefix to use with controller messages.
     * @since   1.6
     */
    protected $text_prefix = 'COM_NOKWEBDAV';

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canDelete($record) {
        if (empty($record->id) || $record->published != -2) {
            return false;
        }
        return parent::canDelete($record);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|bool  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = array(), $loadData = true) {
        // Get the form.
        $form = $this->loadForm('com_nokwebdav.container', 'container', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) {
            return false;
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object) $data)) {
            // Disable fields for display.
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        // Don't allow to change the created_by user if not allowed to access com_users.
        if (!Factory::getUser()->authorise('core.manage', 'com_users')) {
            $form->setFieldAttribute('createdby', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData() {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_nokwebdav.edit.container.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        $this->preprocessData('com_nokwebdav.container', $data);
        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   3.0
     */
    public function save($data) {
        $input = Factory::getApplication()->input;

        // Alter the name for save as copy
        if ($input->get('task') == 'save2copy') {
            $origTable = clone $this->getTable();
            $origTable->load($input->getInt('id'));
            if ($data['name'] == $origTable->name) {
                $data['name'] = $data['name'].' Copy';
            }
            $data['published'] = 0;
        }

        return parent::save($data);
    }

    /**
     * Prepare and sanitise the table prior to saving.
     *
     * @param   \Joomla\CMS\Table\Table  $table  The table object
     *
     * @return  void
     */
    protected function prepareTable($table) {
        $date = Factory::getDate();
        $user = Factory::getUser();

        $table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);

        if (empty($table->alias)) {
            $table->alias = ApplicationHelper::stringURLSafe($table->name, $table->language);
        }

        if (empty($table->id)) {
            // Set the values
            $table->createddate = $date->toSql();
            $table->createdby = $user->get('name');
        } else {
            // Set the values
            $table->modifieddate = $date->toSql();
            $table->modifiedby = $user->get('name');
        }
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    &$pks   A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function publish(&$pks, $value = 1) {
        return parent::publish($pks, $value);
    }
}
