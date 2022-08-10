<?php

namespace GemFourMedia\GContent\Classes;

use Backend;

/**
 * Class GContentNavigations
 * 
 * @package GemFourMedia\GContent\Classes
 */
class GContentNavigations
{

    public function register() {
        return [
            'gcontent-main-menu' => [
                'label' => 'gemfourmedia.gcontent::lang.plugin.name',
                'url' => Backend::url('gemfourmedia/gcontent/item'),
                'icon' => 'icon-text-height',
                'permissions' => ['gcontent.access'],
                'sideMenu' => [
                    'gcontent-menu-item' => [
                        'label' => 'gemfourmedia.gcontent::lang.features.item',
                        'url' => Backend::url('gemfourmedia/gcontent/item'),
                        'icon' => 'icon-file-text',
                        'permissions' => ['gcontent.item.manage'],
                    ],
                    'gcontent-menu-group' => [
                        'label' => 'gemfourmedia.gcontent::lang.features.groups',
                        'url' => Backend::url('gemfourmedia/gcontent/group'),
                        'icon' => 'icon-tasks',
                        'permissions' => ['gcontent.group.manage'],
                    ],
                    'gcontent-menu-category' => [
                        'label' => 'gemfourmedia.gcontent::lang.features.category',
                        'url' => Backend::url('gemfourmedia/gcontent/category'),
                        'icon' => 'icon-sitemap',
                        'permissions' => ['gcontent.category.manage'],
                    ],
                    'gcontent-menu-serie' => [
                        'label' => 'gemfourmedia.gcontent::lang.features.serie',
                        'url' => Backend::url('gemfourmedia/gcontent/serie'),
                        'icon' => 'icon-newspaper-o',
                        'permissions' => ['gcontent.serie.manage'],
                    ],
                ],
            ],
        ];
    }

}