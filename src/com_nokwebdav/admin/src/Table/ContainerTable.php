<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace NKuemin\Component\NoKWebDAV\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

/**
 * Banner table
 *
 * @since  1.5
 */
class ContainerTable extends Table {
    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $_supportNullValue = true;

    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database connector object
     *
     * @since   1.5
     */
    public function __construct(DatabaseDriver $db) {
        $this->typeAlias = 'com_nokwebdav.container';

        parent::__construct('#__nokWebDAV_containers', 'id', $db);

        $this->createddate = Factory::getDate()->toSql();
   }

    /**
     * Overloaded check function
     *
     * @return  boolean
     *
     * @see     Table::check
     * @since   1.5
     */
    public function check() {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        // Set name
        $this->name = htmlspecialchars_decode($this->name, ENT_QUOTES);

        // Set created date if not set.
        if (!(int) $this->createddate) {
            $this->createddate = Factory::getDate()->toSql();
        }

        // Set modified to created if not set
        if (!$this->modifieddate) {
            $this->modifieddate = $this->createddate;
        }

        // Set modified_by to created_by if not set
        if (empty($this->modifiedby)) {
            $this->modifiedby = $this->createdby;
        }

        return true;
    }
}
