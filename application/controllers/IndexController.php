<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        //TODO: debug code entfernen
        $model = new Azebo_Model_Mitarbeiter();
        $mitarbeiter = $model->getMitarbeiterNachId(1);
        $name = $mitarbeiter->getName();
        
        $logger = Zend_Registry::get('log');
        $logger->info("Der Name war: $name");
        //entfernen bis hierher
    }


}

