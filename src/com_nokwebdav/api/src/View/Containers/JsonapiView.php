<?php

/**
 * @package     Joomla.API
 * @subpackage  com_nokwebdav
 *
 * @copyright   Copyright (c) 2022 Norbert Kümin. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace NKuemin\Component\NoKWebDAV\Api\View\Containers;

use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The article view
 *
 * @since  4.0.0
 */
class JsonapiView extends BaseApiView
{
    /**
     * The fields to render item in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderItem = [
        'id',
        'name',
        'type',
        'filepath',
        'query',
        'published',
        'quotaValue',
        'quotaExp',
        'createdby',
        'createddate',
        'modifiedby',
        'modifieddate',
    ];

    /**
     * The fields to render items in the documents
     *
     * @var  array
     * @since  4.0.0
     */
    protected $fieldsToRenderList = [
        'id',
        'name',
        'type',
        'published',
        'createdby',
        'createddate',
        'modifiedby',
        'modifieddate',
    ];

    /**
     * Execute and display a template script.
     *
     * @param   array|null  $items  Array of items
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function displayList(array $items = null) {
        return parent::displayList();
    }
}
