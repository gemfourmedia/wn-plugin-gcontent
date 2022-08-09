<?php namespace GemFourMedia\GContent\Models;

use Model;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

use GemFourMedia\GContent\Models\Setting;

/**
 * Model
 */
class Serie extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \GemFourMedia\GCompany\Traits\SEOHelper;
    
    /**
     * @var string name of field use for og:image.
     */
    public $ogImageField = 'main_image';
    
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
    public $table = 'gemfourmedia_gcontent_serie';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'slug'    => ['required', 'max:255', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:gemfourmedia_gcontent_serie'],
        'featured' => 'boolean',
        'meta_title' => 'max:191',
        'meta_description' => 'max:191',
        'meta_keywords' => 'max:191',
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'name',
        'desc',
        'meta_title',
        'meta_description',
        'meta_keywords',
        ['slug', 'index' => true]
    ];

    /**
     * @var array fillable
     */
    protected $fillable = [
        'name',
        'slug',
        'desc',
        'featured',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * @var array allowed sorting.
     */
    public static $allowedSortingOptions = [
        'name asc' => 'gemfourmedia.gcontent::lang.sorting.title_asc',
        'name desc' => 'gemfourmedia.gcontent::lang.sorting.title_desc',
        'created_at asc' => 'gemfourmedia.gcontent::lang.sorting.created_asc',
        'created_at desc' => 'gemfourmedia.gcontent::lang.sorting.created_desc',
        'updated_at asc' => 'gemfourmedia.gcontent::lang.sorting.updated_asc',
        'updated_at desc' => 'gemfourmedia.gcontent::lang.sorting.updated_desc',
        'random' => 'gemfourmedia.gcontent::lang.sorting.random'
    ];

    /**
     * @var array appends fields
     */
    protected $appends = ['item_count', 'main_image'];

    /**
     * Relationships
     * ===
     */
    public $attachMany = [
        'images' => ['System\Models\File', 'order' => 'sort_order'],
    ];

    public $hasMany = [
        'items' => [
            'GemFourMedia\GContent\Models\Item',
            'order' => 'sort_order asc',
            'scope' => 'isPublished'
        ],
        'items_count' => [
            'GemFourMedia\GContent\Models\Item',
            'scope' => 'isPublished',
            'count' => true
        ]
    ];

    /**
     * Events
     * ===
     */
    public function beforeValidate()
    {
        // Generate a URL slug for this model
        $this->slug = isset($this->slug) ? $this->slug : $this->name;
        $this->slug = \Str::slug($this->slug);

        // Set SEO Meta
        $this->setMetaTags($this->name, $this->desc, $this->meta_keywords);
    }

    public function afterDelete() {
        $this->items()->each(function ($item){
            $item->serie_id = null;
            $item->save();
        });;
    }

    /*
     * Accessors
     * ===
     */
    public function getMainImageAttribute()
    {
        return optional($this->images)->first();
    }

    public function getItemCountAttribute()
    {
        return optional($this->items_count->first())->count ?? 0;
    }

    public function getDefaultUrlAttribute()
    {
        return $this->setUrl();
    }

    /*
     * Helpers
     * ===
     */

    /**
     * Sets the "url" attribute with a URL to this object
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     */
    public function setUrl($pageName = null, $controller = null, array $urlParams = array())
    {
        $params = [
            array_get($urlParams, 'id', 'id')   => $this->id,
            array_get($urlParams, 'serie', 'serie')  => $this->slug,
        ];

        if (!$controller) {
            $controller = new \Cms\Classes\Controller;
        }
        $pageName = isset($pageName) ? $pageName : Setting::get('seriePage');

        if (!$pageName) {
            return null;
        }

        return $this->url = $controller->pageUrl($pageName, $params, false);
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

        if ($type == 'gcontent-all-series') {
            $result = [
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
                 * Component must use a serie filter with a routing parameter
                 * eg: serieFilter = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('gItems');

                if (!isset($properties['serieFilter'])) {
                    continue;
                }

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
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
        if ($item->type == 'gcontent-all-series') {
            $result = [
                'items' => []
            ];

            $series = self::orderBy('name')->get();
            foreach ($series as $serie) {
                $serieItem = [
                    'title' => $serie->name,
                    'url'   => self::getSeriePageUrl($item->cmsPage, $serie, $theme),
                    'mtime' => $serie->updated_at
                ];

                $serieItem['isActive'] = $serieItem['url'] == $url;

                $result['items'][] = $serieItem;
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
    protected static function getSeriePageUrl($pageCode, $serie, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);

        if (!$page) {
            return;
        }

        $properties = $page->getComponentProperties('gItems');
        if (!isset($properties['serieFilter'])) {
            return;
        }
        if ($properties['serieFilter'] == $serie->id || $properties['serieFilter']=='') {
            return CmsPage::url($page->getBaseFileName(), []);
        }
        /*
         * Extract the routing parameter name from the serie filter
         * eg: {{ :someRouteParam }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['serieFilter'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);
        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $serie->slug]);

        return $url;
    }
}
