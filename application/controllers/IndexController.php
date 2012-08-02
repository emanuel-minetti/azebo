<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $authService = new Azebo_Service_Authentication();
        if($authService->getIdentity()) {
            $this->view->name = $authService->getIdentity()->getName();
        }
    }

}

