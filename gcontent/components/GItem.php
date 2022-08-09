<?php namespace GemFourMedia\GContent\Components;

use GemFourMedia\GContent\Classes\ComponentAbstract;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use GemFourMedia\GContent\Models\Item;

use Lang;
use Event;
use Cache;
use Session;

class GItem extends ComponentAbstract
{
    /**
     * The item model used for display.
     *
     * @var GemFourMedia\GContent\Models\Item
     */
    public $item;
    
    /**
     * Next item
     *
     * @var GemFourMedia\GContent\Models\Item
     */
    public $nextItem;
    
    /**
     * Previous item
     *
     * @var GemFourMedia\GContent\Models\Item 
     */
    public $prevItem;

    /**
     * Related items list. 
     *  
     * @var GemFourMedia\GContent\Models\Item 
     */
    public $relatedItems;

    /**
     * Similar items list.
     * 
     * @var GemFourMedia\GContent\Models\Item 
     */
    public $similarItems;

    /**
     * @var string Reference to the page name for linking to categories.
     */
    public $authorPage;

    public function componentDetails()
    {
        return [
            'name'        => 'gemfourmedia.gcontent::lang.components.gItem.name',
            'description' => 'gemfourmedia.gcontent::lang.components.gItem.desc'
        ];
    }

    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItem.props.slug',
                'description' => 'gemfourmedia.gcontent::lang.components.gItem.props.slug_desc',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'listPage' => [
                'title'       => 'gemfourmedia.gcontent::lang.components.gItem.props.listPage',
                'description' => 'gemfourmedia.gcontent::lang.components.gItem.props.listPage_desc',
                'type'        => 'dropdown',
                'default'     => '',
                'showExternalParam' => false,
            ],
            'setPageMeta' => [
                'title'             => 'gemfourmedia.gcontent::lang.components.gItem.props.setPageMeta',
                'description'       => 'gemfourmedia.gcontent::lang.components.gItem.props.setPageMeta_desc',
                'type'              => 'checkbox',
                'default'           => true,
                'group'             => 'gemfourmedia.gcontent::lang.components.gItem.props.group_advanced',
                'showExternalParam' => false,
            ],
        ];
    }

    public function init() {
        $this->prepareGlobalVars();
        
        Event::listen('translate.localePicker.translateParams', function ($page, $params, $oldLocale, $newLocale) {
            $newParams = $params;

            if (isset($params['slug'])) {
                $records = Item::transWhere('slug', $params['slug'], $oldLocale)->first();
                if ($records) {
                    $records->translateContext($newLocale);
                    $newParams['slug'] = $records['slug'];
                }
            }

            return $newParams;
        });
    }

    public function onRun()
    {
        $this->item = $this->page['item'] = $this->loadItem();

        // Register SEO tag
        $this->setPageMeta($this->item);
        
        // Set Hit
        $this->setHit();

        if (!$this->item) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }
    }

    public function onRender()
    {
        if (empty($this->item)) {
            $this->item = $this->page['item'] = $this->loadItem();
        }
    }

    public function loadItem()
    {
        if (!$slug = $this->property('slug')) return;
        $item = new Item;
        $query = $item->query();

        if ($item->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')) {
            $query->transWhere('slug', $slug);
        } else {
            $query->where('slug', $slug);
        }
        $item = $query->with(['images', 'category', 'group', 'categories', 'attachments', 'serie'])->isPublished()->first();
        
        return $item ?: null;
    }

    public function setHit()
    {
        if (!$this->item) return;
        // Set hit impression
        if (!Session::has('item_visit_hit')) {
            Session::put('item_visit_hit', []);
        }

        if (!in_array($this->item['id'], Session::get('item_visit_hit'))) {
            $this->item->setHit($this->item->id);

            Session::push('item_visit_hit', $this->item->id);
        }
    }

    public function tags()
    {
        if (!$this->item) {
            return;
        }
        return $this->item->tags ?? [];
    }

    public function relatedItems()
    {
        if (!$this->item) {
            return;
        }
        $items = $this->item->relatedItems()->get();
        if ($items) {
            $items->each(function ($item) {
                $item->url = $item->setUrl();
            });
        }
        return ($items);
    }

    public function similarItems($take = 5)
    {
        if (!$this->item) {
            return;
        }
        $items = $this->item->getSimilarByTags();

        if ($items) {
            $items->each(function ($item) {
                $item->url = $item->setUrl();
            });
        }
        return ($items);
    }

    public function author()
    {
        if (!$this->item) {
            return;
        }
        $author = $this->item->author;
        if (!$author) return;
        
        $author->url = $this->controller->pageUrl($this->setting->get('authorPage'), ['authorId' => $author->id]);
        
        return $author;
    }

    public function previousItem()
    {
        return $this->getItemSibling(-1);
    }

    public function nextItem()
    {
        return $this->getItemSibling(1);
    }

    protected function getItemSibling($direction = 1)
    {
        if (!$this->item) {
            return;
        }

        $method = $direction === -1 ? 'previousItem' : 'nextItem';

        if (!$item = $this->item->$method()) {
            return;
        }

        // $currentPage = $this->getPage()->getBaseFileName();

        $item->url = $item->setUrl();

        if (isset($item->category)) {
            $item->category->url = $item->category->setUrl($this->listPage);
        }

        return $item;
    }

}
