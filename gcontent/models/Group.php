<?php namespace GemFourMedia\GContent\Models;

use Model;
use GemFourMedia\GContent\Models\Setting;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

/**
 * Model
 */
class Group extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \GemFourMedia\GContent\Traits\ListOptionsHelper;
    use \GemFourMedia\GContent\Traits\SEOHelper;

    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel',
    ];

    /**
     * @var string name of field use for og:image.
     */
    public $ogImageField = 'main_image';
    
    /**
     * @var string name of og:type
     */
    public $ogType = 'website';

    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcontent_group';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|max:255',
        'slug'    => ['required', 'max:255', 'regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:gemfourmedia_gcontent_group'],
        'meta_title' => 'max:191',
        'meta_description' => 'max:191',
        'meta_keywords' => 'max:191',
    ];

    /**
     * @var array fillable
     */
    protected $fillable = [
        'name',
        'slug',
        'desc',
        'list_page',
        'detail_page',
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
     * =============
     * Relationships
     * =============
     */
    public $attachMany = [
        'images' => ['System\Models\File', 'order' => 'sort_order'],
    ];

    public $hasMany=[
        'categories' => ['GemFourMedia\GContent\Models\Category'],
        'items' => ['GemFourMedia\GContent\Models\Item'],
    ];

    /**
     * List options
     * ===
     */
    public function getListPageOptions()
    {
        return $this->listGContentCmsPages('gItems');
    }

    public function getDetailPageOptions()
    {
        return $this->listGContentCmsPages('gItem');
    }

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

    public function afterDelete()
    {
        $this->items()->each(function ($item){
            $item->group_id = null;
            $item->save();
        });
        $this->categories()->each(function ($category){
            $category->group_id = null;
            $category->save();
        });
    }

    /*
     * Accessors
     * ====
     */
    public function getMainImageAttribute()
    {
        return optional($this->images)->first();
    }

    public function getUrlAttribute() {
        return $this->setUrl();
    }

    /**
     * Mutators
     * ===
     */
    public function setUrl($pageName = null, $controller = null, array $urlParams = array())
    {
        $params = [
            array_get($urlParams, 'id', 'id')   => $this->id,
            array_get($urlParams, 'group', 'group') => ($this->slug) ? $this->slug : 'default-group',
        ];

        if (!$controller) {
            $controller = new \Cms\Classes\Controller;
        }
        $pageName = isset($pageName) ? $pageName : $this->list_page;

        if (!$pageName) {
            return null;
        }

        return $this->url = $controller->pageUrl($pageName, $params);
    }

    /**
     * Helpers
     * ===
     */
    protected static function getContentGroupPageUrl($pageCode, $contentGroup, $theme)
    {
        $page = CmsPage::load($theme, $pageCode);
        if (!$page) {
            return;
        }
        $controller = new \Cms\Classes\Controller;
        $pageName = $page->getBaseFileName();

        $properties = $page->getComponentProperties('gItems');
        if (!isset($properties['groupFilter'])) {
            return;
        }
        if ($properties['groupFilter'] == $contentGroup->id || $properties['groupFilter']=='') {
            return $controller->pageUrl($pageName, []);
        }
        /*
         * Extract the routing parameter name from the contentGroup filter
         * eg: {{ :groupslug }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['groupFilter'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);
        // $url = CmsPage::url($page->getBaseFileName(), [$paramName => $contentGroup->slug]);
        $url = $controller->pageUrl($pageName, [$paramName => $contentGroup->slug]);
        return $url;
    }
}
