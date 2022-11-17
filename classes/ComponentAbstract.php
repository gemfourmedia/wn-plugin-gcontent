<?php namespace GemFourMedia\GContent\Classes;

use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\ComponentBase;
use GemFourMedia\GContent\Models\Setting as SettingModel;
use GemFourMedia\GContent\Models\Group as ContentGroup;
use GemFourMedia\GContent\Models\Category;
use GemFourMedia\GContent\Models\Serie;
use GemFourMedia\GContent\Models\Item;
use Event;

abstract class ComponentAbstract extends ComponentBase
{
    use \GemFourMedia\GContent\Traits\ListOptionsHelper;

    /**
     * GContent Setting
     *
     * @var GemFourMedia\GContent\Models\Setting
     */
    public $setting;

    /**
     * Parameter to use for the page number
     * @var string
     */
    public $pageParam;

    /**
     * Reference to the page name for linking to item detail
     *
     * @var string
     */
    public $detailPage;

    /**
     * Reference to the page name for linking to item list
     *
     * @var string
     */
    public $listPage;


    /**
     * Enable set page meta
     *
     * @var boolean
     */
    public $setPageMeta;


    public function init() {
        $this->prepareGlobalVars();
    }

    public function getDetailPageOptions()
    {
        return [''=>'Inherit'] + $this->listGContentCmsPages('gItem');
    }

    public function getListPageOptions()
    {
        return [''=>'Inherit'] + $this->listGContentCmsPages('gItems');
    }

    public function getAuthorFilterOptions()
    {
        if (!class_exists('\Winter\User\Models\User')) return [];

        return [''=>'None'] + \Winter\User\Models\User::get()->lists('name','id');
    }

    public function prepareGlobalVars()
    {
        $this->setting = $this->page['setting'] = SettingModel::instance();
        $this->setPageMeta = $this->page['setPageMeta'] = $this->property('setPageMeta', false);

        $this->listPage = $this->page['categoryPage'] = $this->property('listPage', $this->setting->get('listPage'));
        $this->detailPage = $this->page['detailPage'] = $this->property('detailPage', $this->setting->get('detailPage'));
    }

    public function loadCategory()
    {
        if (!$slug = $this->property('categoryFilter')) return;

        $category = new Category;
        $query = $category->query();

        if ($category->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')) {
            $query->transWhere('slug', $slug);
        } else {
            $query->where('slug', $slug);
        }

        $category = $query->with(['image', 'cover'])->first();

        if ($category) {
            $category->url = $category->setUrl($this->property('listPage'));
        }

        return $category;
    }

    public function loadGroup()
    {
        if (!$slug = $this->property('groupFilter')) return;

        $group = new ContentGroup;
        $query = $group->query();

        if ($group->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')) {
            $query->transWhere('slug', $slug);
        } else {
            $query->where('slug', $slug);
        }

        $group = $query->first();

        if ($group) {
            $group->url = $group->setUrl();
        }

        return $group;
    }

    public function loadSerie()
    {
        if (!$slug = $this->property('filterSerie')) return;

        $serie = new Serie;
        $query = $serie->query();

        if ($serie->isClassExtendedWith('Winter.Translate.Behaviors.TranslatableModel')) {
            $query->transWhere('slug', $slug);
        } else {
            $query->where('slug', $slug);
        }

        $serie = $query->first();

        if ($serie) {
            $serie->url = $serie->setUrl();
        }

        return $serie;
    }

    public function loadAuthor()
    {
        if (!class_exists('\Winter\User\Models\User') || !$this->property('authorFilter')) return null;
        $authorId = $this->property('authorFilter');

        return \Winter\User\Models\User::find($authorId);
    }

    public function loadItems()
    {
        if (!$options = $this->getListingSetting()) return;
        

        $withs = ['category', 'group', 'serie', 'images'];
        if (class_exists('\Winter\User\Models\User')) {
            array_push($withs, 'author');
        }

        $items = Item::with($withs)->listFrontEnd($options);

        if (!$items) return;

        $categorySlug = $this->category ? $this->category->slug : null;
        $groupSlug = $this->group ? $this->group->slug : null;
        $serieSlug = $this->serie ? $this->serie->slug : null;

        $items->each(function($item) use ($categorySlug, $groupSlug, $serieSlug) {
            $item->setUrl($this->detailPage, $this->controller, ['category' => $categorySlug, 'group' => $groupSlug, 'serie' => $serieSlug]);
            if ($item->category) {
                $item->category->url = $item->category->setUrl($this->listPage, $this->controller);
            }
            $item->categories->each(function($category) {
                $category->setUrl($this->listPage, $this->controller);
            });
        });

        return $items;
    }

    public function getListingSetting() : array
    {
        return [];
    }

    /**
     * Set page meta|og tags
     *
     * @param collection $item
     */
    protected function setPageMeta($item)
    {
        if (!$item) return;

        // General Meta Tags

        $this->page->title              = $this->page->meta_title         = $this->page->og_title = $item->meta_title;
        $this->page->meta_description   = $this->page->og_description     = $item->meta_description;
        $this->page->meta_keywords      = $item->meta_keywords;

        // Extra Og Meta Tags
        $this->page->og_image           = $item->og_image;
        $this->page->og_type            = $item->og_type;
    }

    /**
     * @param string $componentName
     * @param string $page
     * @return ComponentBase|null
     */
    protected function getComponent(string $componentName, string $page)
    {
        $component = null;

        $page = Page::load(Theme::getActiveTheme(), $page);

        if (!is_null($page)) {
            $component = $page->getComponent($componentName);
        }

        return $component;
    }

    /**
     * A helper function to get the real URL parameter name. For example, slug for posts
     * can be injected as :post into URL. Real argument is necessary if you want to generate
     * valid URLs for such pages
     *
     * @param ComponentBase|null $component
     * @param string $name
     *
     * @return string|null
     */
    protected function urlProperty(ComponentBase $component = null, string $name = '')
    {
        $property = null;

        if ($component !== null && ($property = $component->property($name))) {
            preg_match('/{{ :([^ ]+) }}/', $property, $matches);

            if (isset($matches[1])) {
                $property = $matches[1];
            }
        } else {
            $property = $name;
        }

        return $property;
    }

    protected function getGItemsComponent($page) {
        return $this->getComponent('gItems', $page);
    }

    protected function getGItemComponent($page) {
        return $this->getComponent('gItem', $page);
    }
}
