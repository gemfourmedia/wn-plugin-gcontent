<?php namespace GemFourMedia\GContent\Models;

use Model;
use Cache;
use BackendAuth;
use ValidationException;
use Carbon\Carbon;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

use GemFourMedia\GContent\Models\Group as ContentGroup;
use GemFourMedia\GContent\Models\Serie;
use GemFourMedia\GContent\Models\Setting;

/**
 * Model
 */
class Item extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Nullable;
    use \Winter\Storm\Database\Traits\Sortable;
    use \GemFourMedia\GContent\Traits\SEOHelper;

    /**
     * @var string name of field use for og:image.
     */
    public $ogImageField = 'main_image';

    /**
     * @var string name of og:type
     */
    public $ogType = 'website';


    public $implement = [
        '@GemFourMedia.GTag.Behaviors.TaggableModel',
        '@Winter.Translate.Behaviors.TranslatableModel',
        '@Winter\Search\Behaviors\Searchable'
    ];

    /**
     * @var string inverse relationship name for GTag reference.
     */
    public $gtagName = 'gcontentItems';

    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcontent_item';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required|max:255',
        'slug'    => ['required', 'max:255','regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i', 'unique:gemfourmedia_gcontent_item'],
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'title',
        'introtext',
        'content',
        'content_html',
        'embeds',
        'meta_title',
        'meta_description',
        'meta_keywords',
        ['slug', 'index' => true]
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public $searchable = [
        'title',
        'introtext',
        'content'
    ];

    /**
     * @var array fillable
     */
    protected $fillable = [
        'group_id',
        'category_id',
        'serie_id',
        'user_id',
        'sort_order',
        'title',
        'slug',
        'introtext',
        'content',
        'content_html',
        'embeds',
        'hit',
        'params',
        'published',
        'featured',
        'pinned',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    /**
     * @var array Nullable attributes.
     */
    protected $nullable = ['group_id','serie_id', 'category_id', 'user_id'];
    /**
     * The attributes are json format
     * @var array
     */
    protected $jsonable = ['embeds', 'params'];

    /**
     * The attributes that appends when model serialize
     * @var array
     */
    protected $appends = ['toc', 'formated_published_at', 'main_image', 'detailPage', 'listPage'];

    /**
     * The attributes that should be mutated to dates.
     * @var array
     */
    protected $dates = ['published_at'];

    /**
     * The attributes on which the category list can be ordered
     * @var array
     */
    public static $allowedSortingOptions = [
        'title asc' => 'gemfourmedia.gcontent::lang.sorting.title_asc',
        'title desc' => 'gemfourmedia.gcontent::lang.sorting.title_desc',
        'created_at asc' => 'gemfourmedia.gcontent::lang.sorting.created_asc',
        'created_at desc' => 'gemfourmedia.gcontent::lang.sorting.created_desc',
        'updated_at asc' => 'gemfourmedia.gcontent::lang.sorting.updated_asc',
        'updated_at desc' => 'gemfourmedia.gcontent::lang.sorting.updated_desc',
        'published_at asc'  => 'gemfourmedia.gcontent::lang.sorting.published_asc',
        'published_at desc' => 'gemfourmedia.gcontent::lang.sorting.published_desc',
        'sort_order asc' => 'gemfourmedia.gcontent::lang.sorting.sort_order_asc',
        'sort_order desc' => 'gemfourmedia.gcontent::lang.sorting.sort_order_desc',
        'random' => 'gemfourmedia.gcontent::lang.sorting.random'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->bindEvent('model.translate.resolveComputedFields', function ($locale) {
            return [
                'content_html' => $this->asExtension('TranslatableModel')->getAttributeTranslated('content', $locale)
            ];
        });
    }

    /*
     * Relationships
     * ===
     */

    public $attachMany = [
        'images' => ['System\Models\File', 'order' => 'sort_order'],
        'attachments' => ['System\Models\File', 'order' => 'sort_order'],
    ];

    public $hasMany = [
        'extras' => [
            'GemFourMedia\GContent\Models\Extra',
            'order' => 'sort_order',
        ],
    ];

    public $belongsToMany = [
        'categories' => [
            'GemFourMedia\GContent\Models\Category',
            'table' => 'gemfourmedia_gcontent_items_categories',
            'order' => 'nest_left',
        ],
        'relatedItems' => [
            'GemFourMedia\GContent\Models\Item',
            'table' => 'gemfourmedia_gcontent_item_related',
            'key' => 'item_id',
            'otherKey' => 'related_id',
        ],
    ];

    public $belongsTo = [
        'group' => ['GemFourMedia\GContent\Models\Group', 'default'=>['name'=>'Default Group', 'slug'=>'default-group']],
        'category' => ['GemFourMedia\GContent\Models\Category', 'default'=>['name'=>'Uncategories', 'slug'=>'uncategories']],
        'serie' => ['GemFourMedia\GContent\Models\Serie'],
        // 'author' => ['Winter\User\Models\User', 'key' => 'user_id'],
    ];

    /*
     * Events
     * ===
     */
    public function beforeValidate()
    {
        // Generate a URL slug for this model
        $this->slug = isset($this->slug) ? $this->slug : $this->title;
        $this->slug = \Str::slug($this->slug);

        // Limit short desc
        if ($this->introtext && strlen(strip_tags($this->introtext))>255) {
            $this->introtext = \Str::limit(strip_tags($this->introtext), 252);
        }

        if ($this->published && !$this->published_at) {
            $this->published_at = Carbon::today();
        }

        // Parse block content to HTML
        if ($this->content) {
            $this->content = $this->content_html = $this->generateTOC($this->content);
        }

    }

    public function afterValidate()
    {
        if ($this->published && !$this->published_at) {
            throw new ValidationException([
               'published_at' => \Lang::get('gemfourmedia.gcontent::lang.item.fields.published_validation')
            ]);
        }
    }

    public function beforeSave()
    {
        // Set SEO Meta
        $keywords = isset ($this->tags) ? implode(', ', $this->tags->lists('name')) : null;
        $this->setMetaTags($this->title, $this->introtext, $keywords);
    }

    /*
     * Accessors
     * ===
     */
    // Setting
    public function getSettingAttribute() {
        return Setting::instance();
    }

    public function getFormatedPublishedAtAttribute() {
        if (!$this->published_at) return null;

        return $this->setting->setFormatedDateTime($this->published_at);
    }

    public function getMainImageAttribute()
    {
        return optional($this->images)->first();
    }

    public function getGalleryAttribute()
    {
        return optional($this->images)->slice(1);
    }

    public function getMainImageUrlAttribute()
    {
        if (!$this->main_image) return '';

        return $this->main_image->getPath();
    }

    public function getDetailPageAttribute()
    {
        return $this->group->detail_page ?? $this->setting->get('detailPage');
    }

    public function getListPageAttribute()
    {
        return $this->group->list_page ?? $this->setting->get('listPage');
    }

    public function getDefaultUrlAttribute()
    {
        return $this->setUrl();
    }

    public function getMinReadAttribute($wpm = 200)
    {
        $totalWords = str_word_count(strip_tags($this->content));
        $minutes = floor($totalWords / $wpm);
        $seconds = floor($totalWords % $wpm / ($wpm / 60));

        return $minutes;
        // return array(
        //     'minutes' => $minutes,
        //     'seconds' => $seconds
        // );
    }

    /*
     * Scopes
     * ===
     */
    public function scopeIsPublished($query)
    {
        return $query
            ->whereNotNull('published')
            ->where('published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
        ;
    }

    public function scopeIsPinned($query)
    {
        return $query
            ->whereNotNull('pinned')
            ->where('pinned', true)
        ;
    }

    public function scopeIsNotPinned($query)
    {
        return $query
            ->where('pinned', false)
            ->orWhereNull('pinned')
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
            ->where('featured', false)
            ->orWhereNull('featured')
        ;
    }

    /**
     * List Front End
     *
     * @param  array $categories
     * @return $query
     */
    public function scopeListFrontEnd($query, $options = [])
    {
        /*
         * Default options
         */
        extract(array_merge([
            'showcase' => null,
            'perPage' => null,
            'pageNumber' => 1,
            'sortOrder' => null,

            'groupFilter' => null,
            'categoryFilter' => null,
            'tagFilter' => null,
            'authorFilter' => null,
            'serieFilter' => null,
            'pinnedFilter' => null,
            'featuredFilter' => null,
            'timeFilter'     => true,
            'fromDate' => null,
            'toDate' => null,

            'excludePinnedItems' => null,
            'excludeFeaturedItems' => null,
            'excludeItems' => null,
            'excludeCategories' => null,

            'search'           => '',
            'published'        => true,
        ], $options));

        $searchableFields = ['title', 'slug', 'introtext', 'content_html'];

        switch ($showcase) {
            case 'trending':
                $query->filterByTrending(); break;
            case 'popular':
                $query->filterByPopular(); break;
        }

        if ($published) {
            $query->isPublished();
        }

        /*
         * Search
         */
        $search = trim($search);
        if (strlen($search)) {
            $query->searchWhere($search, $searchableFields);
        }

        /*
         * Filters
         */
        // Content group
        if ($groupFilter) {
            $query->filterByContentGroup($groupFilter);
        }

        // Category
        if ($categoryFilter && $categoryFilter!=null){
            $query->filterCategory($categoryFilter);
        }

        // Tag
        if ($tagFilter && isset ($this->tags)){
            $tagFilter = is_array($tagFilter) ? $tagFilter : [$tagFilter];
            $query->filterByTags($tagFilter);

        }

        // Author
        if ($authorFilter){
            $query->filterByAuthor($authorFilter);
        }

        // Serie
        if ($serieFilter){
            $query->filterBySerie($serieFilter);

        }
        // Pinned Only
        if ($pinnedFilter){
            $query->isPinned();
        }

        // Featured Only
        if ($featuredFilter){
            $query->isFeatured();
        }

        // Specify time
        if ($timeFilter){
            $query->filterByDate(trim(input('y', null)), trim(input('m', null)), trim(input('d', null)));
        }

        // Period
        if ($fromDate || $toDate){
            $query->filterByPeriod($fromDate, $toDate);
        }

        /*
         * Exclude item(s)
         */
        // Pinned items
        if ($excludePinnedItems){
            $query->isNotPinned();
        }

        // Featured items
        if ($excludeFeaturedItems){
            $query->isNotFeatured();
        }

        // Sepcify items
        if ($excludeItems){
            $excludeItems = (is_array($excludeItems)) ? $excludeItems : [$excludeItems];
            $excludeItemIds = [];
            $excludeItemSlugs = [];
            foreach ($excludeItems as $excludeItem) {
                $excludeItem = trim($excludeItem);

                if (is_numeric($excludeItem)) {
                    $excludeItemIds[] = $excludeItem;
                } else {
                    $excludeItemSlugs[] = $excludeItem;
                }
            }

            if (count($excludeItemIds)) {
                $query->whereNotIn('id', $excludeItemIds);
            }
            if (count($excludeItemSlugs)) {
                $query->whereNotIn('slug', $excludeItemSlugs);
            }
        }

        // Sepcify categories
        if ($excludeCategories){
            $excludeCategories = is_array($excludeCategories) ? $excludeCategories : [$excludeCategories];
            array_walk($excludeCategories, 'trim');

            $query->whereNotIn('category_id', $excludeCategories);

            $query->whereDoesntHave('categories', function ($q) use ($excludeCategories) {
                $q->whereIn('slug', $excludeCategories);
            });
        }

        /*
         * Sorting
         */
        if (in_array($sortOrder, array_keys(static::$allowedSortingOptions))) {
            if ($sortOrder == 'random') {
                $query->inRandomOrder();
            } else {
                @list($sortField, $sortDirection) = explode(' ', $sortOrder);

                if (is_null($sortDirection)) {
                    $sortDirection = "desc";
                }

                $query->orderBy($sortField, $sortDirection);
            }
        }

        /*
         * Return paginate
         */
        return $query->paginate($perPage, $pageNumber);
    }

    /**
     * Filter item by categories IDs
     *
     * @param  array $categories
     * @return $query
     */
    public function scopeFilterCategories($query, $categories)
    {
        return $query->whereHas('categories', function($q) use ($categories) {
            $q->whereIn('id', $categories);
        })->orWhereIn('category_id', $categories);
    }

    /**
     * Allows filtering for specifc categories.
     * @param  array $categories List of category ids
     * @return @return Illuminate\Query\Builder $query
     */
    public function scopeFilterFeaturedCategories($query, $categories)
    {
        return $query->whereHas('categories', function($q) use ($categories) {
            $q->whereIn('id', $categories)->isFeatured();
        })->orWhereHas('category', function($q) use ($categories) {
            $q->whereIn('id', $categories)->isFeatured();
        });
    }

    /**
     * Filter item by category ID
     *
     * @param  int|string $categoryID
     * @return $query
     */
    public function scopeFilterCategory($query, int $categoryID)
    {
        return $query->where('category_id', $categoryID)->orWhereHas('categories', function ($q) use ($categoryID) {
            $q->where('id', $categoryID);
        });
    }

    /**
     * Filter item by serie ID
     *
     * @param  int $serieID
     * @return $query
     */
    public function scopeFilterBySerie($query, int $serieID)
    {
        return $query->where('serie_id', $serieID);
    }

    /**
     * Filter item by content group ID
     *
     * @param  int|string $contentGroupID
     * @return @return Illuminate\Query\Builder $query
     */
    public function scopeFilterByContentGroup($query, int $groupId)
    {
        return $query->where('group_id', $groupId);
    }

    /**
     * Filter By Impression (Most viewed)
     *  @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterByPopular($query)
    {
        return $query->orderBy('hit','DESC');
    }

    /**
     * Filter By Trending (most viewed  items in last month)
     * @return Illuminate\Query\Builder              QueryBuilder
     */
    public function scopeFilterByTrending($query)
    {
        $lastMonth = strtotime(date('Y-m-d H:i:s') . ' -30 days');
        $date = date('Y-m-d H:i:s', $lastMonth);
        $query->whereRaw("published_at > '$date'");
        return $query->orderBy('hit','desc');
    }

    /**
     * Filter By Author
     * @param int $userId
     * @return Illuminate\Query\Builder $query
     */
    public function scopeFilterByAuthor($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }


    /**
     * Filter By Specify Date
     * @param int $y
     * @param int $m
     * @param int $d
     * @return Illuminate\Query\Builder $query
     */
    public function scopeFilterByDate($query, $y, $m, $d)
    {
        if ($y) $query->whereYear('published_at', $y);
        if ($m) $query->whereMonth('published_at', $m);
        if ($d) $query->whereDay('published_at', $d);
        return $query;
    }


    /**
     * Filter By Period
     * @param int $y
     * @param int $m
     * @param int $d
     * @return Illuminate\Query\Builder $query
     */
    public function scopeFilterByPeriod($query, $fromDate, $toDate)
    {
        if ($fromDate) $query->where('published_at', '>=' , $fromDate);
        if ($toDate) $query->where('published_at', '<=', $toDate);
        return $query;
    }

    //
    // Next / Previous
    //

    /**
     * Apply a constraint to the query to find the nearest sibling
     *
     *     // Get the next item
     *     Item::applySibling()->first();
     *
     *     // Get the previous item
     *     Item::applySibling(-1)->first();
     *
     *     // Get the previous item, ordered by the ID attribute instead
     *     Item::applySibling(['direction' => -1, 'attribute' => 'id'])->first();
     *
     * @param       $query
     * @param array $options
     * @return
     */
    public function scopeApplySibling($query, $options = [])
    {
        if (!is_array($options)) {
            $options = ['direction' => $options];
        }

        extract(array_merge([
            'direction' => 'next',
            'attribute' => 'id',
            'enableSerie' => false,
        ], $options));

        $isPrevious = in_array($direction, ['previous', -1]);
        $directionOrder = $isPrevious ? 'asc' : 'desc';
        $directionOperator = $isPrevious ? '>' : '<';

        // Apply content group filter
        if ($this->group_id) {
            $query->where('group_id', $this->group_id);
        }
        // Apply Serie filter (optional)
        if ($this->serie_id && $enableSerie) {
            $query->where('serie_id', $this->serie_id);
            $attribute = 'sort_order';
        }
        $query->where('id', '<>', $this->id);

        if (!is_null($this->$attribute)) {
            $query->where($attribute, $directionOperator, $this->$attribute);
        }
        return $query->orderBy($attribute, $directionOrder);
    }

    /*
     * Helpers
     * ===
     */

    /**
     * Returns the next item, if available.
     * @return self
     */
    public function nextItem()
    {
        return self::isPublished()->applySibling()->first();
    }

    /**
     * Returns the previous item, if available.
     * @return self
     */
    public function previousItem()
    {
        return self::isPublished()->applySibling(['direction'=>'previous'])->first();
    }

    /**
     * Generate Table of Content
     * @param string $html
     * @return string
     */
    public function generateTOC($html) {
        $markupFixer  = new \TOC\MarkupFixer();

        $html = $markupFixer->fix($html);
        return $html;
    }

    public function getTocAttribute() {
        if (empty($this->content)) return;
        $tocGenerator = new \TOC\TocGenerator();
        return $tocGenerator->getMenu($this->content);
    }

    /**
     * Batch update
     *
     * @param  array $data
     * @return boolean
     */
    public function batchUpdate($data) {
        $selectedItems = isset($data['selectedItems']) ? json_decode($data['selectedItems']) : [];
        if ($selectedItems) {
            $itemsModel = self::whereIn('id', $selectedItems);

            if (array_get($data,'enable_batch_category', false)) {
                $categoryId = array_get($data, 'category');
                $category = Category::find($categoryId);

                if (array_get($data, 'enable_batch_contentgroup', false)) {
                    $group = ContentGroup::find(array_get($data, 'group'));
                    $groupId = ($group) ? $group->id : null;

                    $catGroupId = $category->group ? $category->group->id : null;

                    if ($groupId != $catGroupId)
                        $categoryId = null;
                }
                if ($categoryId) {
                    $itemsModel->update(['category_id' => $categoryId]);
                }
            }

            if (array_get($data, 'enable_batch_categories')) {
                $categories = isset($data['categories']) ? $data['categories'] : [];
                if ($data['enable_batch_contentgroup']) {
                    $cgroup = isset($data['group']) ? ContentGroup::find($data['group']) : null;
                    $allowedCategories = [];
                    if ($cgroup) {
                        $allowedCategories = $cgroup->categories()->getNested()->lists('id');
                    }
                    $categories = is_array($allowedCategories) ? array_intersect($categories, $allowedCategories) : [];
                }
                $items = $itemsModel->get();
                if ($items) {
                    $items->each(function ($item) use($categories) {
                        if ($categories == [] || $categories==0)
                            $item->categories()->detach();
                        else {
                            $item->categories()->sync($categories);
                        }
                    });
                }
            }
            if (array_get($data, 'enable_batch_contentgroup')) {
                $contentGroup = isset($data['group']) ? $data['group'] : null;
                $contentGroup = is_numeric($contentGroup) ? $contentGroup :nuull;
                $itemsModel->update(['group_id' => $contentGroup]);
            }

            if ($data['enable_batch_serie']) {
                $serie = isset($data['serie']) ? $data['serie'] : null;
                $serie = is_numeric($serie) ? $serie : null;
                $itemsModel->update(['serie_id' => $serie]);
            }


            if (isset($data['mark_featured']) && $data['mark_featured']!='none') {
                $itemsModel->update(['featured' => $data['mark_featured']]);
            }

            if (isset($data['mark_pinned']) && $data['mark_pinned']!='none') {
                $itemsModel->update(['pinned' => $data['mark_pinned']]);
            }

            if (isset($data['mark_published']) && $data['mark_published']!='none') {
                $itemsModel->update(['published' => $data['mark_published']]);
            }
        }
        return true;
    }

    /**
     * Limit visibility of the published-button
     *
     * @param       $fields
     * @param  null $context
     * @return void
     */
    public function filterFields($fields, $context = null)
    {
        if (!isset($fields->published, $fields->published_at)) {
            return;
        }

        $user = BackendAuth::getUser();

        if (!$user->hasAnyAccess(['gcontent.item.manage'])) {
            $fields->published->hidden = true;
            $fields->published_at->hidden = true;
        }
        else {
            $fields->published->hidden = false;
            $fields->published_at->hidden = false;
        }

        if (!isset($fields->category, $fields->group)) {
            return;
        }
        if($this->category && $this->category!=null) {
            $fields->categories->hidden = false;
        }
        else {
            $fields->categories->hidden = true;
        }
        if ($this->group && $this->group != null) {
            $fields->categories->hidden = false;
        }
        else {
            $fields->categories->hidden = true;
        }
    }

    /**
     * Handler for the backend.richeditor.getTypeInfo event.
     * Returns a menu item type information. The type information is returned as array
     * @param string $type Specifies the page link type
     * @return array
     */
    public static function getRichEditorTypeInfo($type)
    {
        if ($type == 'gcontent-item') {

            $items = self::get();
            $result = [];
            if ($items) {
                foreach ($items as $item) {
                    $url = $item->setUrl();
                    $result[$url] = $item->title;
                }
            }
            return $result;
        }

        return [];
    }

    /**
     * Set hit view for this content
     * @param integer $hit
     */
    public function setHit($hit = 1) {
        if (!is_numeric($hit)) return;

        $this->hit = $this->hit + $hit;
        $this->save();
    }
    /**
     * Sets the "url" attribute with a URL to this object.
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     */
    public function setUrl($pageName = null, $controller = null, array $urlParams = array())
    {
        $params = [
            array_get($urlParams, 'id', 'id')   => $this->id,
            array_get($urlParams, 'slug', 'slug') => $this->slug,
            array_get($urlParams, 'category', 'category') => ($this->category) ? $this->category->slug : 'uncategories',
            array_get($urlParams, 'group', 'group') => ($this->group) ? $this->group->slug : 'default-group',
            array_get($urlParams, 'serie', 'serie') => ($this->serie) ? $this->serie->slug : 'default-serie',
        ];

        // Expose published year, month and day as URL parameters.
        if ($this->published) {
            $params['year']  = $this->published_at->format('Y');
            $params['month'] = $this->published_at->format('m');
            $params['day']   = $this->published_at->format('d');
        }

        if (!$controller) {
            $controller = new \Cms\Classes\Controller;
        }
        $pageName = isset($pageName) ? $pageName : $this->detailPage;

        if (!$pageName) {
            return null;
        }

        return $this->url = $controller->pageUrl($pageName, $params);
    }

    //
    // Menu helpers
    //

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
     *
     * @param string $type Specifies the menu item type
     * @return array Returns an array
     */
    public static function getMenuTypeInfo($type)
    {
        $result = [];
        // Single item
        if ($type == 'gcontent-item') {
            $references = [];

            $items = self::get();
            foreach ($items as $item) {
                $references[$item->id] = $item->title;
            }

            $result = [
                'references'   => $references,
                'nesting'      => false,
                'dynamicItems' => false
            ];
        }
        // All Items
        if ($type == 'gcontent-all-items') {
            $result = [
                'dynamicItems' => true
            ];
        }

        // Items filter by category
        if ($type == 'gcontent-category-items') {
            $references = [];

            $references = Category::listSubCategoryOptions();

            $result = [
                'references'   => $references,
                'dynamicItems' => true
            ];
        }

        // Items filter by content group
        if ($type == 'gcontent-contentgroup-items') {
            $references = [];

            $contentGroups = ContentGroup::orderBy('name')->get();
            foreach ($contentGroups as $contentGroup) {
                $references[$contentGroup->id] = $contentGroup->name;
            }

            $result = [
                'references'   => $references,
                'nesting'      => true,
                'dynamicItems' => true
            ];
        }

        // Items filter by Serie
        if ($type == 'gcontent-serie-items') {
            $references = [];

            $series = Serie::orderBy('name')->get();
            foreach ($series as $serie) {
                $references[$serie->id] = $serie->name;
            }

            $result = [
                'references'   => $references,
                'dynamicItems' => true
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];

            foreach ($pages as $page) {
                if (!$page->hasComponent('gItem')) {
                    continue;
                }

                /*
                 * Component must use a categoryPage filter with a routing parameter and post slug
                 * eg: categoryPage = "{{ :somevalue }}", slug = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('gItem');
                if (!isset($properties['listPage']) || !preg_match('/{{\s*:/', $properties['slug'])) {
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
     *   Use the Url::to() helper to generate the URLs.
     * - isActive - determines whether the menu item is active. Not required for menu item types that
     *   return all available records.
     * - items - an array of arrays with the same keys (url, isActive, items) + the title key.
     *   The items array should be added only if the $item's $nesting property value is TRUE.
     *
     * @param \RainLab\Pages\Classes\MenuItem $item Specifies the menu item.
     * @param \Cms\Classes\Theme $theme Specifies the current theme.
     * @param string $url Specifies the current page URL, normalized, in lower case
     * The URL is specified relative to the website root, it includes the subdirectory name, if any.
     * @return mixed Returns an array. Returns null if the item cannot be resolved.
     */
    public static function resolveMenuItem($item, $url, $theme)
    {
        $result = null;
        // Single Item
        if ($item->type == 'gcontent-item') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }

            $GCItem = self::find($item->reference);
            if (!$GCItem) {
                return;
            }

            $pageUrl = self::getItemPageUrl($item->cmsPage, $GCItem, $theme);
            if (!$pageUrl) {
                return;
            }

            $pageUrl = Url::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $GCItem->updated_at;
        }
        // All Items
        elseif ($item->type == 'gcontent-all-items') {
            $result = [
                'items' => []
            ];

            $GCItems = self::isPublished()
            ->orderBy('title')
            ->get();

            foreach ($GCItems as $GCItem) {
                $itemDetail = [
                    'title' => $GCItem->title,
                    'url'   => self::getItemPageUrl($item->cmsPage, $GCItem, $theme),
                    'mtime' => $GCItem->updated_at
                ];

                $itemDetail['isActive'] = $itemDetail['url'] == $url;

                $result['items'][] = $itemDetail;
            }
        }
        // Items filter by Category| Content Group | Serie
        elseif ($item->type == 'gcontent-category-items' || $item->type == 'gcontent-contentgroup-items' || $item->type == 'gcontent-serie-items') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }
            $listPage = Setting::get('listPage');

            $query = self::isPublished()->orderBy('title');

            $result = [];
            $pageUrl = '';

            if ($item->type == 'gcontent-category-items') {
                $category = Category::find($item->reference);
                if (!$category) {
                    return;
                }
                if ($contentGroup = $category->group) {
                    $cmsPage = $contentGroup->list_page;
                }
                if ($cmsPage) {
                    $pageUrl = Category::getCategoryPageUrl($cmsPage, $category, $theme);
                    $result['mtime'] = $category->updated_at;
                }

                $categories = $category->getAllChildrenAndSelf()->lists('id');
                $query->where('category_id', $category->id);
                $GCItems = $query->get();
            }
            elseif ($item->type == 'gcontent-contentgroup-items') {
                $GCItems = $query->where('group_id', $item->reference)->get();

                // Set pageurl for content group
                $contentGroup = ContentGroup::find($item->reference);
                if($contentGroup) {
                    $cmsPage = $contentGroup->list_page;
                    $pageUrl = ContentGroup::getContentGroupPageUrl($cmsPage, $contentGroup, $theme);
                    $result['mtime'] = $contentGroup->updated_at;
                }
            }
            elseif ($item->type == 'gcontent-serie-items') {
                $GCItems = $query->where('serie_id', $item->reference)->get();
            }

            if (!$GCItems) {
                return;
            }
            if ($pageUrl) {
                $pageUrl = \Url::to($pageUrl);
                $result['url'] = $pageUrl;
                $result['isActive'] = $pageUrl == $url;
            }

            foreach ($GCItems as $GCItem) {
                $itemDetail = [
                    'title' => $GCItem->title,
                    'url'   => self::getItemPageUrl($item->cmsPage, $GCItem, $theme),
                    'mtime' => $GCItem->updated_at
                ];

                $itemDetail['isActive'] = $itemDetail['url'] == $url;

                $result['items'][] = $itemDetail;

            }

        }

        return $result;
    }

    /**
     * Returns URL of a item page.
     *
     * @param $pageCode
     * @param $category
     * @param $theme
     */
    protected static function getItemPageUrl($pageCode, $item, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) {
            return;
        }

        $properties = $page->getComponentProperties('gItem');
        if (!isset($properties['slug'])) {
            return;
        }

        /*
         * Extract the routing parameter name from the item filter
         * eg: {{ :someRouteParam }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['slug'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);
        $params = [
            $paramName => $item->slug,
            'year'  => $item->published_at->format('Y'),
            'month' => $item->published_at->format('m'),
            'day'   => $item->published_at->format('d')
        ];
        $url = CmsPage::url($page->getBaseFileName(), $params);

        return $url;
    }
}
