<?php namespace GemFourMedia\GContent\Components;

use GemFourMedia\GContent\Classes\ComponentAbstract;

use GemFourMedia\GContent\Models\Seire;

class GSeries extends ComponentAbstract
{

    public $cssClass;
    public $title;
    public $subtitle;
    
    public $items;
    public $currentSerie;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcontent::lang.components.gSeries.name',
            'description' => 'gemfourmedia.gcontent::lang.components.gSeries.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'cssClass' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gSeries.props.cssClass',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'title' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gSeries.props.title',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'subtitle' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gSeries.props.subtitle',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'currentSerie' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gSeries.props.currentSerie',
                'description' => 'gemfourmedia.gcontent::lang.components.gSeries.props.currentSerie_desc',
                'type' => 'string',
                'default' => '{{:serie}}'
            ],
        ];
    }

    public function onRun()
    {
        $this->prepareVars();
        $this->items = $this->loadSeries();

    }

    public function onRender()
    {
        if (!$this->items) {
            $this->prepareVars();
            $this->items = $this->loadSeries();
        }
    }

    public function prepareVars()
    {
        $this->cssClass = $this->page['cssClass'] = $this->property('cssClass');
        $this->title = $this->page['title'] = $this->property('title');
        $this->subtitle = $this->page['subtitle'] = $this->property('subtitle');

        $this->currentSerie = $this->page['currentSerie'] = $this->property('currentSerie');
    }

    public function loadSeries()
    {
        $items = Serie::with(['images', 'items_count'])->get();
        if ($items) {
            $items->each(function ($item) {
                $item->isActive = ($this->property('currentSerie') == $item->slug);
                $item->url = $item->setUrl();
            });
        }
        return $items;
    }
}
