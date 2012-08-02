<?php

class MonatController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->view->monat = $this->_getParam('monat');
        $this->view->jahr = $this->_getParam('jahr');
    }


}

