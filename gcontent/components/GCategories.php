<?php namespace GemFourMedia\GContent\Components;

use GemFourMedia\GContent\Classes\ComponentAbstract;
use GemFourMedia\GContent\Models\Category;

class GCategories extends ComponentAbstract
{
    public $title;
    public $subtitle;
    public $cssClass;

    public $category;
    public $currentCategory;
    public $group;

    public $categories;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcontent::lang.components.gCategories.name',
            'description' => 'gemfourmedia.gcontent::lang.components.gCategories.desc',
        ];
    }

    public function defineProperties()
    {
        return [
            'cssClass' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gCategories.props.cssClass',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'title' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gCategories.props.title',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'subtitle' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gCategories.props.subtitle',
                'type' => 'string',
                'showExternalParam' => false,
            ],
            'groupFilter' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gCategories.props.groupFilter',
                'description' => 'gemfourmedia.gcontent::lang.components.gCategories.props.groupFilter_desc',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
            'categoryFilter' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gCategories.props.categoryFilter',
                'description' => 'gemfourmedia.gcontent::lang.components.gCategories.props.categoryFilter_desc',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
            'currentCategory' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gCategories.props.currentCategory',
                'description' => 'gemfourmedia.gcontent::lang.components.gCategories.props.currentCategory_desc',
                'type' => 'string',
                'default' => '{{:category}}'
            ],
            'featured' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gCategories.props.featured',
                'description' => 'gemfourmedia.gcontent::lang.components.gCategories.props.featured_desc',
                'type' => 'checkbox',
                'showExternalParam' => false,
            ],
            'listPage' => [
                'title' => 'gemfourmedia.gcontent::lang.components.gCategories.props.listPage',
                'description' => 'gemfourmedia.gcontent::lang.components.gCategories.props.listPage_desc',
                'type' => 'dropdown',
                'showExternalParam' => false,
            ],
        ];
    }

    public function onRun()
    {
        $this->prepareVars();
        $this->categories = $this->page['categories'] = $this->loadCategories();
    }

    public function onRender()
    {
        if (!$this->categories) {
            $this->prepareVars();
            $this->categories = $this->page['categories'] = $this->loadCategories();
        }
    }

    public function prepareVars()
    {
        $this->cssClass = $this->page['cssClass'] = $this->property('cssClass');
        $this->title = $this->page['title'] = $this->property('title');
        $this->subtitle = $this->page['subtitle'] = $this->property('subtitle');

        $this->group = $this->page['group'] = $this->loadGroup();
        $this->category = $this->page['category'] = $this->loadCategory();

        $this->currentCategory = $this->page['currentCategory'] = $this->property('currentCategory');
    }

    public function loadCategories()
    {
        $query = Category::with('items_count', 'image', 'cover');

        if ($this->property('featured')) $query->featured();

        if ($this->group) $query->where('group_id', $this->group->id);
        $categories = $query->getNested();

        if ($this->category) {
            $query->where('id', $this->category->id);
            $categories = $query->get();
        }

        if ($categories) {
            /*
             * Populate the isChildActive property
             */
            $hasActiveChild = function($categories) use (&$hasActiveChild) {
                foreach ($categories as $category) {
                    if ($category->isActive) {
                        return true;
                    }

                    $result = $hasActiveChild($category->children);
                    if ($result) {
                        return $result;
                    }
                }
            };

            $iterator = function ($categories) use (&$iterator, &$hasActiveChild) {
                return $categories->each(function ($category) use (&$iterator, &$hasActiveChild) {
                    $category->isActive = ($this->currentCategory == $category->slug);
                    $category->isChildActive = false;
                    $category->children = $iterator($this->loadSubCategories($category));
                    
                    if ($category->children) {
                        $category->isChildActive = $hasActiveChild($category->children);
                    }
                });

            };
            $categories = $iterator($categories);
                    
            $this->linkCategories($categories);

        }

        return $categories;
    }

    protected function loadSubCategories($category) {

        if (!$category) return;

        $children = $category->children();

        if ($this->group) $children->where('group_id', $this->group->id);

        if ($this->property('featured')) $children->featured();
        
        return $children->get();
    }

    /**
     * Sets the URL on each category according to the defined category page
     * @return void
     */
    protected function linkCategories($categories)
    {
        
        return $categories->each(function ($category){
            $category->setUrl($this->listPage, $this->controller);

            if ($category->children) {
                $this->linkCategories($category->children);
            }
        });
    }
}
