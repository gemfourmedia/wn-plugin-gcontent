<?php namespace GemFourMedia\GContent\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Serie extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'gcontent.serie.manage', 
        'gcontent.serie.create', 
        'gcontent.serie.update', 
        'gcontent.serie.delete' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('GemFourMedia.GContent', 'gcontent-main-menu', 'gcontent-menu-serie');
    }
}
