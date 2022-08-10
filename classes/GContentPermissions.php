<?php

namespace GemFourMedia\GContent\Classes;

/**
 * Class GContentPermissions
 * 
 * @package GemFourMedia\GContent\Classes
 */
class GContentPermissions
{

    public function register() {
        return [
            'gcontent.access' => [
                'tab' => 'gemfourmedia.gcontent::lang.plugin.name',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.access',
            ],
            'gcontent.access_setting' => [
                'tab' => 'gemfourmedia.gcontent::lang.plugin.name',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.access_setting',
            ],
            'gcontent.group.manage' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_groups',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.group.manage',
            ],
            'gcontent.group.create' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_groups',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.group.create',
            ],
            'gcontent.group.update' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_groups',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.group.update',
            ],
            'gcontent.group.delete' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_groups',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.group.delete',
            ],
            'gcontent.item.manage' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_items',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.item.manage',
            ],
            'gcontent.item.create' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_items',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.item.create',
            ],
            'gcontent.item.update' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_items',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.item.update',
            ],
            'gcontent.item.delete' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_items',
                'label' => 'gemfourmedia.gcontent::lang.permissions.item.delete',
            ],
            'gcontent.category.manage' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_categories',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.category.manage',
            ],
            'gcontent.category.create' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_categories',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.category.create',
            ],
            'gcontent.category.update' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_categories',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.category.update',
            ],
            'gcontent.category.delete' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_categories',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.category.delete',
            ],
            'gcontent.serie.manage' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_series',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.serie.manage',
            ],
            'gcontent.serie.create' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_series',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.serie.create',
            ],
            'gcontent.serie.update' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_series',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.serie.update',
            ],
            'gcontent.serie.delete' => [
                'tab' => 'gemfourmedia.gcontent::lang.permissions.tab_series',
                'label'=> 'gemfourmedia.gcontent::lang.permissions.serie.delete',
            ],
        ];
    }

}