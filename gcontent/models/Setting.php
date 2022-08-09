<?php namespace GemFourMedia\GContent\Models;

use Model;
use Carbon\Carbon;
use Lang;

/**
 * Model
 */
class Setting extends Model
{
    use \GemFourMedia\GContent\Traits\ListOptionsHelper;
    
    /**
     * @var array
     */
    public $implement = ['System.Behaviors.SettingsModel'];

    /**
     * @var string
     */
    public $settingsCode = 'gemfourmedia_gcontent_setting';

    /**
     * @var string
     */
    public $settingsFields = 'fields.yaml';

    public function getDateFormat(){
        $format = (self::get('dateFormat') == 'custom') ? self::get('dateFormatCustom'): self::get('dateFormat');
        return $format;
    }

    public function getTimeFormat(){
        $format = (self::get('timeFormat') == 'custom') ? self::get('timeFormatCustom') : self::get('timeFormat');
        return $format;
    }
    
    public function setFormatedDateTime(Carbon $date) {
        $dateFormat = $this->getDateFormat();
        $timeFormat = $this->getTimeFormat();
        $date->setLocale(Lang::getLocale());
        return $date->format($dateFormat.' '. $timeFormat);
    }

}
