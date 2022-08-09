<?php namespace GemFourMedia\GContent\Traits;
use System\Models\File;

trait SEOHelper {
	
	/**
	 * Meta tag generator (should use in beforeValidate)
	 * ---
	 * @var string $title
	 * @var string $description
	 * @var string $keywords
	 */
	public function setMetaTags($title='', $description='', $keywords='')
	{
		if (isset($this->meta_title)) {
			$meta_title = !empty($this->meta_title) ? $this->meta_title : $title;
			$this->meta_title = str_limit(strip_tags($meta_title), 191, '');
		}
		
		if (isset($this->meta_description)) {
			$meta_description = !empty($this->meta_description) ? $this->meta_description : $description;
			$this->meta_description = str_limit(strip_tags($meta_description), 191, '');
		}
		
		if (isset($this->meta_keywords)) {
			$meta_keywords = !empty($this->meta_keywords) ? $this->meta_keywords : $keywords;
			$this->meta_keywords = str_limit(strip_tags($meta_keywords), 191, '');
		}
	}

	public function getOgTitleAttribute()
	{
		if ($this->meta_title) return $this->meta_title;
	}

	public function getOgDescriptionAttribute()
	{
		if ($this->meta_description) return $this->meta_description;
	}

	public function getOgImageAttribute()
	{
		$ogImage = [
			'path' => null,
			'width' => null,
			'height' => null,
		];
		if (! $imageField = $this->{$this->ogImageField}) return $ogImage;
		
		if ( $imageField instanceof File ) {
			$ogImage['path'] = $imageField->getPath();
			$ogImage['width'] = $imageField->width;
			$ogImage['height'] = $imageField->height;
		}
		else {
			$filePath = base_path(config('cms.storage.media.path') . $imageField);
			if (is_file($filePath)) {
				list($width, $height) = getimagesize($filePath);

				$ogImage['path'] = url(config('cms.storage.media.path') . $imageField);
				$ogImage['width'] = $width;
				$ogImage['height'] = $height;
			}
		}

		return $ogImage;
	}


	public function getOgTypeAttribute()
	{
		return isset($this->ogType) ? $this->ogType : 'website';
	}
}