<?php

/*
 * 
 *     This file is part of azebo.
 * 
 *     azebo is free software: you can redistribute it and/or modify
 *     it under the terms of the GNU General Public License as published by
 *     the Free Software Foundation, either version 3 of the License, or
 *     (at your option) any later version.
 * 
 *     azebo is distributed in the hope that it will be useful,
 *     but WITHOUT ANY WARRANTY; without even the implied warranty of
 *     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *     GNU General Public License for more details.
 * 
 *     You should have received a copy of the GNU General Public License
 *     along with azebo.  If not, see <http://www.gnu.org/licenses/>.
 *  
 *     Copyright 2012 Emanuel Minetti (e.minetti (at) arcor.de)
 */

class MonatController extends AzeboLib_Controller_Abstract {

    public $tag;
    public $monat;
    public $jahr;
    public $tageImMonat;

    /**
     * @var Azebo_Service_Zeitrechner
     */
    public $zeitrechner;

    /**
     * @var Zend_Date 
     */
    public $zuBearbeitendesDatum;

    /**
     * @var Azebo_Resource_Mitarbeiter_Item_Interface 
     */
    public $mitarbeiter;

    /**
     * @var array 
     */
    public $arbeitstage;

    public function init() {
        parent::init();

        $heute = new Zend_Date();

        // Hohle die Parameter
        $this->tag = $this->_getParam('tag', $heute->get(Zend_Date::DAY));
        $this->monat = $this->_getParam('monat', $heute->get(Zend_Date::MONTH));
        $this->jahr = $this->_getParam('jahr', $heute->get(Zend_Date::DAY));

        $this->view->tag = $this->tag;
        $this->view->monat = $this->monat;
        $this->view->jahr = $this->jahr;

        // Setze das Datum
        $this->zuBearbeitendesDatum = new Zend_Date(array(
                    'day' => $this->tag,
                    'month' => $this->monat,
                    'year' => $this->jahr,
                ));

        // Ermittle die Anzahl der Tage des Monats
        $this->tageImMonat = $this->zuBearbeitendesDatum
                ->get(Zend_Date::MONTH_DAYS);
        $this->view->tageImMonat = $this->tageImMonat;

        // Initialisiere den Feiertagsservice mit dem zu bearbeitendem Jahr
        // und übergebe den Service an die Session. Arbeitstag_Item greift
        // darauf zu.
        $feiertagsservice = new Azebo_Service_Feiertag($this->jahr);
        $ns = new Zend_Session_Namespace();
        $ns->feiertagsservice = $feiertagsservice;

        // Aktiviere Dojo
        $this->view->dojo()->enable()
                ->setDjConfigOption('parseOnLoad', true)
                ->requireModule('dojox.grid.DataGrid')
                ->requireModule('dojo.data.ItemFileReadStore')
                ->requireModule('dojo._base.connect');

        // Lade den Mitarbeiter und die Arbeitstage
        $authService = new Azebo_Service_Authentifizierung();
        $this->mitarbeiter = $authService->getIdentity();
        $this->arbeitstage = $this->mitarbeiter
                ->getArbeitstageNachMonat($this->zuBearbeitendesDatum);

        // Speichere den Mitarbeiter in der Session
        //TODO Stellvertreter implementieren!
        $ns->mitarbeiter = $this->mitarbeiter;

        // Stelle den Zeitrechner-Service zur Verfügung
        $this->zeitrechner = new Azebo_Service_Zeitrechner();
        
        //Saldo bis zum Vormonat setzen
        $this->view->saldoBisher = $this->mitarbeiter->getSaldoBisher();
    }

    public function getSeitenName() {
        return 'Monatsübersicht';
    }

    public function indexAction() {

        $request = $this->getRequest();
        $abschlussForm = $this->_getMitarbeiterAbschlussForm();
        if ($request->isPost()) {
            // ist Post-Request, also prüfen ob 'prüfen' gedrückt wurde
            $postDaten = $request->getPost();
            if (isset($postDaten['pruefen'])) {
                $valid = $abschlussForm->isValid($postDaten);
                $daten = $abschlussForm->getValues();
            }
        }

        $datum = new Zend_Date($this->zuBearbeitendesDatum);

        // setze den Seitennamen
        $this->erweitereSeitenName($datum->toString(' MMMM yyyy'));

        // befülle die Reihen der Tabelle
        $erster = new Zend_Date($this->zuBearbeitendesDatum);
        $letzter = new Zend_Date($this->zuBearbeitendesDatum);
        $erster->setDay(1);
        $letzter->setDay($this->tageImMonat);
        $tabelle = $this->_befuelleDieTabelle($erster, $letzter);

        // füge die Tabelle dem View hinzu
        $this->view->monatsDaten = $tabelle['tabellenDaten'];
        $this->view->hoheTageImMonat = $tabelle['hoheTage'];

        // die Form für den Monatsabschluss hinzufügen
        $this->view->monatForm = $abschlussForm;
    }

    public function editAction() {
        $request = $this->getRequest();
        $form = $this->_getMitarbeiterTagForm();

        if ($request->isPost()) {
            // ist Post-Request, also prüfen ob 'absenden' gedrückt wurde
            $postDaten = $request->getPost();
            if (isset($postDaten['absenden'])) {
                // 'absenden' wurde gedrückt, also Daten filtern und validieren!
                $valid = $form->isValid($postDaten);
                $daten = $form->getValues();
                // bevölkere das Beginn- und Ende-Element
                $form->setBeginn($daten['beginn']);
                $form->setEnde($daten['ende']);
                if ($valid) {
                    // ist valide also, speichen und redirect
                    $this->mitarbeiter->saveArbeitstag(
                            $this->zuBearbeitendesDatum, $daten);
                    $redirector = $this->_helper->getHelper('Redirector');
                    $redirector->gotoRoute(array(
                        'jahr' => $this->jahr,
                        'monat' => $this->monat,
                            ), 'monat');
                }
                // nicht valide, also tue nichts und rendere die Seite mit
                // Fehlermeldungen neu.
            }
            // 'zrücksetzen' wurde gedrückt, also tue nichts sondern, rendere
            // einfach die Seite neu
        }

        // rendere die Seite
        $this->view->tagForm = $form;
        // setze den Seitennamen
        $this->erweitereSeitenName(' - Bearbeite ');
        $this->erweitereSeitenName($this->zuBearbeitendesDatum
                        ->toString('d.M.yy'));

        // initialisiere zwei Daten
        $erster = new Zend_Date($this->zuBearbeitendesDatum);
        $letzter = new Zend_Date($this->zuBearbeitendesDatum);

        // befülle die obere Tabelle
        if ($this->tag != 1) {
            $erster->setDay(1);
            $letzter->setDay($this->tag - 1);
            $tabelle = $this->_befuelleDieTabelle($erster, $letzter);
            $this->view->monatsDatenOben = $tabelle['tabellenDaten'];
            $this->view->hoheTageImMonatOben = $tabelle['hoheTage'];
        } else {
            $this->view->monatsDatenOben = null;
            $this->view->hoheTageImMonatOben = 0;
        }

        //befülle die untere Tabelle
        if ($this->tag != $this->tageImMonat) {
            $erster->setDay($this->tag + 1);
            $letzter->setDay($this->tageImMonat);
            $tabelle = $this->_befuelleDieTabelle($erster, $letzter);
            $this->view->monatsDatenUnten = $tabelle['tabellenDaten'];
            $this->view->hoheTageImMonatUnten = $tabelle['hoheTage'];
        } else {
            $this->view->monatsDatenUnten = null;
            $this->view->hoheTageImMonatUnten = 0;
        }
    }

    private function _getMitarbeiterTagForm() {
        $model = new Azebo_Model_Mitarbeiter();
        $form = $model->getForm('mitarbeiterTag');
        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
            'tag' => $this->tag,
            'monat' => $this->monat,
            'jahr' => $this->jahr,
                ), 'monatEdit', true);
        $url .= '#form';
        $form->setAction($url);
        $form->setMethod('post');
        $form->setName('tagForm');
        return $form;
    }

    private function _getMitarbeiterAbschlussForm() {
        $model = new Azebo_Model_Mitarbeiter();
        $form = $model->getForm('mitarbeiterAbschluss');
        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
            'monat' => $this->monat,
            'jahr' => $this->jahr,
                ), 'monat', true);
        $url .= '#monatForm';
        $form->setAction($url);
        $form->setMethod('post');
        $form->setName('monatForm');

        $monatElement = $form->getElement('monat');
        $monatElement->setValue($this->zuBearbeitendesDatum->toString('MM.YYYY'));
        return $form;
    }

    private function _befuelleDieTabelle(Zend_Date $erster, Zend_Date $letzter) {
        // Hole die Befreiungsoptionen für diesen Mitarbeiter
        $befreiungService = new Azebo_Service_Befreiung();
        $befreiungOptionen = $befreiungService->getOptionen($this->mitarbeiter);

        // Initialisiere die Daten
        $tabellenDaten = new Zend_Dojo_Data();
        $tabellenDaten->setIdentifier('datum');
        $anzahlHoheTage = 0;

        // Iteriere über die Tage
        foreach ($this->arbeitstage as $arbeitstag) {
            if ($arbeitstag->tag->compare($erster, Zend_Date::DATE_MEDIUM) != -1 &&
                    $arbeitstag->tag->compare($letzter, Zend_Date::DATE_MEDIUM) != 1) {

                $tag = $arbeitstag->tag;
                $feiertag = $arbeitstag->feiertag;
                $beginn = null;
                $ende = null;
                $befreiung = null;
                $anwesend = null;
                $ist = null;
                $soll = null;
                $saldo = null;

                if ($arbeitstag->beginn !== null) {
                    $beginn = $arbeitstag->beginn->toString('HH:mm');
                }
                if ($arbeitstag->ende !== null) {
                    $ende = $arbeitstag->ende->toString('HH:mm');
                }
                if ($arbeitstag->befreiung !== null) {
                    $befreiung = $befreiungOptionen[$arbeitstag->befreiung];
                }
                if ($arbeitstag->getRegel() !== null) {
                    $soll = $arbeitstag->regel->soll->toString('HH:mm');
                }

                $anwesend = $arbeitstag->getAnwesend();
                $ist = $arbeitstag->getIst();
                $saldoErg = $arbeitstag->getSaldo();
                if($anwesend !== null) {
                    $anwesend = $anwesend->toString('HH:mm');
                    $ist = $ist->toString('HH:mm');
                    $saldo = $saldoErg['positiv'] ?
                            $saldoErg['saldo']->toString('+ HH:mm') :
                            $saldoErg['saldo']->toString('- HH:mm');
                }

                $tabellenDaten->addItem(array(
                    'datum' => $feiertag['name'] . ' ' . $tag->toString('EE, dd.MM.YYYY'),
                    'feiertag' => $feiertag['feiertag'],
                    'beginn' => $beginn,
                    'ende' => $ende,
                    'befreiung' => $befreiung,
                    'bemerkung' => $arbeitstag->bemerkung,
                    'pause' => $arbeitstag->pause,
                    'anwesend' => $anwesend,
                    'ist' => $ist,
                    'soll' => $soll,
                    'saldo' => $saldo,
                ));

                //Neujahr und Karfreitag passen in eine Zeile mit dem Wochentag,
                //sind also keine 'hohen' Tage.
                if ($feiertag['name'] != '') {
                    if ($feiertag['name'] != 'Neujahr' &&
                            $feiertag['name'] != 'Karfreitag') {
                        $anzahlHoheTage++;
                    }
                }
            }
        }

        return array(
            'tabellenDaten' => $tabellenDaten,
            'hoheTage' => $anzahlHoheTage,
        );
    }

}
