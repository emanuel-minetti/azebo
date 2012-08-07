<?php

class MonatController extends AzeboLib_Controller_Abstract {

    public function init() {
        parent::init();
    }

    public function getSeitenName() {
        return 'Monatsübersicht';
    }

    public function indexAction() {

        $jahr = $this->_getParam('jahr');
        $monat = $this->_getParam('monat');

        $this->view->jahr = $jahr;
        $this->view->monat = $monat;

        $feiertagsService = new Azebo_Service_Feiertag($jahr);

        // setze $datum auf den ersten des Monats
        $datum = new Zend_Date(array(
                    'day' => 1,
                    'month' => $monat,
                    'year' => $jahr,
                ));
        
        //Finde die Anzahle der Tage in diesem Monat
        $tageImMonat = $datum->get(Zend_Date::MONTH_DAYS);
        $this->view->tageImMonat = $tageImMonat;

        // setze den Seitennamen
        $this->erweitereSeitenName($datum->toString(' MMMM yyyy'));

        //aktiviere Dojo
        $this->view->dojo()->enable()
                ->setDjConfigOption('parseOnLoad', true)
                ->requireModule('dojox.grid.DataGrid')
                ->requireModule('dojo.data.ItemFileReadStore')
                ->requireModule('dojo._base.connect');

        // befülle die Reihen
        $monatsDaten = new Zend_Dojo_Data();
        $monatsDaten->setIdentifier('datum');
        for ($tag = 1; $tag <= $tageImMonat; $tag++) {
            $datum->setDay($tag);
            $feiertag = $feiertagsService->feiertag($datum);
            $monatsDaten->addItem(array(
                'datum' => $feiertag['name'] . ' ' . $datum->toString('EE, dd.MM.YYYY'),
                'feiertag' => $feiertag['feiertag'],
            ));
        }

        $this->view->monatsDaten = $monatsDaten;
    }

    public function editAction() {
        $jahr = $this->_getParam('jahr');
        $monat = $this->_getParam('monat');
        $tag = $this->_getParam('tag');

        $this->view->jahr = $jahr;
        $this->view->monat = $monat;
        $this->view->tag = $tag;

        $feiertagsService = new Azebo_Service_Feiertag($jahr);
        $zuBearbeitenderTag = new Zend_Date(array(
                    'day' => $tag,
                    'month' => $monat,
                    'year' => $jahr,
                ));
        
        $tageImMonat = $zuBearbeitenderTag->get(Zend_Date::MONTH_DAYS);
        $this->view->tageImMonat = $tageImMonat;

        // setze den Seitennamen
        $this->erweitereSeitenName('-Bearbeite ');
        $this->erweitereSeitenName($zuBearbeitenderTag->toString('d.M.yy'));

        //aktiviere Dojo
        $this->view->dojo()->enable()
                ->setDjConfigOption('parseOnLoad', true)
                ->requireModule('dojox.grid.DataGrid')
                ->requireModule('dojo.data.ItemFileReadStore')
                ->requireModule('dojo._base.connect');

        if ($tag != 1) {
            $datum = new Zend_Date(array(
                        'day' => 1,
                        'month' => $monat,
                        'year' => $jahr,
                    ));
            $monatsDatenOben = new Zend_Dojo_Data();
            $monatsDatenOben->setIdentifier('datum');
            for ($tagIndex = 1; $tagIndex < $tag; $tagIndex++) {
                $datum->setDay($tagIndex);
                $feiertag = $feiertagsService->feiertag($datum);
                $monatsDatenOben->addItem(array(
                    'datum' => $feiertag['name'] . ' ' . $datum->toString('EE, dd.MM.YYYY'),
                    'feiertag' => $feiertag['feiertag'],
                ));
            }
        } else {
            $monatsDatenOben = null;
        }

        if ($tag != $tageImMonat) {
            // setze $datum auf den auf $tag folgenden Tag des Monats
            $datum = new Zend_Date(array(
                        'day' => $tag + 1,
                        'month' => $monat,
                        'year' => $jahr,
                    ));
            $monatsDatenUnten = new Zend_Dojo_Data();
            $monatsDatenUnten->setIdentifier('datum');
            for ($tagIndex = $tag + 1; $tagIndex <= $tageImMonat; $tagIndex++) {
                $datum->setDay($tagIndex);
                $feiertag = $feiertagsService->feiertag($datum);
                $monatsDatenUnten->addItem(array(
                    'datum' => $feiertag['name'] . ' ' . $datum->toString('EE, dd.MM.YYYY'),
                    'feiertag' => $feiertag['feiertag'],
                ));
            }
        } else {
            $monatsDatenUnten = null;
        }

        $this->view->monatsDatenOben = $monatsDatenOben;
        $this->view->monatsDatenUnten = $monatsDatenUnten;
    }

}
