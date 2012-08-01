<?php

class IndexController extends Zend_Controller_Action {

    public function init() {
        /* Initialize action controller here */
    }

    public function indexAction() {
        $authService = new Azebo_Service_Authentication();
        $this->view->name = $authService->getIdentity()->getName();
        //TODO: debug code entfernen
        $logger = Zend_Registry::get('log');
        $authService = new Azebo_Service_Authentication();
        if ($authService->getIdentity()) {
            $logger->info("Eingeloggt ist: {$authService->getIdentity()->getName()}");
        } else {
            $logger->info('Niemand ist eingeloggt!');
        }
        //entfernen bis hierher
    }

}

