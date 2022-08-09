<?php namespace GemFourMedia\GContent\Behaviors;

use Db;
use Str;
use Lang;
use Flash;
use Event;
use Redirect;
use Backend;
use Backend\Classes\ControllerBehavior;
use October\Rain\Html\Helper as HtmlHelper;
use October\Rain\Router\Helper as RouterHelper;
use ApplicationException;
use Exception;

/**
 * Adds features for working with backend list. This behavior
 * will inject batch update actions to the controller
 *
 *
 * This behavior is implemented in the controller like so:
 *
 *     public $implement = [
 *         'GemFourMedia.GContent.Behaviors.BatchUpdateController',
 *     ];
 *
 *     public $batchUpdateConfig = 'config_batchupdate.yaml';
 *
 * The `$batchUpdateConfig` property makes reference to the form configuration
 * values as either a YAML file, located in the controller view directory,
 * or directly as a PHP array.
 *
 */
class BatchUpdateController extends ControllerBehavior
{

    /**
     * @var \Backend\Classes\Controller|FormController Reference to the back end controller.
     */
    protected $controller;

    /**
     * @var \Backend\Widgets\Form Reference to the widget object.
     */
    protected $batchWidget;

    /**
     * @inheritDoc
     */
    protected $requiredProperties = ['batchUpdateConfig'];

    /**
     * @var array Configuration values that must exist when applying the primary config file.
     * - modelClass: Class name for the model
     * - form: Form field definitions
     */
    protected $requiredConfig = ['modelClass', 'form', 'batchMethod'];


    /**
     * @var Model The initialized model used by the form.
     */
    protected $model;

    /**
     * Behavior constructor
     * @param Backend\Classes\Controller $controller
     */
    public function __construct($controller)
    {
        parent::__construct($controller);

        /*
         * Build configuration
         */
        $this->config = $this->makeConfig($controller->batchUpdateConfig, $this->requiredConfig);
        $this->config->modelClass = Str::normalizeClassName($this->config->modelClass);
        $this->makeBatchWidget();
    }

    protected function makeBatchWidget() {
        $fields = \Yaml::parse($this->config->form);
        $formFields = $this->getConfig("form", $this->config->form);

        $config = $this->makeConfig($formFields);
        $config->model = $this->createModel();
        $config->alias = 'form_item_batch';
        $config->arrayName = 'itemBatch';
        
        $this->batchWidget = $this->makeWidget('Backend\Widgets\Form', $config);
        $this->batchWidget->isNested = true; // Avoid extends fields from global event
        $this->batchWidget->bindToController();
    }

    public function onLoadBatchModal() {
        $this->makeBatchWidget();
        $this->vars['selectedItems'] = json_encode(post('checked',[]));
        $this->vars['batchWidget'] = $this->batchWidget;
        return $this->makePartial('batch_modal');
    }

    public function onBatchUpdate() {
        // print_r(unserialize(post()));
        $batchWidgetPost = post('itemBatch', []);
        if ($batchWidgetPost) {
            $model = $this->createModel();
            $batchMethod = $this->getConfig('batchMethod', $this->config->batchMethod);
            $model->$batchMethod($batchWidgetPost);
            Flash::success('Batch Success!');
            return Redirect::refresh();
        }

        Flash::error('Batch fail!');
        return Redirect::refresh();
    }

    /**
     * Internal method used to prepare the form model object.
     *
     * @return October\Rain\Database\Model
     */
    protected function createModel()
    {
        $class = $this->config->modelClass;
        return new $class;
    }


    /**
     * Parses in some default variables to a language string defined in config.
     *
     * @param string $name Configuration property containing the language string
     * @param string $default A default language string to use if the config is not found
     * @param array $extras Any extra params to include in the language string variables
     * @return string The translated string.
     */
    protected function getLang($name, $default = null, $extras = [])
    {
        $name = $this->getConfig($name, $default);
        $vars = [
            'name' => Lang::get($this->getConfig('name', 'backend::lang.model.name'))
        ];
        $vars = array_merge($vars, $extras);
        return Lang::get($name, $vars);
    }
}
