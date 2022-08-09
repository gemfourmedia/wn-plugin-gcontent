<?php

namespace GemFourMedia\GContent\Classes;

use GemFourMedia\GContent\Models\Item;
use GemFourMedia\GContent\Models\Category;
use GemFourMedia\GContent\Models\Group as ContentGroup;
use GemFourMedia\GContent\Models\Serie;
use Winter\Pages\Classes\Page as StaticPage;
use System\Classes\PluginManager;
use Event;

/**
 * Class GContentAuthorExtend
 * @package GemFourMedia\GContent\Classes
 */
class GContentPageExtend
{

    /**
     * @return void
     */
    public function extend()
    {
        $this->registerPageMenuItems();

        if (PluginManager::instance()->exists('Winter.Pages')) {
            $this->extendPageModel();
        }
    }

    protected function registerPageMenuItems()
    {
        /*
         * Register menu items for the RainLab.Pages plugin
         */
        Event::listen('pages.menuitem.listTypes', function() {
            return [
                // Categories
                'gcontent-category' => 'gemfourmedia.gcontent::lang.menuitem.category',
                'gcontent-all-categories' => 'gemfourmedia.gcontent::lang.menuitem.all_categories',
                'gcontent-contentgroup-categories' => 'gemfourmedia.gcontent::lang.menuitem.contentgroup_categories',

                // Items
                'gcontent-item' => 'gemfourmedia.gcontent::lang.menuitem.single_item',
                'gcontent-all-items' => 'gemfourmedia.gcontent::lang.menuitem.all_item',
                'gcontent-category-items' => 'gemfourmedia.gcontent::lang.menuitem.category_items',
                'gcontent-contentgroup-items' => 'gemfourmedia.gcontent::lang.menuitem.contentgroup_item',
                'gcontent-serie-items' => 'gemfourmedia.gcontent::lang.menuitem.serie_items',

                // Serie
                'gcontent-all-series' => 'gemfourmedia.gcontent::lang.menuitem.all_serie',
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function($type) {
            if ($type == 'gcontent-category' || $type == 'gcontent-all-categories' || $type == 'gcontent-contentgroup-categories') {
                return Category::getMenuTypeInfo($type);
            }
            elseif ($type == 'gcontent-item' || $type == 'gcontent-all-items' || $type == 'gcontent-category-items' || $type == 'gcontent-contentgroup-items' || $type == 'gcontent-serie-items') {
                return Item::getMenuTypeInfo($type);
            }
            elseif ($type == 'gcontent-all-series') {
                return Serie::getMenuTypeInfo($type);
            }
        });

        Event::listen('pages.menuitem.resolveItem', function($type, $item, $url, $theme) {
            if ($type == 'gcontent-category' || $type == 'gcontent-all-categories' || $type == 'gcontent-contentgroup-categories') {
                return Category::resolveMenuItem($item, $url, $theme);
            }
            elseif ($type == 'gcontent-item' || $type == 'gcontent-all-items' || $type == 'gcontent-category-items' || $type == 'gcontent-contentgroup-items' || $type == 'gcontent-serie-items') {
                return Item::resolveMenuItem($item, $url, $theme);
            }
            elseif ($type == 'gcontent-all-series') {
                return Serie::resolveMenuItem($item, $url, $theme);
            }
        });
    }

    /**
     * @return void
     */
    protected function extendPageModel()
    {
        StaticPage::extend(function($model) {
            $model->addDynamicMethod('listGContentGroups', function() use ($model) {
                return $this->getContentGroupFilterOptions();
            });

            $model->addDynamicMethod('listGContentCategories', function() use ($model) {
                return $this->getCategoryFilterOptions();
            });
        });
    }

        // Category filter options
    protected function getCategoryFilterOptions()
    {
        $categories = collect([''=>'Unset'] + Category::get()->lists('name','id'))->toArray();
        return $categories;
    }

    // Contentgroup filter options
    protected function getContentGroupFilterOptions()
    {
        $contentgroups = collect([''=>'Unset'] + ContentGroup::get()->lists('name','id'))->toArray();
        return $contentgroups;
    }

}