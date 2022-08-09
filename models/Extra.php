<?php namespace GemFourMedia\GContent\Models;

use Model;

/**
 * Model
 */
class Extra extends Model
{
    use \Winter\Storm\Database\Traits\Validation;
    use \Winter\Storm\Database\Traits\Nullable;
    use \Winter\Storm\Database\Traits\Sortable;
    use \Winter\Storm\Database\Traits\Sluggable;

    public $implement = [
        '@Winter.Translate.Behaviors.TranslatableModel'
    ];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'gemfourmedia_gcontent_extras';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'nullable|max:255',
        'subtitle' => 'nullable|max:255',
        'code'    => ['nullable', 'max:255','regex:/^[a-z0-9\/\:_\-\*\[\]\+\?\|]*$/i'],
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = [
        'title',
        'subtitle',
        'content',
        ['code', 'index' => true]
    ];

    /**
     * @var array Nullable attributes.
     */
    protected $nullable = ['item_id','code', 'title', 'subtitle'];

    /**
     * @var array jsonable fields.
     */
    public $jsonable = ['params'];

    /**
     * @var array Generate slugs for these attributes.
     */
    protected $slugs = ['code' => 'title'];

    public $attachMany = [
        'images' => ['System\Models\File', 'order' => 'sort_order'],
    ];

    public $belongsTo = [
        'item' => ['GemFourMedia\GContent\Models\Item'],
    ];

    /**
     * The attributes on which the post list can be ordered.
     * @var array
     */
    public static $allowedSortingOptions = [
        'title asc'           => 'gemfourmedia.gcontent::lang.sorting.title_asc',
        'title desc'          => 'gemfourmedia.gcontent::lang.sorting.title_desc',
        'created_at asc'      => 'gemfourmedia.gcontent::lang.sorting.created_asc',
        'created_at desc'     => 'gemfourmedia.gcontent::lang.sorting.created_desc',
        'updated_at asc'      => 'gemfourmedia.gcontent::lang.sorting.updated_asc',
        'updated_at desc'     => 'gemfourmedia.gcontent::lang.sorting.updated_desc',
        'sort_order asc'      => 'gemfourmedia.gcontent::lang.sorting.manually_asc',
        'sort_order desc'     => 'gemfourmedia.gcontent::lang.sorting.manually_desc',
        'random'              => 'gemfourmedia.gcontent::lang.sorting.random'
    ];

    /*
     * Events
     * ===
     */
    public function beforeValidate()
    {
        // Generate a URL slug for this model
        $this->code = isset($this->code) ? $this->code : $this->title;
        $this->code = \Str::slug($this->code);

        // Limit short desc
        if ($this->subtitle && strlen($this->subtitle)>255) {
            $this->subtitle = \Str::limit($this->subtitle, 252);
        }

    }

    public function getMainImageAttribute()
    {
        return optional($this->images)->first();
    }

    public function getMainImageUrlAttribute()
    {
        if (!$this->main_image) return '';

        return $this->main_image->getPath();
    }
}
