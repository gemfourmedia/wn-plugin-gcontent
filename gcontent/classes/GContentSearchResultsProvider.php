<?php

namespace GemFourMedia\GContent\Classes;

use Carbon\Carbon;
use Cms\Classes\Controller;
use DB;
use Illuminate\Database\Eloquent\Collection;
use OFFLINE\SiteSearch\Classes\Result;
use OFFLINE\SiteSearch\Models\Settings;
use GemFourMedia\GContent\Models\Item;
use Throwable;
use OFFLINE\SiteSearch\Classes\Providers\ResultsProvider;

/**
 * Searches the contents generated by the
 * GemFourMedia.GContent plugin
 *
 * @package OFFLINE\SiteSearch\Classes\Providers
 */
class GContentSearchResultsProvider extends ResultsProvider
{

    /**
     * Runs the search for this provider.
     *
     * @return ResultsProvider
     */
    public function search()
    {
        if ( ! $this->isInstalledAndEnabled()) {
            return $this;
        }

        foreach ($this->gItems() as $gItem) {
            // Make this result more relevant, if the query is found in the title
            $relevance = mb_stripos($gItem->title, $this->query) === false ? 1 : 2;

            if ($relevance > 1 && $gItem->published_at) {
                // Make sure that `published_at` is a Carbon object
                $publishedAt = $gItem->published_at;
                if (is_string($publishedAt)) {
                    try {
                        $publishedAt = Carbon::parse($publishedAt);
                    } catch (Throwable $e) {
                        // If parsing fails use the current date.
                        $publishedAt = Carbon::now();
                    }
                }
                $relevance -= $this->getAgePenalty($publishedAt->diffInDays(Carbon::now()));
            }

            $result        = new Result($this->query, $relevance);
            $result->title = $gItem->title;
            $result->text  = $gItem->introtext;
            $result->meta  = $gItem->published_at;
            $result->model = $gItem;
            $result->url = $gItem->setUrl();
            $result->thumb = isset($gItem->main_image) ? $gItem->main_image : null;
            
            $this->addResult($result);
        }

        return $this;
    }

    /**
     * Get all gItems with matching title or content.
     *
     * @return Collection
     */
    protected function gItems()
    {
        // If Rainlab.Translate is not installed or we are currently,
        // using the default locale we simply query the default table.
        $translator = $this->translator();
        if ( ! $translator || $translator->getDefaultLocale() === $translator->getLocale()) {
            return $this->itemsFromDefaultLocale();
        }

        // If Rainlab.Translate is available we also have to
        // query the rainlab_translate_attributes table for translated
        // contents since the title and content attributes on the Post
        // model are not indexed.
        return $this->itemsFromCurrentLocale();
    }

    /**
     * Returns all matching gItems from the default locale.
     * Translated attributes are ignored.
     *
     * @return Collection
     */
    protected function itemsFromDefaultLocale()
    {
        return $this->defaultModelQuery()
                    ->where(function ($query) {
                        $query->where('title', 'like', "%{$this->query}%")
                              ->orWhere('content_html', 'like', "%{$this->query}%")
                              ->orWhere('introtext', 'like', "%{$this->query}%");
                    })
                    ->get();
    }

    /**
     * Returns all matching gItems with translated contents.
     *
     * @return Collection
     */
    protected function itemsFromCurrentLocale()
    {
        // First fetch all model ids with maching contents.
        $results = DB::table('winter_translate_attributes')
                     ->where('locale', $this->currentLocale())
                     ->where('model_type', Item::class)
                     ->where('attribute_data', 'LIKE', "%{$this->query}%")
                     ->get(['model_id']);

        $ids = collect($results)->pluck('model_id');

        // Then return all maching gItems via Eloquent.
        return $this->defaultModelQuery()->whereIn('id', $ids)->get();
    }

    /**
     * This is the default "base query" for quering
     * matching models.
     */
    protected function defaultModelQuery()
    {
        return Item::isPublished()->with(['images', 'group', 'category']);
    }

    /**
     * Checks if the RainLab.Blog Plugin is installed and
     * enabled in the config.
     *
     * @return bool
     */
    protected function isInstalledAndEnabled()
    {
        return $this->isPluginAvailable($this->identifier);
    }


    /**
     * Display name for this provider.
     *
     * @return mixed
     */
    public function displayName()
    {
        return 'Article';
    }

    /**
     * Returns the plugin's identifier string.
     *
     * @return string
     */
    public function identifier()
    {
        return 'GemFourMedia.GContent';
    }

    /**
     * Return the current locale
     *
     * @return string|null
     */
    protected function currentLocale()
    {
        $translator = $this->translator();

        if ( ! $translator) {
            return null;
        }

        return $translator->getLocale();
    }
}
