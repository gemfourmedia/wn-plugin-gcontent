<?php

namespace GemFourMedia\GContent\Classes;

use GemFourMedia\GContent\Models\Item as ItemModel;
use Winter\User\Models\User as UserModel;
use System\Classes\PluginManager;
use Event;

/**
 * Class GContentUserExtend
 * @package GemFourMedia\GContent\Classes
 */
class GContentUserExtend
{

    /**
     * @return void
     */
    public function extend()
    {
        if (PluginManager::instance()->exists('Winter.User')) {
            $this->extendModel();
            $this->extendFields();
            $this->extendListsColumn();
        }
    }

    /**
     * Extend author relationship for Item Model
     * @return void
     */
    protected function extendModel()
    {
        UserModel::extend(function($model) {
            $model->hasMany['items'] = [
                ItemModel::class,
                'order' => 'published_at desc'
            ];
        });

        ItemModel::extend(function($model) {
            $model->belongsTo['author'] = [
                UserModel::class,
                "key" => "user_id"
            ];
        });
    }

    /**
     * Add author field for Item Form
     * @return void
     */
    protected function extendFields()
    {
        Event::listen('backend.form.extendFields', function ($widget){
            if (!$widget->model instanceof ItemModel) {
                return;
            }

            if ($widget->isNested) {
                return;
            }

            /*
             * For pivot models isNested property on widget does not work
             */
            if (array_key_exists('pivot', $widget->model->getRelations())) {
                return;
            }

            if ( ! $widget->model->author) {
                $widget->model->author = new UserModel;
            }

            $widget->addSecondaryTabFields([
                'author' => [
                    'label' => 'gemfourmedia.gcontent::lang.item.fields.user_id',
                    'nameFrom' => 'name',
                    'descriptionFrom' => 'description',
                    'emptyOption' => 'None',
                    'span' => 'full',
                    'type' => 'relation',
                    'tab' => 'gemfourmedia.gcontent::lang.item.tabs.publishing',
                ],
            ]);
        });
    }

    /**
     * Addd author column for Item List
     * @return void
     */
    protected function extendListsColumn()
    {
        // Extend all backend list usage
        Event::listen('backend.list.extendColumns', function ($widget) {
            // Only for the Item controller
            if (!$widget->getController() instanceof \GemFourMedia\GContent\Controllers\Item) {
                return;
            }
            // Only for the Item model
            if (!$widget->model instanceof \GemFourMedia\GContent\Models\Item) {
                return;
            }
            
            if ( ! $widget->model->author) {
                $widget->model->author = new UserModel;
            }

            // Add an extra author column
            $widget->addColumns([
                'user_id' => [
                    'label' => 'gemfourmedia.gcontent::lang.item.fields.user_id',
                    'type' => 'text',
                    'select' => 'name',
                    'relation' => 'author',
                    'sortable' => true,
                ],
            ]);
        });
    }

}