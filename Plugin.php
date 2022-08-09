<?php namespace GemFourMedia\GContent;

use Event;
use System\Classes\PluginBase;

use GemFourMedia\GContent\Classes\GContentPageExtend;
use GemFourMedia\GContent\Classes\GContentUserExtend;

class Plugin extends PluginBase
{

    // public $require = ['Winter.User'];

    public function boot()
    {
        (new GContentUserExtend)->extend();
    }

    public function pluginDetails()
    {
        return [
            'name' => 'gemfourmedia.gcontent::lang.plugin.name',
            'description' => 'gemfourmedia.gcontent::lang.plugin.description',
            'author' => 'GemFourMedia',
            'icon' => 'oc-icon-adjust',
            'homepage' => 'https://wintercms.gemfourmedia.com/plugin/gcontent',
        ];
    }

    public function register()
    {
        (new GContentPageExtend)->extend();
        
    }

    public function registerComponents()
    {
        return [
            'GemFourMedia\GContent\Components\GItem'        => 'gItem',
            'GemFourMedia\GContent\Components\GItems'       => 'gItems',
            'GemFourMedia\GContent\Components\GRssFeed'     => 'gRssFeed',
            'GemFourMedia\GContent\Components\GSeries'      => 'gSeries',
            'GemFourMedia\GContent\Components\GShowCase'    => 'gShowCase',
            'GemFourMedia\GContent\Components\GCategories'  => 'gCategories',
        ];
    }

    public function registerSettings()
    {
    	return [
            'setting' => [
                'label' => 'gemfourmedia.gcontent::lang.setting.label',
                'description' => 'gemfourmedia.gcontent::lang.setting.desc',
                'category' => 'gemfourmedia.gcontent::lang.setting.category',
                'icon' => 'icon-text-height',
                'class' => \GemFourMedia\GContent\Models\Setting::class,
                'order' => 500,
                'keywords' => 'gcontent cms',
                'permissions' => ['gemfourmedia.gcontent.access_setting'],
            ],
        ];
    }

    public function registerSearchHandlers()
    {
        
        return [

            'gContentItemSearch' => [
                'name' => 'Content Search',
                'model' => \GemFourMedia\GContent\Models\Item::class,
                'record' => [
                    'title' => 'title',
                    'image' => 'main_image',
                    'description' => 'introtext',
                    'url' => 'default_url',
                ]
            ]
        ];
    }
}
