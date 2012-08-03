<?php

class MonatController extends AzeboLib_Controller_Abstract {

    public function init()
    {
        parent::init();
    }
    
    public function getSeitenName() {
        return 'Monatsübersicht';
    }

    public function indexAction()
    {
        $this->view->monat = $this->_getParam('monat');
        $this->view->jahr = $this->_getParam('jahr');
    }


}

