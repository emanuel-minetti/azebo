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

    /**
     * @var boolean
     */
    public $bearbeitbar;

    public function init() {
        parent::init();

        $heute = new Zend_Date();

        // Hohle die Parameter
        $this->tag = $this->_getParam('tag', $heute->get(Zend_Date::DAY));
        $this->monat = $this->_getParam('monat', $heute->get(Zend_Date::MONTH));
        $this->jahr = $this->_getParam('jahr', $heute->get(Zend_Date::YEAR));

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
                ->requireModule('dojo._base.connect')
                ->requireModule('dijit.Tooltip');

        // Lade den Mitarbeiter und die Arbeitstage
        $this->mitarbeiter = $ns->mitarbeiter;
        $this->arbeitstage = $this->mitarbeiter
                ->getArbeitstageNachMonat($this->zuBearbeitendesDatum);
        
        // Stelle den Zeitrechner-Service zur Verfügung
        $this->zeitrechner = new Azebo_Service_Zeitrechner();

        //Saldo bis zum Vormonat setzen
        $this->view->saldoBisher = $this->mitarbeiter->getSaldoBisher()->
                getString();
        $this->view->saldo = $this->mitarbeiter->getSaldo(
                        $this->zuBearbeitendesDatum, true)->getString();
        $this->view->urlaubBisher = $this->mitarbeiter->getUrlaubBisher();
        $this->view->urlaub = $this->mitarbeiter->getUrlaubNachMonat(
                $this->zuBearbeitendesDatum);

        //prüfe ob bereits abgeschlossen
        $this->bearbeitbar = true;
        $arbeitmonate = $this->mitarbeiter->getArbeitsmonate();
        foreach ($arbeitmonate as $arbeitsmonat) {
            if ($this->zuBearbeitendesDatum->compareMonth(
                            $arbeitsmonat->getMonat()) == 0) {
                $this->bearbeitbar = false;
                break;
            }
        }
        $this->view->bearbeitbar = $this->bearbeitbar;
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
                if ($valid) {
                    $daten = $abschlussForm->getValues();
                    $monat = new Zend_Date($daten['monat'], 'MM.YYYY');
                    $this->view->saldo =
                            $this->mitarbeiter->getSaldo($monat)->getString();
                    // markiere den Monat in der Session als geprüft
                    $ns = new Zend_Session_Namespace();
                    $ns->geprueft[$monat->toString('MM-YYYY')] = true;
                    //TODO Mehr als 10 Defizitstunden
                    // lade die Form neu, um den richtigen Button anzuzeigen
                    $abschlussForm = $this->_getMitarbeiterAbschlussForm();
                }
            } elseif (isset($postDaten['abschliessen'])) {
                //lege den Monat in der DB ab
                $valid = $abschlussForm->isValid($postDaten);
                if ($valid) {
                    $daten = $abschlussForm->getValues();
                    $monat = new Zend_Date($daten['monat'], 'MM.YYYY');
                    $saldo = $this->mitarbeiter->getSaldo($monat);
                    $urlaub = $this->mitarbeiter->getUrlaubNachMonat($monat);
                    $this->view->saldo = $this->mitarbeiter->getSaldo($monat)->
                            getString();
                    $this->mitarbeiter->saveArbeitsmonat(
                            $monat, $saldo, $urlaub);
                }
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
        $this->view->extraZeilen = $tabelle['extraZeilen'];

        // die Form für den Monatsabschluss hinzufügen
        $this->view->monatForm = $abschlussForm;
    }

    public function editAction() {
        $request = $this->getRequest();

        // falls der Monat nicht bearbeitbar ist, gibt es keinen Link hierher.
        // Der User versucht etwas Böses!
        if (!$this->bearbeitbar) {
            $errors = new ArrayObject();
            $errors->type =
                    Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
            $errors->request = $request;
            $errors->exception = new AzeboLib_Exception(
                            'Auf diese Seite haben Sie keinen Zugriff!',
                            null, null);
            $request->setParam('error_handler', $errors);
            $this->_forward('nichterlaubt', 'error');
        }


        $form = $this->_getMitarbeiterTagForm();
        $form->setNachmittag();

        if ($request->isPost()) {
            $postDaten = $request->getPost();

            // bevölkere das Beginn- und Ende-Element
            $filter = new Azebo_Filter_ZeitAlsDate();
            $form->setBeginn($filter->filter($postDaten['beginn']));
            $form->setEnde($filter->filter($postDaten['ende']));
            if (!isset($postDaten['nachmittagButton']) &&
                    $postDaten['nachmittag']) {
                $form->setBeginn($filter->filter($postDaten['beginnnachmittag']), true);
                $form->setEnde($filter->filter($postDaten['endenachmittag']), true);
            }

            if (isset($postDaten['absenden'])) {
                // 'absenden' wurde gedrückt, also Daten filtern und validieren!
                $valid = $form->isValid($postDaten);
                $daten = $form->getValues();

                if ($valid) {
                    // ist valide also, speichen, in der Session als ungeprüft
                    // markieren und redirect
                    $this->mitarbeiter->saveArbeitstag(
                            $this->zuBearbeitendesDatum, $daten);
                    $ns = new Zend_Session_Namespace();
                    $ns->geprueft[
                            $this->zuBearbeitendesDatum->toString('MM-YYYY')] =
                            false;
                    $redirector = $this->_helper->getHelper('Redirector');
                    $redirector->gotoRoute(array(
                        'jahr' => $this->jahr,
                        'monat' => $this->monat,
                            ), 'monat');
                }
                // nicht valide, also tue nichts und rendere die Seite mit
                // Fehlermeldungen neu.
            } elseif (isset($postDaten['nachmittagButton'])) {
                // Nachmittag wurde gedrückt, also
                // schalte das DB-Feld um und passe die Form an
                $this->mitarbeiter->
                        getArbeitstagNachTag($this->zuBearbeitendesDatum)->
                        toggleNachmittag();
                $form->setNachmittag();
            }
            // 'zurücksetzen' wurde gedrückt, also tue nichts sondern, rendere
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
            $this->view->extraZeilenOben = $tabelle['extraZeilen'];
        } else {
            $this->view->monatsDatenOben = null;
            $this->view->hoheTageImMonatOben = 0;
            $this->view->extraZeilenOben = 0;
        }

        //befülle die untere Tabelle
        if ($this->tag != $this->tageImMonat) {
            $erster->setDay($this->tag + 1);
            $letzter->setDay($this->tageImMonat);
            $tabelle = $this->_befuelleDieTabelle($erster, $letzter);
            $this->view->monatsDatenUnten = $tabelle['tabellenDaten'];
            $this->view->hoheTageImMonatUnten = $tabelle['hoheTage'];
            $this->view->extraZeilenUnten = $tabelle['extraZeilen'];
        } else {
            $this->view->monatsDatenUnten = null;
            $this->view->hoheTageImMonatUnten = 0;
            $this->view->extraZeilenUnten = 0;
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

        // entferne die 'Prüfen'-, 'Abschließen'-, oder 'Ausdrucken'-Buttons,
        // je nachdem ob der Monat bereits geprüft bzw. abgeschlossen ist.
        $ns = new Zend_Session_Namespace();
        $geprueft = $ns->geprueft;
        $index = $this->zuBearbeitendesDatum->toString('MM-YYYY');
        //$elemente = $form->getElements();
        if (!$this->bearbeitbar) {
            $form->removeElement('pruefen');
            $form->removeElement('abschliessen');
        } elseif ($geprueft !== null && isset($geprueft[$index]) &&
                $geprueft[$index]) {
            $form->removeElement('ausdrucken');
            $form->removeElement('pruefen');
        } else {
            $form->removeElement('ausdrucken');
            $form->removeElement('abschliessen');
        }

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
        $extraZeilen = 0;

        // Iteriere über die Tage
        foreach ($this->arbeitstage as $arbeitstag) {
            if ($arbeitstag->tag->compare($erster, Zend_Date::DATE_MEDIUM)
                    != -1 &&
                    $arbeitstag->tag->compare($letzter, Zend_Date::DATE_MEDIUM)
                    != 1) {

                $tag = $arbeitstag->tag;
                $feiertag = $arbeitstag->feiertag;
                $nachmittag = $arbeitstag->nachmittag;
                $beginn = null;
                $ende = null;
                $befreiung = null;
                $anwesend = null;
                $ist = null;
                $soll = null;
                $saldo = null;

                $datum = $feiertag['name'] . ' ' . $tag->toString('EE, dd.MM.yyyy');
                if ($nachmittag) {
                    $datum .= ' Vormittag';
                    $anzahlHoheTage++;
                }

                if ($arbeitstag->beginn !== null) {
                    $beginn = $arbeitstag->beginn->toString('HH:mm');
                }
                if ($arbeitstag->ende !== null) {
                    $ende = $arbeitstag->ende->toString('HH:mm');
                }
                if ($arbeitstag->befreiung !== null) {
                    $befreiung = $befreiungOptionen[$arbeitstag->befreiung];
                }
                if ($arbeitstag->getRegel() !== null && !$nachmittag) {
                    $soll = $arbeitstag->regel->soll->toString('HH:mm');
                }

                $anwesend = $arbeitstag->getAnwesend();
                $ist = $arbeitstag->getIst();
                $saldoErg = $arbeitstag->getSaldo();
                if ($anwesend !== null && !$nachmittag) {
                    $anwesend = $anwesend->toString('HH:mm');
                    $ist = $ist->toString('HH:mm');
                    $saldo = $saldoErg->getString();
                } else {
                    $anwesend = null;
                    $ist = null;
                    $saldo = null;
                    if ($arbeitstag->befreiung == 'fa') {
                        $saldo = $saldoErg->getString();
                    }
                }

                $tabellenDaten->addItem(array(
                    'datum' => $datum,
                    'tag' => $tag->toString('dd'),
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
                if ($nachmittag) {
                    // füge die Zeile für den Nachmittag hinzu
                    $datum = $feiertag['name'] . ' ' .
                            $tag->toString('EE, dd.MM.YYYY') . ' Nachmittag';
                    $anzahlHoheTage++;
                    
                    $beginn = null;
                    $ende = null;
                    $befreiung = null;
                    $anwesend = null;
                    $ist = null;
                    $soll = null;
                    $saldo = null;

                    if ($arbeitstag->getBeginnNachmittag() !== null) {
                        $beginn = $arbeitstag->getBeginnNachmittag()->toString('HH:mm');
                    }
                    if ($arbeitstag->getEndeNachmittag() !== null) {
                        $ende = $arbeitstag->getEndeNachmittag()->toString('HH:mm');
                    }
                    if ($arbeitstag->getRegel() !== null) {
                        $soll = $arbeitstag->regel->soll->toString('HH:mm');
                    }
                    $anwesend = $arbeitstag->getAnwesend();
                    $ist = $arbeitstag->getIst();
                    $saldoErg = $arbeitstag->getSaldo();
                    if ($anwesend !== null) {
                        $anwesend = $anwesend->toString('HH:mm');
                        $ist = $ist->toString('HH:mm');
                        $saldo = $saldoErg->getString();
                    }
                    $tabellenDaten->addItem(array(
                        'datum' => $datum,
                        'tag' => $tag->toString('dd'),
                        'feiertag' => $feiertag['feiertag'],
                        'beginn' => $beginn,
                        'ende' => $ende,
                        'befreiung' => $befreiung,
                        'bemerkung' => null,
                        'pause' => $arbeitstag->pause,
                        'anwesend' => $anwesend,
                        'ist' => $ist,
                        'soll' => $soll,
                        'saldo' => $saldo,
                    ));
                    $extraZeilen++;
                }
            }
        }

        return array(
            'tabellenDaten' => $tabellenDaten,
            'hoheTage' => $anzahlHoheTage,
            'extraZeilen' => $extraZeilen,
        );
    }

}
