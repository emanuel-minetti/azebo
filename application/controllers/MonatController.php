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
        $datum->setDay(1);
        $datum->setMonth($monat);
        $datum->setYear($jahr);
        
        $this->erweitereSeitenName($datum->toString(' MMMM'));
        $this->erweitereSeitenName($datum->toString(' yyyy'));

        $this->view->dojo()->enable()
                ->setDjConfigOption('parseOnLoad', true)
                ->requireModule('dojox.grid.DataGrid')
                ->requireModule('dojo.data.ItemFileReadStore')
                ->requireModule('dojo._base.connect');

        $daten = new Zend_Dojo_Data();
        $daten->setIdentifier('number');
        $daten->addItem(array(
            'number' => '12',
            'name' => 'Jim Kelly',
            'position' => 'QB',
            'victories' => '0',
        ));
        $daten->addItem(array(
            'number' => '34',
            'name' => 'Thurman Thomas',
            'position' => 'RB',
            'victories' => '0',
        ));
        $daten->addItem(array(
            'number' => '89',
            'name' => 'Steve Tasker',
            'position' => 'WR',
            'victories' => '0',
        ));
        $daten->addItem(array(
            'number' => '78',
            'name' => 'Bruce Smith',
            'position' => 'DE',
            'victories' => '0',
        ));
        $this->view->daten = $daten;
    }

}

