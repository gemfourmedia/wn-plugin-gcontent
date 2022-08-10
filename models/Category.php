<?php namespace GemFourMedia\GContent\Models;

use Model;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;
use GemFourMedia\GContent\Models\Group as ContentGroup;
use GemFourMedia\GContent\Models\Item;
use GemFourMedia\GContent\Models\Setting;
use Cache;
use Url;

/**
 * Model
 */
class Category extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\NestedTree;
    use \GemFourMedia\GContent\Traits\SEOHelper;

    /**
     * @var string name of field use for og:image.
     */
    public $ogImageField = 'image';

    /**
     * @var string name of og:type
     */
    public $ogType = 'website';


    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcontent_category';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'slug' => ['required', 'max:255','regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:gemfourmedia_gcontent_category'],
        'short_desc' => 'max:255',
        'meta_title' => 'max:191',
        'meta_description' => 'max:191',
        'meta_keywords' => 'max:191',
        'published' => 'boolean',
        'featured' => 'boolean',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'name',
        'desc',
        'short_desc',
        'meta_title',
        'meta_description',
        'meta_keywords',
        ['slug', 'index' => true]
    ];

    /**
     * @var array fillable
     */
    protected $fillable = [
        'group_id',
        'name',
        'slug',
        'desc',
        'featured',
        'published',
        'parent_id',
        'nest_left',
        'nest_right',
        'nest_depth',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * @var array append
     */
    protected $appends = ['item_count'];

    /*
     * Relationships
     * ===
     */
    public $attachOne = [
        'image' => 'System\Models\File',
        'cover' => 'System\Models\File',
    ];

    public $hasMany = [
        'items' => [
            'GemFourMedia\GContent\Models\Item',
            'scope' => 'isPublished',
            'order' => 'sort_order',
        ],
        'items_count' => [
            'GemFourMedia\GContent\Models\Item',
            'scope' => 'isPublished',
            'count' => true
        ],
    ];

    public $belongsTo = [
        'group' => [
            'GemFourMedia\GContent\Models\Group',
            'default' => ['name'=>'Default Group', 'slug'=>'default-group']
        ],
    ];

    public $belongsToMany = [
        'extra_items' => [
            'GemFourMedia\GContent\Models\Item',
            'table' => 'gemfourmedia_gcontent_items_categories',
            'order' => 'published_at desc',
            'scope' => 'isPublished'
        ],
        'extra_items_count' => [
            'GemFourMedia\GContent\Models\Item',
            'table' => 'gemfourmedia_gcontent_items_categories',
            'scope' => 'isPublished',
            'count' => true
        ],
    ];

    /*
     * Events
     * ===
     */
    public function beforeValidate()
    {
        // Generate a URL slug for this model
        $this->slug = isset($this->slug) ? $this->slug : $this->name;
        $this->slug = \Str::slug($this->slug);

        // Limit short desc
        if ($this->short_desc && strlen($this->short_desc)>255) {
            $this->short_desc = \Str::limit($this->short_desc, 252);
        }
    }

    public function afterDelete()
    {
        $this->extra_items()->detach();
        $this->items()->each(function ($item){
            $item->category_id = null;
            $item->save();
        });
    }

    public function beforeSave()
    {
        // Set SEO Meta
        $keywords = isset ($this->tags) ? implode(', ', $this->tags->lists('name')) : null;
        $this->setMetaTags($this->name, $this->short_desc, $keywords);
    }

    /*
     * Accessors
     * ===
     */
    // public function getShortDescAttribute()
    // {
    //     if ($this->desc) {
    //         return str_limit(strip_tags($this->desc), 191, '');
    //     }
    // }

    public function getAllItemsAttribute() {
        return Item::whereHas('categories', function($q) {
            $q->where('id', $this->id);
        })->orWhere('category_id', $this->id)->get();
    }

    public function getItemCountAttribute()
    {
        $directItemCount = optional($this->items_count->first())->count ?? 0;
        $extraItemCount = optional($this->extra_items_count->first())->count ?? 0;
        return $directItemCount + $extraItemCount;
    }

    public function getDetailPageAttribute()
    {
        return $this->group->detail_page ?? Setting::get('detailPage');
    }

    public function getListPageAttribute()
    {
        return $this->group->list_page ?? Setting::get('listPage');
    }

    public function getDefaultUrlAttribute()
    {
        return $this->setUrl();
    }

    /**
     * Count items in this and nested categories
     * @return int
     */
    public function getNestedItemsCount()
    {
        return $this->item_count + $this->children->sum(function ($category) {
            return $category->getNestedItemsCount();
        });
    }

    /*
     * Helpers
     * ===
     */


    /*
     * Scopes
     * ===
     */
    public function scopeIsPublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', true)
        ;
    }

    public function scopeIsFeatured($query)
    {
        return $query
            ->whereNotNull('featured')
            ->where('featured', true)
        ;
    }

    public function scopeIsNotFeatured($query)
    {
        return $query
            ->whereNotNull('featured')
            ->where('featured', false)
        ;
    }

    public function scopeFilterByContentGroup($query, $model) {
        $cgroup = \Request::input('Item.group', null);
        if ($model && isset($model->group_id)) {
            $cgroup = $model->group_id;
        }
        else {
            if (\Request::has('Item.group'))
                $cgroup = post('Item.group', null);
            if (\Request::has('Category.group'))
                $cgroup = post('Category.group',null);
        }
        if ($cgroup && is_numeric($cgroup)) {
            $query->where('group_id', $cgroup);
        }
        return $query;
    }

    public function scopeFilterByMainCat($query, $model){

        $query = $this->scopeFilterByContentGroup($query, $model);

        $mainCat = \Request::input('Item.category', null);
        if ($model && isset($model->category_id)) {
            $mainCat = $model->category_id;
        }
        if ($mainCat) {
            $query->where('id','<>',$mainCat);
        }
        return $query;
    }

    /*
     * Helpers
     * ===
     */
    // Batch Update Helper Method
    public function batchUpdate($data) {
        $selectedItems = isset($data['selectedItems']) ? json_decode($data['selectedItems']) : [];
        if ($selectedItems) {
            $itemsModel = self::whereIn('id', $selectedItems);
            if ($data['enable_batch_contentgroup']) {
                $contentGroup = isset($data['group']) ? $data['group'] : null;
                $contentGroup = is_numeric($contentGroup) ? $contentGroup : null;
                // Flattern category and their children
                $categoryCollection = function($items, $depth = 0) use (&$categoryCollection) {
                    $result = [];
                    foreach ($items as $item) {
                        $result[$item->id] = $item->id;
                        $childItems = $item->getChildren();
                        if ($childItems->count() > 0) {
                            $result = $result + $categoryCollection($childItems, $depth + 1);
                        }
                    }

                    return $result;
                };
                $categories = $categoryCollection($itemsModel->get());
                if ($categories)
                self::whereIn('id', $categories)->update(['group_id' => $contentGroup]);
            }

            if (isset($data['mark_featured']) && $data['mark_featured']!='none') {
                $itemsModel->update(['featured' => $data['mark_featured']]);
            }

            if (isset($data['mark_published']) && $data['mark_published']!='none') {
                $itemsModel->update(['published' => $data['mark_published']]);
            }
        }
        return true;
    }

    /**
     * Sets the "url" attribute with a URL to this object
     *
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     * @param array $urlParams
     *
     * @return string
     */
    public function setUrl($pageName = null, $controller = null, array $urlParams = array())
    {
        $params = [
            array_get($urlParams, 'id', 'id')   => $this->id,
            array_get($urlParams, 'category', 'category') => ($this->slug) ? $this->slug : 'uncategories',
            array_get($urlParams, 'group', 'group') => ($this->group) ? $this->group->slug : '',
        ];

        if (!$controller) {
            $controller = new \Cms\Classes\Controller;
        }
        $pageName = isset($pageName) ? $pageName : $this->listPage;

        if (!$pageName) {
            return null;
        }

        $this->url = $controller->pageUrl($pageName, $params);

        return $this->url;
    }

    /**
     * Handler for the pages.menuitem.getTypeInfo event.
     * Returns a menu item type information. The type information is returned as array
     * with the following elements:
     * - references - a list of the item type reference options. The options are returned in the
     *   ["key"] => "title" format for options that don't have sub-options, and in the format
     *   ["key"] => ["title"=>"Option title", "items"=>[...]] for options that have sub-options. Optional,
     *   required only if the menu item type requires references.
     * - nesting - Boolean value indicating whether the item type supports nested items. Optional,
     *   false if omitted.
     * - dynamicItems - Boolean value indicating whether the item type could generate new menu items.
     *   Optional, false if omitted.
     * - cmsPages - a list of CMS pages (objects of the Cms\Classes\Page class), if the item type requires a CMS page reference to
     *   resolve the item URL.
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type == 'gcontent-category') {
            $result = [
                'references'   => self::listSubCategoryOptions(),
                'nesting'      => true,
                'dynamicItems' => true
            ];
        }

        if ($type == 'gcontent-all-categories') {
            $result = [
                'dynamicItems' => true
            ];
        }


        if ($type == 'gcontent-contentgroup-categories') {
            $result = [
                'references'   => self::listMenuByContentGroupOptions(),
                'nesting'      => true,
                'dynamicItems' => true
            ];
        }


        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];
            foreach ($pages as $page) {
                if (!$page->hasComponent('gItems')) {
                    continue;
                }

                /*
                 * Component must use a category filter with a routing parameter
                 * eg: categoryFilter = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('gItems');
                // if (!isset($properties['categoryFilter']) || !preg_match('/{{\s*:/', $properties['categoryFilter'])) {
                if (!isset($properties['categoryFilter'])) {
                    continue;
                }
                // elseif (!isset($properties['groupFilter']) || !preg_match('/{{\s*:/', $properties['groupFilter'])) {
                if (!isset($properties['groupFilter'])) {
                    continue;
                }

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
    }

    protected static function listSubCategoryOptions()
    {
        $category = self::getNested();

        $iterator = function($categories) use (&$iterator) {
            $result = [];

            foreach ($categories as $category) {
                if (!$category->children) {
                    $result[$category->id] = $category->name;
                }
                else {
                    $result[$category->id] = [
                        'title' => $category->name,
                        'items' => $iterator($category->children)
                    ];
                }
            }

            return $result;
        };

        return $iterator($category);
    }

    public static function listMenuByContentGroupOptions () {
        return ContentGroup::get()->lists('name','id');
    }

    /**
     * Handler for the pages.menuitem.resolveItem event.
     * Returns information about a menu item. The result is an array
     * with the following keys:
     * - url - the menu item URL. Not required for menu item types that return all available records.
     *   The URL should be returned relative to the website root and include the subdirectory, if any.
     *   Use the URL::to() helper to generate the URLs.
     * - isActive - determines whether the menu item is active. Not required for menu item types that
     *   return all available records.
     * - items - an array of arrays with the same keys (url, isActive, items) + the title key.
     *   The items array should be added only if the $item's $nesting property value is TRUE.
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, $url, $theme)
    {
        $result = null;

        if ($item->type == 'gcontent-category') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }

            $category = self::find($item->reference);
            if (!$category) {
                return;
            }
            $pageUrl = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
            if (!$pageUrl) {
                return;
            }
            $pageUrl = Url::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $category->updated_at;

            if ($item->nesting) {
                $categories = $category->getChildren();
                // dd($categories);
                $iterator = function($categories) use (&$iterator, &$item, &$theme, $url) {
                    $branch = [];

                    foreach ($categories as $category) {

                        $branchItem = [];
                        $branchItem['url'] = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
                        $branchItem['isActive'] = $branchItem['url'] == $url;
                        $branchItem['title'] = $category->name;
                        $branchItem['mtime'] = $category->updated_at;

                        if ($category->children) {
                            $branchItem['items'] = $iterator($category->children);
                        }

                        $branch[] = $branchItem;
                    }

                    return $branch;
                };

                $result['items'] = $iterator($categories);
            }
        }
        elseif ($item->type == 'gcontent-all-categories') {
            $result = [
                'items' => []
            ];

            $categories = self::orderBy('nest_left')->get();
            foreach ($categories as $category) {
                $categoryItem = [
                    'title' => $category->name,
                    'url'   => self::getCategoryPageUrl($item->cmsPage, $category, $theme),
                    'mtime' => $category->updated_at
                ];

                $categoryItem['isActive'] = $categoryItem['url'] == $url;

                $result['items'][] = $categoryItem;
            }
        }
        elseif ($item->type == 'gcontent-contentgroup-categories') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }
            $contentGroup = ContentGroup::find($item->reference);
            if (!$contentGroup) {
                return;
            }
            $pageUrl = ContentGroup::getContentGroupPageUrl($item->cmsPage, $contentGroup, $theme);
            if (!$pageUrl) {
                return;
            }
            $pageUrl = Url::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $contentGroup->updated_at;
            if ($item->nesting) {
                $categories = $contentGroup->categories()->orderBy('nest_left')->getNested();
                $iterator = function($categories) use (&$iterator, &$item, &$theme, $url) {
                    $branch = [];
                    foreach ($categories as $category) {

                        $branchItem = [];
                        $branchItem['url'] = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
                        $branchItem['isActive'] = $branchItem['url'] == $url;
                        $branchItem['title'] = $category->name;
                        $branchItem['mtime'] = $category->updated_at;
                        $branchItem['level'] = $category->getLevel();

                        if ($category->children) {
                            $branchItem['items'] = $iterator($category->children);
                        }

                        $branch[] = $branchItem;
                    }

                    return $branch;
                };

                $result['items'] = $iterator($categories);
            }
        }

        return $result;
    }

    /**
     * Returns URL of a category page.
     *
     * @param $pageCode
     * @param $category
     * @param $theme
     */
    protected static function getCategoryPageUrl($pageCode, $category, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) {
            return;
        }

        $properties = $page->getComponentProperties('gItems');
        if (!isset($properties['categoryFilter'])) {
            return;
        }

        // Handle if use selected category in component
        if ($properties['categoryFilter'] == $category->id || $properties['categoryFilter']=='') {
            return CmsPage::url($page->getBaseFileName(), []);
        }
        /*
         * Handle if use category slug from url
         * Extract the routing parameter name from the category filter
         * eg: {{ :categoryslug }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['categoryFilter'], $matches)) {
            return;
        }

        /*
         * Handle if use content group from url
         * Extract the routing parameter name from the content group filter
         * eg: {{ :groupslug }}
         */
        preg_match('/^\{\{([^\}]+)\}\}$/', $properties['groupFilter'], $contentGroup);

        $categoryParamName = substr(trim($matches[1]), 1);
        $params = [$categoryParamName=>$category->slug];

        if ($contentGroup && isset($category->group)) {
            $contentGroupParamName = substr(trim($contentGroup[1]), 1);
            $params[$contentGroupParamName] = $category->group->slug;
        }
        $url = CmsPage::url($page->getBaseFileName(), $params);

        return $url;
    }
}
