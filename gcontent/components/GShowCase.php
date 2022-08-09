<?php namespace GemFourMedia\GContent\Components;

use Lang;
use GemFourMedia\GContent\Classes\ComponentAbstract;

use GemFourMedia\GContent\Models\Item;
use GemFourMedia\GContent\Models\Category;
use GemFourMedia\GContent\Models\Group;

class GShowCase extends ComponentAbstract
{
    public $cssClass;
    public $title;
    public $subtitle;

    public $pageParam;
    
    public $items;
    public $category;
    public $group;
    public $serie;
    public $author;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcontent::lang.components.gShowCase.name',
            'description' => 'gemfourmedia.gcontent::lang.components.gShowCase.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'cssClass' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gItems.props.cssClass',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'title' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gItems.props.title',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'subtitle' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gItems.props.subtitle',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'perPage' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.perPage',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.perPage_desc',
                'validationPattern' => '^(0|[1-9][0-9]*)$',
                'validationMessage' => 'gemfourmedia.gcontent::lang.components.gItems.props.numeric_validation',
                'default'     => '12',
                'type'        => 'string',
                'showExternalParam' => false,
            ],
            'pageNumber' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.pageNumber',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.pageNumber_desc',
                'validationPattern' => '^(0|[1-9][0-9]*)$',
                'validationMessage' => 'gemfourmedia.gcontent::lang.components.gItems.props.numeric_validation',
                'default'     => '{{ :page }}',
                'type'        => 'string'
            ],
            'sortOrder' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.sortOrder',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.sortOrder_desc',
                'default'     => 'published_at desc',
                'type'        => 'dropdown',
                'showExternalParam' => false,
            ],
            'showcase' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gShowCase.props.showcase',
                'description' => 'gemfourmedia.gcontent::lang.components.gShowCase.props.showcase_desc',
                'default'     => '',
                'type'        => 'dropdown',
                'showExternalParam' => false,
            ],
            'groupFilter' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.groupFilter',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.groupFilter_desc',
                'default'     => '{{:group}}',
                'type'        => 'dropdown',
                'group'       => 'gemfourmedia.gcontent::lang.components.gItems.props.group_filter',
            ],
            'categoryFilter' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.categoryFilter',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.categoryFilter_desc',
                'default'     => '{{:category}}',
                'type'        => 'dropdown',
                'group'       => 'gemfourmedia.gcontent::lang.components.gItems.props.group_filter',
            ],
            'authorFilter' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.authorFilter',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.authorFilter_desc',
                'default'     => '{{:author}}',
                'type'        => 'dropdown',
                'group'       => 'gemfourmedia.gcontent::lang.components.gItems.props.group_filter',
            ],
            'serieFilter' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.serieFilter',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.serieFilter_desc',
                'default'     => '{{:serie}}',
                'type'        => 'dropdown',
                'group'       => 'gemfourmedia.gcontent::lang.components.gItems.props.group_filter',
            ],
            'tagFilter' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.tagFilter',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.tagFilter_desc',
                'default'     => '{{:tag}}',
                'type'        => 'string',
                'group'       => 'gemfourmedia.gcontent::lang.components.gItems.props.group_filter',
            ],
            'pinnedFilter'    => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.pinnedFilter',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.pinnedFilter_desc',
                'type'        => 'checkbox',
                'group'       => 'gemfourmedia.gcontent::lang.components.gItems.props.group_filter',
                'showExternalParam' => false,
            ],
            'featuredFilter'  => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.featuredFilter',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.featuredFilter_desc',
                'type'        => 'checkbox',
                'group'       => 'gemfourmedia.gcontent::lang.components.gItems.props.group_filter',
                'showExternalParam' => false,
            ],
            'fromDate'  => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.fromDate',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.fromDate_desc',
                'type'        => 'string',
                'group'       => 'gemfourmedia.gcontent::lang.components.gItems.props.group_filter',
                'showExternalParam' => false,
            ],
            'toDate'  => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.toDate',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.toDate_desc',
                'type'        => 'string',
                'group'       => 'gemfourmedia.gcontent::lang.components.gItems.props.group_filter',
                'showExternalParam' => false,
            ],
            'listPage' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.listPage',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.listPage_desc',
                'type'        => 'dropdown',
                'group'       => 'gemfourmedia.gcontent::lang.components.gItems.props.group_link',
                'showExternalParam' => false,
            ],
            'detailPage' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItems.props.detailPage',
                'description' => 'gemfourmedia.gcontent::lang.components.gItems.props.detailPage_desc',
                'type'        => 'dropdown',
                'group'       => 'gemfourmedia.gcontent::lang.components.gItems.props.group_link',
                'showExternalParam' => false,
            ],
            'excludePinnedItems' => [
                'title'             => 'gemfourmedia.gcontent::lang.components.gItems.props.excludePinnedItems',
                'description'       => 'gemfourmedia.gcontent::lang.components.gItems.props.excludePinnedItems_desc',
                'type'              => 'checkbox',
                'default'           => false,
                'group'             => 'gemfourmedia.gcontent::lang.components.gItems.props.group_exceptions',
                'showExternalParam' => false,
            ],
            'excludeFeaturedItems' => [
                'title'             => 'gemfourmedia.gcontent::lang.components.gItems.props.excludeFeaturedItems',
                'description'       => 'gemfourmedia.gcontent::lang.components.gItems.props.excludeFeaturedItems_desc',
                'type'              => 'checkbox',
                'default'           => false,
                'group'             => 'gemfourmedia.gcontent::lang.components.gItems.props.group_exceptions',
                'showExternalParam' => false,
            ],
            'excludeItems' => [
                'title'             => 'gemfourmedia.gcontent::lang.components.gItems.props.excludeItems',
                'description'       => 'gemfourmedia.gcontent::lang.components.gItems.props.excludeItems_desc',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'gemfourmedia.gcontent::lang.components.gItems.props.except_items_validation',
                'default'           => '',
                'group'             => 'gemfourmedia.gcontent::lang.components.gItems.props.group_exceptions',
                'showExternalParam' => false,
            ],
            'excludeCategories' => [
                'title'             => 'gemfourmedia.gcontent::lang.components.gItems.props.excludeCategories',
                'description'       => 'gemfourmedia.gcontent::lang.components.gItems.props.excludeCategories_desc',
                'type'              => 'string',
                'validationPattern' => '^[a-z0-9\-_,\s]+$',
                'validationMessage' => 'gemfourmedia.gcontent::lang.components.gItems.props.except_items_validation',
                'default'           => '',
                'group'             => 'gemfourmedia.gcontent::lang.components.gItems.props.group_exceptions',
                'showExternalParam' => false,
            ],
        ];
    }

    public function getSortOrderOptions()
    {
        $options = Item::$allowedSortingOptions;

        foreach ($options as $key => $value) {
            $options[$key] = Lang::get($value);
        }

        return $options;
    }

    public function getShowcaseOptions() {
        return [
            '' => 'Unset',
            'trending' => 'Trending',
            'popular' => 'Popular',
        ];
    }

     public function onRun()
    {
        $this->prepareVars();

        $this->items = $this->page['items'] = $this->loadItems();

        /*
         * If the page number is not valid, redirect
         */
        if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->items->lastPage()) && $currentPage > 1) {
                return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
            }
        }
    }

    public function onRender()
    {
        if (!$this->items) {
            $this->prepareVars();
            $this->items = $this->page['items'] = $this->loadItems();
        }
    }

    public function prepareVars()
    {
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');
        $this->cssClass = $this->page['cssClass'] = $this->property('cssClass');
        $this->title = $this->page['title'] = $this->property('title');
        $this->subtitle = $this->page['subtitle'] = $this->property('subtitle');

        $this->category = $this->page['category'] = $this->loadCategory();
        $this->group = $this->page['group'] = $this->loadGroup();
        $this->serie = $this->page['serie'] = $this->loadSerie();
        $this->author = $this->page['author'] = $this->loadAuthor();
        
    }

    public function getListingSetting():array
    {
        $options = [
            'perPage' => $this->property('perPage', 12),
            'pageNumber' => $this->property('pageNumber') ? $this->property('pageNumber'): \Input::get('page'),
            'sortOrder' => $this->property('sortOrder', 'published_at desc'),
            'showcase' => $this->property('showcase', null),

            // Filter
            'groupFilter' => $this->group ? $this->group->id : null,
            'categoryFilter' => $this->category ? $this->category->id : null,
            'authorFilter' => $this->author ? $this->author->id : null,
            'serieFilter' => $this->serie ? $this->serie->id : null,
            'tagFilter' => $this->property('tagFilter', null),
            'search'           => trim(input('search')),

            'pinnedFilter' => $this->property('pinnedFilter', null),
            'featuredFilter' => $this->property('featuredFilter', null),
            'toDate' => $this->property('toDate', null),
            'fromDate' => $this->property('fromDate', null),

            // Excludes
            'excludePinnedItems' => $this->property('excludePinnedItems', null),
            'excludeFeaturedItems' => $this->property('excludeFeaturedItems', null),
            'excludeItems' => is_array($this->property('excludeItems'))
                            ? $this->property('excludeItems')
                            : preg_split('/,\s*/', $this->property('excludeItems'), -1, PREG_SPLIT_NO_EMPTY),
            'excludeCategories' => is_array($this->property('excludeCategories'))
                            ? $this->property('excludeCategories')
                            : preg_split('/,\s*/', $this->property('excludeCategories'), -1, PREG_SPLIT_NO_EMPTY),
        ];

        return array_filter($options);

    }

    public function onPaginate()
    {
        $this->setProperty('pageNumber', post('page'));
        // $this->prepareVars();
        $this->page['items'] = $this->loadItems();

        $partial = \Request::header('X-WINTER-REQUEST-PARTIALS');
        $updateArea = '#gItems__'.$this->alias;
        
        return [
            $updateArea => $this->renderPartial($partial),
        ];

    }
}
