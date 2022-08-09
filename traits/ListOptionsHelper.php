<?php namespace GemFourMedia\GContent\Traits;

use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;
use GemFourMedia\GContent\Models\Category;
use GemFourMedia\GContent\Models\Group;
use GemFourMedia\GContent\Models\Serie;

trait ListOptionsHelper {
    
    public function getCategoryFilterOptions()
    {
        return ['' => 'Select Category'] + (new Category)->listsNested('name', 'slug');
    }
    
    public function getSerieFilterOptions()
    {
        return ['' => 'Select Serie'] + (new Serie)->lists('name', 'slug');
    }
    
    public function getGroupFilterOptions()
    {
        return ['' => 'Select Group'] + (new Group)->lists('name', 'slug');
    }
    
    /**
     * List Categories
     * @return array
     */
    public function listCategories() {
        return Category::get()->listsNested('name', 'id');
    }
    
    /**
     * List Content Group
     * @return array
     */
    public function listContentGroup() {
        return Group::get()->lists('name', 'id');
    }
    
    /**
     * List Series
     * @return array
     */
    public function listSeries() {
        return Serie::get()->lists('name', 'id');
    }
    
    /**
     * List CMS pages
     * @return array
     */
    public function listCmsPages() {
        return CmsPage::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    /**
     * List CMS pages which have gItem Component
     * @return array
     */
    public function listCmsItemPages(){
        return $this->listGContentCmsPages('gItem');
    }

    /**
     * List CMS pages which have gItems Component
     * @return array
     */
    public function listCmsItemsPages(){
        return $this->listGContentCmsPages('gItems');
    }

    /**
     * List CMS pages which has specific GContent components
     * @param string
     * @return array
     */
    public function listGContentCmsPages($componentName = '', $exceptPage='') {
        $theme = Theme::getActiveTheme();

        $pages = CmsPage::listInTheme($theme, true);
        $cmsPages = [];

        foreach ($pages as $page) {
            if (!$page->hasComponent($componentName)) {
                continue;
            }
            if ($exceptPage && $page->baseFileName == $exceptPage) {
                continue;
            }
            $cmsPages[$page->baseFileName] = $page->baseFileName;
        }
        return $cmsPages;
    }

}