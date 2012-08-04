<?php

class MonatController extends AzeboLib_Controller_Abstract {

    public function init() {
        parent::init();
    }

    public function getSeitenName() {
        return 'Monatsübersicht';
    }

    public function indexAction() {
        $monat = $this->_getParam('monat');
        $jahr = $this->_getParam('jahr');
        
        $this->view->monat = $monat;
        $this->view->jahr = $jahr;
        
        $datum = new Zend_Date();
        // setze $datum auf den ersten des Monats
        $datum->setDay(1);
        $datum->setMonth($monat);
        $datum->setYear($jahr);
        
        // setze den Seitennamen
        $this->erweitereSeitenName($datum->toString(' MMMM'));
        $this->erweitereSeitenName($datum->toString(' yyyy'));

        //aktiviere Dojo
        $this->view->dojo()->enable()
                ->setDjConfigOption('parseOnLoad', true)
                ->requireModule('dojox.grid.DataGrid')
                ->requireModule('dojo.data.ItemFileReadStore')
                ->requireModule('dojo._base.connect');
        
        // befülle die Reihen
        $monatsDaten = new Zend_Dojo_Data();
        $monatsDaten->setIdentifier('datum');
        for ($tag = 1; $tag <= $datum->get(Zend_Date::MONTH_DAYS) ; $tag++) {
            $datum->setDay($tag);
            $monatsDaten->addItem(array(
                'datum' => $datum->toString('EE, dd.MM.YYYY'),
            ));
        }
        
        $this->view->monatsDaten = $monatsDaten;
    }

}

