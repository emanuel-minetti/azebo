<?php

class IndexController extends AzeboLib_Controller_Abstract {

    public function init() {
        parent::init();
        $this->_log->info(__METHOD__);
    }
    public function getSeitenName() {
        return 'Übersicht';
    }

    public function indexAction() {
        
    }
    
}

