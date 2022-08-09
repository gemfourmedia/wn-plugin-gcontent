<?php namespace GemFourMedia\GContent;

use Event;
use System\Classes\PluginBase;

use GemFourMedia\GContent\Classes\GContentPageExtend;
use GemFourMedia\GContent\Classes\GContentSearchResultsProvider;

class Plugin extends PluginBase
{

    public function boot()
    {
        Event::listen('offline.sitesearch.extend', function () {
            return new GContentSearchResultsProvider();
        });
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
}
