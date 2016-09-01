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
 *     Copyright 2012-16 Emanuel Minetti (e.minetti (at) posteo.de)
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
     * @var Zend_Session_Namespace
     */
    public $ns;

    /**
     * @var Zend_Date 
     */
    public $zuBearbeitendesDatum;

    /**
     * @var Azebo_Resource_Mitarbeiter_Item_Interface 
     */
    public $mitarbeiter;

    /**
     * Das Saldo der vor diesem Monat liegenden und abgeschlossenen Monate.
     * 
     * @var Azebo_Model_Saldo 
     */
    public $saldoBisher;

    /**
     * Das Saldo des zu bearbeitenden Monats.
     * 
     * @var Azebo_Model_Saldo 
     */
    public $saldo;

    /**
     * Die Summe aus $saldoBisher und $saldo.
     * 
     * @var Azebo_Model_Saldo 
     */
    public $saldoGesamt;

    /**
     * Der Resturlaub bis zum Vormonat.
     * 
     * @var int
     */
    public $urlaubBisher;

    /**
     * Der in diesem Monat genommene Urlaub
     * 
     * @var int
     */
    public $urlaubMonat;

    /**
     * Der Resturlaub inklusive dieses Monats.
     * 
     * @var int
     */
    public $urlaubGesamt;

    /**
     * Der Resturlaub aus dem Vorjahr bis zum Vormonat.
     * 
     * @var int
     */
    public $vorjahrRestBisher;

    /**
     * Der Resturlaub aus dem Vorjahr inklusive dieses Monats.
     * 
     * @var int
     */
    public $vorjahrRestGesamt;

    /**
     * @var boolean
     */
    public $bearbeitbar;

    /**
     * Ob die AZV-Tage angezeigt werden sollen.
     * 
     * @var boolean
     */
    public $azvAnzeigen;

    /**
     * Ob das Vorjahr noch abgeschlossen werden muss
     * 
     * @var boolean
     */
    public $jahresabschlussFehlt;

    public function init() {
        parent::init();

        $heute = new Zend_Date();

        // Hole die Parameter
        $this->tag = $this->_getParam('tag', 1);
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
        $this->ns = $ns;

        // Aktiviere Dojo
        $this->view->dojo()
                ->enable()
                ->setDjConfigOption('parseOnLoad', true)
                ->requireModule('dojox.grid.DataGrid')
                ->requireModule('dojo.data.ItemFileReadStore')
                ->requireModule('dojo._base.connect')
                ->requireModule('dijit.Tooltip');

        // Lade den Mitarbeiter
        $this->mitarbeiter = $ns->mitarbeiter;

        // Stelle den Zeitrechner-Service zur Verfügung
        $this->zeitrechner = new Azebo_Service_Zeitrechner();

        // Salden setzen
        $this->saldoBisher = $this->mitarbeiter->getSaldoBisher(
                $this->zuBearbeitendesDatum, true);
        $this->view->saldoBisher = $this->saldoBisher->getString();
        $this->saldo = $this->mitarbeiter->getSaldo(
                $this->zuBearbeitendesDatum, true);
        $this->view->saldo = $this->saldo->getString();
        $this->saldoGesamt = $this->mitarbeiter->getSaldoGesamt(
                $this->zuBearbeitendesDatum);
        $this->view->saldoGesamt = $this->saldoGesamt->getString();
        if ($this->mitarbeiter->getHochschule() == 'hfm' &&
                $this->saldoBisher->getRest()) {
            $this->view->hatRest = true;
            $this->view->saldoBisher2007 = $this->saldoBisher->getRestString();
            $this->view->saldoGesamt2007 = $this->saldoGesamt->getRestString();
        }

        // Urlaubswerte setzen
        $this->urlaubBisher = $this->mitarbeiter->getUrlaubBisher(
                $this->zuBearbeitendesDatum);
        $this->view->urlaubBisher = $this->urlaubBisher;
        $this->urlaubMonat = $this->mitarbeiter->getUrlaubNachMonat(
                $this->zuBearbeitendesDatum);
        $this->view->urlaub = $this->urlaubMonat;
        $gesamt = $this->mitarbeiter->getUrlaubGesamt($this->zuBearbeitendesDatum);
        $this->urlaubGesamt = $gesamt['rest'];
        $this->view->urlaubGesamt = $this->urlaubGesamt;
        $this->vorjahrRestBisher = $this->mitarbeiter->getUrlaubVorjahrBisher(
                $this->zuBearbeitendesDatum);
        $this->vorjahrRestGesamt = $gesamt['vorjahr'];
        if ($this->vorjahrRestBisher != 0) {
            $this->view->hatVorjahrRest = true;
            $this->view->vorjahrRestBisher = $this->vorjahrRestBisher;
            $this->view->vorjahrRestGesamt = $this->vorjahrRestGesamt;
        }

        $this->urlaubZusammenBisher = $this->urlaubBisher + $this->vorjahrRestBisher;
        $this->view->urlaubZusammenBisher = $this->urlaubZusammenBisher;
        $this->urlaubZusammenGesamt = $this->urlaubGesamt + $this->vorjahrRestGesamt;
        $this->view->urlaubZusammenGesamt = $this->urlaubZusammenGesamt;

        // prüfe ob der Monat bereits abgeschlossen ist, d.h. in der DB
        // vorhanden ist
        $this->bearbeitbar = true;
        $arbeitmonate = $this->mitarbeiter->getArbeitsmonate(false);
        foreach ($arbeitmonate as $arbeitsmonat) {
            if ($this->zuBearbeitendesDatum->compareMonth(
                            $arbeitsmonat->getMonat()) == 0 &&
                    $this->zuBearbeitendesDatum->compareYear(
                            $arbeitsmonat->getMonat()) == 0) {
                $this->bearbeitbar = false;
                break;
            }
        }
        $this->view->bearbeitbar = $this->bearbeitbar;

        //übergebe dem View die Hochschule
        $this->view->hochschule = $this->mitarbeiter->getHochschule();

        // füge für die HfS die wochenarbeitszeiten hinzu
        if ($this->mitarbeiter->getHochschule() == 'hfs') {
            $kwService = new Azebo_Service_KWnachMonat();
            $kwZeiten = $kwService->getIstKwNachMonatundMitarbeiterId(
                    $this->zuBearbeitendesDatum, $this->mitarbeiter->id);
            $this->view->kwZeiten = $kwZeiten;
        }

        // übergebe dem View die AZV-Tage, falls passend 
        if ($this->mitarbeiter->getBeamter() && $this->zuBearbeitendesDatum->compareDate('31.12.2013', 'dd.MM.YYYY') == 1) {
            $this->azvAnzeigen = true;
            $this->view->azvAnzeigen = true;
            $this->view->azvRest = $this->mitarbeiter->getAzvTage() - $this->mitarbeiter->getAzvTageBisher($this->zuBearbeitendesDatum);
            $this->view->azvMonat = $this->mitarbeiter->getAzvTageNachMonat($this->zuBearbeitendesDatum);
        } else {
            $this->view->azvAnzeigen = false;
        }

        // Prüfen, ob das Vorjahr abgeschlossen ist.
        $this->jahresabschlussFehlt = $this->mitarbeiter->jahresabschlussFehlt(
                $this->zuBearbeitendesDatum);
        $this->view->jahresabschlussFehlt = $this->jahresabschlussFehlt;
    }

    public function getSeitenName() {
        return 'Monatsübersicht';
    }

    public function druckAction() {
        $this->_erzeugePDF();
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
                    $monat = new Zend_Date($daten['monat'], 'MM.yyyy');
                    $this->view->saldo = $this->mitarbeiter->getSaldo($monat)->getString();
                    // markiere den Monat in der Session als geprüft
                    $ns = new Zend_Session_Namespace();
                    $ns->geprueft[$monat->toString('MM-yyyy')] = true;
                    //TODO Mehr als 10 Defizitstunden
                    // lade die Form neu, um den richtigen Button anzuzeigen
                    $abschlussForm = $this->_getMitarbeiterAbschlussForm();
                }
            } elseif (isset($postDaten['abschliessen'])) {
                // lege den Monat in der DB ab, falls er noch nicht vorhanden
                // ist (= möglich bei 'Resend')
                $valid = $abschlussForm->isValid($postDaten);
                if (
                        $valid &&
                        $this->mitarbeiter->
                        getArbeitsmonat($this->zuBearbeitendesDatum) === null) {
                    $daten = $abschlussForm->getValues();
                    $monat = new Zend_Date($daten['monat'], 'MM.yyyy');
                    $saldo = $this->mitarbeiter->getSaldo($monat);
                    $this->view->saldo = $saldo->getString();
                    $this->mitarbeiter->saveArbeitsmonat($monat);
                    $this->bearbeitbar = false;
                    
                    //falls der zu bearbeitende Monat der Dezember ist,
                    //schließe auch das Jahr ab!
                    $dezember = new Zend_Date('01.12.2000');
                    if ($this->zuBearbeitendesDatum->compareMonth($dezember)
                            == 0) {
                        $this->jahresabschlussFehlt = true;
                        $this->_schliesseJahrAb();
                    }

                    // aktualisiere den View
                    $this->view->bearbeitbar = false;
                    $abschlussForm = $this->_getMitarbeiterAbschlussForm();
                }
            }

            if (isset($postDaten['uebertragen'])) {
                $valid = $abschlussForm->isValid($postDaten);
                if ($valid) {
                    //$this->_log->debug('Habe abgeschlossen!!');
                    $this->_schliesseJahrAb();
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
        $tabelle = $this->_helper->
                MonatsTabelle($erster, $letzter, $this->mitarbeiter);

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
            $errors->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
            $errors->request = $request;
            $errors->exception = new AzeboLib_Exception(
                    'Auf diese Seite haben Sie keinen Zugriff!', null, null);
            $request->setParam('error_handler', $errors);
            $this->_forward('nichterlaubt', 'error');
        }


        $form = $this->_getMitarbeiterTagForm();
        $form->setNachmittag();

        if ($request->isPost()) {
            $postDaten = $request->getPost();

            if (!isset($postDaten['zuruecksetzen'])) {
                // bevölkere das Beginn- und Ende-Element
                $filter = new Azebo_Filter_ZeitAlsDate();
                $form->setBeginn($filter->filter($postDaten['beginn']));
                $form->setEnde($filter->filter($postDaten['ende']));
                if (!isset($postDaten['nachmittagButton']) &&
                        $postDaten['nachmittag']) {
                    $form->setBeginn($filter->
                                    filter($postDaten['beginnnachmittag']), true);
                    $form->setEnde($filter->
                                    filter($postDaten['endenachmittag']), true);
                }

                if (isset($postDaten['absenden']) ||
                        isset($postDaten['absendenWeiter'])) {
                    // 'absenden' oder 'absendenWeiter' wurde gedrückt,
                    // also Daten filtern und validieren!
                    $valid = $form->isValid($postDaten);
                    $daten = $form->getValues();

                    if ($valid) {
                        // für HfM die Pause setzen
                        if ($this->mitarbeiter->getHochschule() == 'hfm') {
                            if ($daten['beginn'] !== null &&
                                    $daten['beginn'] != '' &&
                                    $daten['ende'] !== null &&
                                    $daten['ende'] != '') {
                                $anwesend = $this->zeitrechner->
                                        anwesend($daten['beginn'], $daten['ende']);
                                $anwesendZusammen = new Zend_Date($anwesend);
                                $anwesendNachmittag = NULL;
                                $zwischenzeit = NULL;
                                $pause = $this->ns->zeiten->pause;
                                
                                if ($daten['beginnnachmittag'] !== null &&
                                        $daten['beginnnachmittag'] != '' &&
                                        $daten['endenachmittag'] !== null &&
                                        $daten['endenachmittag'] != '') {
                                    $anwesendNachmittag = $this->zeitrechner->
                                            anwesend(
                                            $daten['beginnnachmittag'], $daten['endenachmittag']);
                                    $anwesendZusammen = new Zend_Date($anwesend);
                                    $anwesendZusammen->addTime($anwesendNachmittag);
                                    $zwischenzeit = $this->zeitrechner->anwesend($daten['ende'], $daten['beginnnachmittag']);
                                }
                                
                                // Falls 'anwesend' und 'anwesendNachmittag' kürzer als 
                                // pauseKurzAb sind *und* 'anwesend' und 'anwesendNachmittag' zusammen
                                // mehr als pauseKurzAb sind *und* die Zeit zwischen
                                // 'ende' und 'beginnNachmittag' größer als pauseKurzDauer
                                // bzw., falls angebracht, größer als pauseLangDauer,
                                // ist, soll keine Pause abgezogen werden. 
                                if ($anwesend->compareTime($pause->kurz->ab) === 1) {
                                    $daten['pause'] = '-';    //=> Pause wird abgezogen
                                } elseif ($anwesendNachmittag !== NULL &&
                                        $anwesendNachmittag->compareTime($pause->kurz->ab) === 1) {
                                    $daten['pause'] = '-';
                                } elseif ($anwesendZusammen->compareTime($pause->kurz->ab) === 1) {
                                    if ($anwesendZusammen->compareTime($pause->lang->ab) === 1) {
                                        if ($zwischenzeit !== NULL &&
                                                $zwischenzeit->compareTime($pause->lang->dauer) !== -1) {
                                            $daten['pause'] = 'x';  //=> Pause wird nicht abgezogen
                                        } else {
                                            $daten['pause'] = '-';
                                        }
                                    } else {
                                        if ($zwischenzeit !== NULL &&
                                                $zwischenzeit->compareTime($pause->kurz->dauer) !== -1) {
                                            $daten['pause'] = 'x';
                                        } else {
                                            $daten['pause'] = '-';
                                        }
                                    }
                                } else {
                                    $daten['pause'] = 'x';
                                }   
                            }
                        }

                        // speichern und in der Session als ungeprüft
                        // markieren
                        $this->mitarbeiter->saveArbeitstag(
                                $this->zuBearbeitendesDatum, $daten);
                        $ns = new Zend_Session_Namespace();
                        $ns->geprueft[
                                $this->zuBearbeitendesDatum->toString('MM-yyyy')
                                ] = false;
                        
                        // Redirect: je nachdem, ob 'absenden' oder
                        // 'absendenWeiter' gedrückt wurde, auf die
                        // entsprechende Seite weiterleiten.
                        $redirector = $this->_helper->getHelper('Redirector');
                        if(isset($postDaten['absenden'])) {
                        $redirector->gotoRoute(array(
                            'jahr' => $this->jahr,
                            'monat' => $this->monat,
                                ), 'monat');
                        } else {
                            // 'absendenWeiter' wurde gedrückt: Ermittle den
                            // nächsten Arbeitstag
                            $naechsterTag = $this->zuBearbeitendesDatum->add(
                                    '1', Zend_Date::DAY);
                            $feiertagsservice = $this->ns->feiertagsservice;
                            while ($feiertagsservice->
                                    feiertag($naechsterTag)['feiertag']) {
                                $naechsterTag = $this->zuBearbeitendesDatum->
                                        add('1', Zend_Date::DAY);
                            }
                            // und leite weiter
                            $redirector->gotoRoute(array(
                                'jahr' => $this->jahr,
                                'monat' => $this->monat,
                                'tag' => $naechsterTag->get(
                                        Zend_Date::DAY_SHORT),
                            ), 'monatEdit');
                        }
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
            }
            // 'zurücksetzen' wurde gedrückt, also tue nichts und rendere
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
            $tabelle = $this->_helper->
                    MonatsTabelle($erster, $letzter, $this->mitarbeiter);
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
            $tabelle = $this->_helper->
                    MonatsTabelle($erster, $letzter, $this->mitarbeiter);
            $this->view->monatsDatenUnten = $tabelle['tabellenDaten'];
            $this->view->hoheTageImMonatUnten = $tabelle['hoheTage'];
            $this->view->extraZeilenUnten = $tabelle['extraZeilen'];
        } else {
            $this->view->monatsDatenUnten = null;
            $this->view->hoheTageImMonatUnten = 0;
            $this->view->extraZeilenUnten = 0;
        }
    }

    public function blockAction() {
        $request = $this->getRequest();

        // falls der Monat nicht bearbeitbar ist, gibt es keinen Link hierher.
        // Der User versucht etwas Böses!
        if (!$this->bearbeitbar) {
            $errors = new ArrayObject();
            $errors->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
            $errors->request = $request;
            $errors->exception = new AzeboLib_Exception(
                    'Auf diese Seite haben Sie keinen Zugriff!', null, null);
            $request->setParam('error_handler', $errors);
            $this->_forward('nichterlaubt', 'error');
        }

        $model = new Azebo_Model_Mitarbeiter();
        $form = $model->getForm('mitarbeiterBlock');
        $monatElement = $form->getElement('monat');
        $monatElement->setValue($this->zuBearbeitendesDatum->toString('yyyy-MM-dd'));

        if ($request->isPost()) {
            $postDaten = $request->getPost();
            if (isset($postDaten['absenden'])) {
                $valid = $form->isValid($postDaten);
                $daten = $form->getValues();
                if ($valid) {
                    $von = $daten['von'];
                    $bis = $daten['bis'];
                    $filter = new Azebo_Filter_DatumAlsDate();
                    $tagIndex = $filter->filter($von);
                    $tagBis = $filter->filter($bis);
                    // iteriere über die Tage
                    while ($tagIndex->compareDate($tagBis) != 1) {
                        $arbeitstag = $this->mitarbeiter->getArbeitstagNachTag($tagIndex);
                        // Arbeitsfreie Tage werden nicht bearbeitet
                        $arbeitsfrei = !$arbeitstag->getRegel();
                        if (!$arbeitsfrei) {
                            $arbeitstag->setBeginn(null);
                            $arbeitstag->setEnde(null);
                            $arbeitstag->befreiung = $daten['befreiung'];
                            $arbeitstag->save();
                        }
                        $tagIndex->addDay(1);
                    }
                    // redirect
                    return $this->_helper->redirector->gotoRoute(array(
                                'monat' => $this->monat,
                                'jahr' => $this->jahr,
                                    ), 'monat');
                }
            }
        }

        $this->erweitereSeitenName(' - ' . $this->zuBearbeitendesDatum
                        ->toString('MMMM yyyy'));
        $this->erweitereSeitenName(' Block bearbeiten');

        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
            'monat' => $this->monat,
            'jahr' => $this->jahr,
                ), 'monatBlock', true);
        $form->setAction($url);
        $form->setMethod('post');
        $form->setName('blockForm');
        $this->view->form = $form;
    }
    
    public function csvAction() {
        $request = $this->getRequest();

        // falls der Monat nicht bearbeitbar ist, gibt es keinen Link hierher.
        // Der User versucht etwas Böses!
        if (!$this->bearbeitbar) {
            $errors = new ArrayObject();
            $errors->type = Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
            $errors->request = $request;
            $errors->exception = new AzeboLib_Exception(
                    'Auf diese Seite haben Sie keinen Zugriff!', null, null);
            $request->setParam('error_handler', $errors);
            $this->_forward('nichterlaubt', 'error');
        }

        $model = new Azebo_Model_Mitarbeiter();
        $form = $model->getForm('mitarbeiterCSV');
        $monatElement = $form->getElement('monat');
        $monatElement->setValue($this->zuBearbeitendesDatum->toString('yyyy-MM-dd'));

        if ($request->isPost()) {
            $postDaten = $request->getPost();
            if (isset($postDaten['absenden'])) {
                $valid = $form->isValid($postDaten);
                $form->file->receive();
                $postDaten = $form->getValues();
                if ($valid) {
                    $monat = new Zend_Date($postDaten['monat'], 'yyyy-MM-dd');
                    $dateiName = $form->file->getFileName();
                    $termine = $this->_parseTrackWorkTime($monat, $dateiName);
                    
                    // Die ermittelten Termine in den Bogen übernehmen
                    foreach ($termine as $termin) {
                        $tag = $termin['beginn'];
                        $termin['pause'] = '-';
                        $termin['befreiung'] = null;
                        $this->mitarbeiter->saveArbeitstag($tag, $termin);
                    }
                    
                    // Die hochgeladene Datei löschen
                    unlink($dateiName);

                    // redirect
                    return $this->_helper->redirector->gotoRoute(array(
                                'monat' => $this->monat,
                                'jahr' => $this->jahr,
                                    ), 'monat');
                }
            }
        }

        $this->erweitereSeitenName(' - ' . $this->zuBearbeitendesDatum
                        ->toString('MMMM yyyy'));
        $this->erweitereSeitenName(' CSV-Datei hochladen');

        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
            'monat' => $this->monat,
            'jahr' => $this->jahr,
                ), 'monatCSV', true);
        $form->setAction($url);
        $form->setMethod('post');
        $form->setName('csvForm');
        $this->view->form = $form;
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

        // entferne die 'Prüfen'-, 'Abschließen'-, 'Vorjahr abschließen' oder
        // 'Ausdrucken'-Buttons, je nachdem ob der Monat bereits geprüft bzw.
        // abgeschlossen ist.
        $ns = new Zend_Session_Namespace();
        $geprueft = $ns->geprueft;
        $index = $this->zuBearbeitendesDatum->toString('MM-yyyy');
        if (!$this->bearbeitbar) {
            $form->removeElement('pruefen');
            $form->removeElement('abschliessen');
            $form->removeElement('uebertragen');

            $druckElement = $form->getElement('ausdrucken');
            $druckElement->setAttrib('onclick', 'drucke();');
        } elseif ($geprueft !== null && isset($geprueft[$index]) &&
                $geprueft[$index]) {
            $form->removeElement('ausdrucken');
            $form->removeElement('pruefen');
            $form->removeElement('uebertragen');
        } else {
            // zeige den Prüfen-Knopf, außer das letzte Jahr ist noch nicht
            // abgeschlossen
            if (!$this->jahresabschlussFehlt) {
                $form->removeElement('ausdrucken');
                $form->removeElement('abschliessen');
                $form->removeElement('uebertragen');
            } else {
                $form->removeElement('ausdrucken');
                $form->removeElement('abschliessen');
                $form->removeElement('pruefen');
            }
        }

        $monatElement = $form->getElement('monat');
        $monatElement->setValue($this->zuBearbeitendesDatum->toString('MM.yyyy'));
        return $form;
    }

    private function _erzeugePDF() {
        // Einrichten des Bogens
        $pdf = new Azebo_Service_BogenPDF();
        $pdf->SetTitle('Arbeitszeitbogen');
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // Kopf des Bogens
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(95, 15, 'Arbeitszeiterfassung', 0, 0, 'L');
        $pdf->Cell(95, 15, $this->mitarbeiter->getHochschulString(), 0, 0, 'R');
        $pdf->Ln(10);
        $pdf->Cell(95, 15, $this->zuBearbeitendesDatum->toString('MMMM yyyy'), 0, 0, 'L');
        $pdf->Cell(95, 15, $this->mitarbeiter->getName(), 0, 0, 'R');
        $pdf->Ln(20);

        // Kopf der Tabelle
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetFillColor(220);
        $pdf->SetWidths(array(9, 19, 14, 14, 25, 52, 14, 14, 14, 15));
        $pdf->SetAligns(array('C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C', 'C'));
        $pdf->Row(array(
            'Tag',
            'Datum',
            'Beginn',
            'Ende',
            'Befreiung',
            'Bemerkung',
            'Anwe-send',
            'Ist',
            'Soll',
            'Saldo',
                ), true);

        // Hohlen der Tabellendaten
        $erster = new Zend_Date($this->zuBearbeitendesDatum);
        $letzter = new Zend_Date($this->zuBearbeitendesDatum);
        $erster->setDay(1);
        $letzter->setDay($this->tageImMonat);
        $tabelle = $this->_helper->
                MonatsTabelle($erster, $letzter, $this->mitarbeiter);

        // Tabellenkörper
        $pdf->SetFont('Times', '', 8);
        $pdf->SetAligns(array('C', 'C', 'C', 'C', 'L', 'L', 'C', 'C', 'C', 'C'));
        foreach ($tabelle['tabellenDaten'] as $row) {
            $fill = $row['feiertag'] == null ? false : true;
            $pdf->Row(array(
                $row['pdfTag'],
                $row['pdfDatum'],
                $row['beginn'],
                $row['ende'],
                $row['befreiung'],
                $row['bemerkung'],
                $row['anwesend'],
                $row['ist'],
                $row['soll'],
                $row['saldo']), $fill);
        }
        $pdf->Ln(6);

        // Hohlen der Daten für den Fuß des Bogens
        $saldoString = $this->saldo->getString();
        if ($this->mitarbeiter->getHochschule() == 'hfm' &&
                $this->saldoBisher->getRest()) {
            $saldoBisherString = $this->saldoBisher->getString() .
                    '     (Rest 2007: ' . $this->saldoBisher->getRestString() .
                    ')';
            $saldoGesamtString = $this->saldoGesamt->getString() .
                    '     (Rest 2007: ' . $this->saldoGesamt->getRestString() .
                    ')';
        } else {
            $saldoBisherString = $this->saldoBisher->getString();
            $saldoGesamtString = $this->saldoGesamt->getString();
        }

        $urlaubBisherString = $this->urlaubBisher;
        $urlaubMonatString = $this->urlaubMonat;
        $urlaubGesamtString = $this->urlaubGesamt;
        if ($this->vorjahrRestBisher != 0) {
            if ($this->mitarbeiter->getHochschule() == 'khb') {
                $urlaubBisherString .= '     (+ Rest Vorjahr: ' .
                        $this->vorjahrRestBisher . ')';
                $urlaubGesamtString .= '     (+ Rest Vorjahr: ' .
                        $this->vorjahrRestGesamt . ')';
            } elseif ($this->mitarbeiter->getHochschule() == 'hfm') {
                $urlaubBisherString .= ' + Vorjahr: ' .
                        $this->vorjahrRestBisher . ' = ';
                $urlaubBisherString .=
                        $this->urlaubBisher + $this->vorjahrRestBisher;
                $urlaubGesamtString .= ' + Vorjahr: ' .
                        $this->vorjahrRestGesamt . ' = ';
                $urlaubGesamtString .=
                        $this->urlaubGesamt + $this->vorjahrRestGesamt;
            } else {
                $urlaubBisherString .=
                        '     (Rest Vorjahr: ' . $this->vorjahrRestBisher . ')';
                $urlaubGesamtString .=
                        '     (Rest Vorjahr: ' . $this->vorjahrRestGesamt . ')';
            }
        }

        $azvTageRest = $this->mitarbeiter->getAzvTage() -
                $this->mitarbeiter->getAzvTageBisher(
                        $this->zuBearbeitendesDatum);
        $azvTageMonat = $this->mitarbeiter->getAzvTageNachMonat(
                $this->zuBearbeitendesDatum);

        // Setzen von Textbausteinen
        if ($this->mitarbeiter->getHochschule() == 'khb') {
            $vormonatSaldoText = 'Saldo Vormonat: ';
            $monatSaldoText = 'Saldo dieses Monat: ';
            $gesamtSaldoText = 'Übertrag Folgemonat: ';
            $urlaubBisherText = 'Resturlaub Vormonat: ';
            $urlaubMonatText = 'Urlaub dieses Monats: ';
            $urlaubGesamtText = 'Übertrag Folgemonat: ';
            $azvTageRestText = 'AZV-Tage-Rest: ';
            $azvTageMonatText = 'AZV-Tage dieses Monats: ';
        } else {
            $vormonatSaldoText = 'Saldo Vormonat: ';
            $monatSaldoText = 'Saldo dieses Monats: ';
            $gesamtSaldoText = 'Saldo Gesamt: ';
            $urlaubBisherText = 'Resturlaub Vormonat: ';
            $urlaubMonatText = 'Urlaub dieses Monats: ';
            $urlaubGesamtText = 'Resturlaub Gesamt: ';
            $azvTageRestText = 'AZV-Tage-Rest: ';
            $azvTageMonatText = 'AZV-Tage dieses Monats: ';
        }


        // Fuß des Bogens
        $pdf->SetFont('Times', '', 10);
        $pdf->SetWidths(array(48, 47, 48, 47));
        $pdf->SetAligns(array('L', 'C', 'R', 'C'));
        $pdf->Row(array($vormonatSaldoText, $saldoBisherString,
            $urlaubBisherText, $urlaubBisherString), false);
        $pdf->Row(array($monatSaldoText, $saldoString, $urlaubMonatText,
            $urlaubMonatString), false);
        $pdf->Row(array($gesamtSaldoText, $saldoGesamtString, $urlaubGesamtText,
            $urlaubGesamtString), false);
        // AZV-Tage
        if ($this->azvAnzeigen) {
            $pdf->Cell(95);
            $pdf->Cell(48, 5, $azvTageRestText, 1, 0, 'R');
            $pdf->Cell(47, 5, $azvTageRest, 1, 1, 'C');
            $pdf->Cell(95);
            $pdf->Cell(48, 5, $azvTageMonatText, 1, 0, 'R');
            $pdf->Cell(47, 5, $azvTageMonat, 1, 1, 'C');
        }
        $pdf->Ln(10);

        $pdf->SetWidths(array(60, 70, 60));
        $pdf->SetAligns(array('L', 'C', 'L'));
        $pdf->Row(array("_____________________________\n      Unterschrift Beschäftigte/r",
            '', "_____________________________\n     Unterschrift Fachvorgesetzte/r"), false, false);

        // Rendern und senden des Bogens
        $pdf->AutoPrint();
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="bogen.pdf"');
        $pdf->Output();
    }

    private function _schliesseJahrAb() {
        // Falls das Jahr schon abgeschlossen ist, darf außer dem Redirect
        // nichts passieren
        
        if ($this->jahresabschlussFehlt) {
            // Ermittle den Dezember des abzuschließenden Jahres
            $uebertragenBis = $this->mitarbeiter->getUebertragenbis();
            $jahr = $uebertragenBis->get(Zend_Date::YEAR);
            $jahr++;
            $dezember = new Zend_Date("1.12.$jahr");
            
            // Die zu überschreibenden Daten 'retten', also in der Tabelle
            // 'vorjahr' abspeichern
            $vorjahr = $this->mitarbeiter->getVorjahr();
            $uebertrag = $this->mitarbeiter->getSaldouebertrag();
            $vorjahr->setSaldouebertrag($uebertrag);
            if ($this->mitarbeiter->getHochschule() == 'hfm') {
                if ($uebertrag->getRest()) {
                    $saldo2007 = new Azebo_Model_Saldo($uebertrag->getRestStunden(), $uebertrag->getRestMinuten(), true);
                    $vorjahr->setSaldo2007($saldo2007);
                } else {
                    $this->mitarbeiter->setSaldo2007(null);
                }
            }
            $vorjahr->setUrlaub($this->mitarbeiter->getUrlaub());
            $vorjahr->setUrlaubVorjahr($this->mitarbeiter->getUrlaubVorjahr());
            $vorjahr->save();

            // Saldo neu setzen
            $saldo = $this->mitarbeiter->getSaldoGesamt($dezember);
            $this->mitarbeiter->setSaldoUebertrag($saldo);

            // Saldo2007 setzen
            if ($this->mitarbeiter->getHochschule() == 'hfm') {
                if ($saldo->getRest()) {
                    $saldo2007 = new Azebo_Model_Saldo($saldo->getRestStunden(), $saldo->getRestMinuten(), true);
                    $this->mitarbeiter->setSaldo2007($saldo2007);
                } else {
                    $this->mitarbeiter->setSaldo2007(null);
                }
            }

            // Resturlaub setzen
            $urlaubGesamt = $this->mitarbeiter->getUrlaubGesamt($dezember);
            $this->mitarbeiter->setUrlaubVorjahr($urlaubGesamt['rest']);

            // ÜbertragenBis aktualisieren
            $this->mitarbeiter->setUebertragenbis($dezember);

            // DB aktualisieren
            $this->mitarbeiter->save();

            // Arbeitsmonate als übertragen markieren
            $arbeitsmonate = $this->mitarbeiter->getArbeitsmonateNachJahr(
                    $dezember);
            foreach ($arbeitsmonate as $arbeitsmonat) {
                $arbeitsmonat->setUebertragen();
                $arbeitsmonat->save();
            }

            // Alte Monate und Tage löschen
            $dezemberVorjahr = new Zend_Date($dezember);
            $dezemberVorjahr->subYear(1);
            $arbeitsmonatTabelle = new Azebo_Resource_Arbeitsmonat();
            $arbeitsmonatTabelle->deleteArbeitsmonateBis($dezemberVorjahr, $this->mitarbeiter->id);
            $arbeitstagTabelle = new Azebo_Resource_Arbeitstag();
            $arbeitstagTabelle->deleteArbeitstageBis($dezemberVorjahr, $this->mitarbeiter->id);
        }

        // Mache einen 'redirect' um den Controller neu zu laden
        // und damit die richtigen Salden und Urlaubswerte
        // anzuzeigen.
        $redirector = $this->_helper->getHelper('Redirector');
        $redirector->gotoSimple('index', 'monat', null, array(
            'jahr' => $this->jahr,
            'monat' => $this->monat,
        ));
    }

    /**
     * Liest Arbeitsanfang und -ende sowie evevntuelle Bemerkungen aus einer
     * mit der APP 'TrackWorkTime' erstellten CSV-Datei in ein Array ein.
     * 
     * Es wird pro Tag immer nur die erste Anmeldung und die letzte Abmeldung
     * als Anfang bzw. Ende der Arbeitszeit gewertet. Die Bemerkungen der
     * einzelnen Einträge eines Tages werden gesammelt und aneinander angehägt,
     * um schließlich eine Bemerkung für den Arbeitstag zu generieren.
     *     Es werden immer nur die Tage des Monats berücksichtigt, die in dem
     * angegeben Monat liegen.
     *     Das Parsen der CSV-Datei wurde in eigene Funktion ausgelagert, um die
     * zukünftige Erweiterbarkeit der Lösung (andere Apps) zu gewährleisten.
     * 
     * @param Zend_Date $monat der Monat, der geparst werden soll.
     * @param String $dateiName der Name der Datei, die geparst werden soll.
     * @return array ein array von arrays das die einzelnen Arbeitstage als
     * Einträge enthält. Die einzelnen Einträge enthalten die Schlüssel
     * 'Beginn', 'Ende' und 'Bemerkung'. Hierbei sind Beginn und Ende jeweils
     * vom Typ Zend_Date und Bemerkung ist vom Typ String.
     */
    private function _parseTrackWorkTime($monat, $dateiName) {
        // Ein leeres array für die gesammelten Termine bereitstellen
        $termine = array();
        // Die Zeilen der hochgeladenen Datei in ein array einlesen
        $zeilen = file($dateiName, FILE_IGNORE_NEW_LINES || FILE_SKIP_EMPTY_LINES);

        // Die Zeile mit den Spaltentiteln loswerden.
        array_shift($zeilen);

        // Die Zeilen, die nicht zum Monat gehören loswerden.
        $indices = array();
        foreach ($zeilen as $index => $zeile) {
            $datum = strtok($zeile, ';');
            $datum = new Zend_Date($datum, 'YYYY-MM-dd HH:mm');
            if (!($monat->equals($datum, Zend_Date::MONTH) &&
                    $monat->equals($datum, Zend_Date::YEAR))) {
                unset($zeilen[$index]);
            }
        }

        //'$zeilen' kopieren und nur die nicht-leeren Einträge
        //übernehmen!
        $zeilenAlt = $zeilen;
        $zeilen = array();
        foreach ($zeilenAlt as $zeile) {
            if (isset($zeile)) {
                array_push($zeilen, $zeile);
            }
        }
        
        // Die zu bearbeitenden Einträge sammeln: Sprich nur den ersten
        // und den letzten Eintrag jeden Tages übernehmen.
        
        // Die Schleife, die über alle Zeile läuft
        while (true) {
            if (!$zeilen || !$zeilen[0] || chop($zeilen[0]) === '') {
                break;
            }
            // Ein neuer Arbeitstag wird bearbeitet, sprich Datum und Bemerkung
            // werden (vorläufig) gespeichert.
            $tokens = explode(';',$zeilen[0]);
            $datum = $tokens[0];
            $datum = new Zend_Date($datum, 'YYYY-MM-dd HH:mm');
            $bemerkung = chop($tokens[count($tokens) - 1]);
            // Die Zeile ist fertig bearbeitet und kann entfernt werden
            array_shift($zeilen);
            // Falls keine weitere (nicht-leere) Zeile existiert,
            // springe aus der Schleife über die Zeilen raus und vergiss
            // dsa gerade bearbeitete Datum
            if (!$zeilen || !$zeilen[0] || chop($zeilen[0]) === '') {
                break;
            }
            // Bearbeite die nächste Zeile
            $tokens = explode(';',$zeilen[0]);
            $letztesDatum = $tokens[0];
            $letztesDatum = new Zend_Date($letztesDatum, 'YYYY-MM-dd HH:mm');
            $letzteBemerkung = $tokens[count($tokens) - 1];
            // Falls die nächste Zeile ein anderes Datum enthält, war die 
            // bearbeitete Zeile ungültig (kein Ende). Also wirf die Zeile weg
            // und bearbeite die nächste Zeile
            if (!($letztesDatum->equals($datum, Zend_Date::DATES))) {
                continue;
            } else {
                // Die nächste Zeile gehört zum bearbeiteten Datum, also
                // füge die Bemerkung zum berbeiteten Datum hoinzu.
                $bemerkung .= ' ' . chop($letzteBemerkung);
                // Die Zeile ist fertig bearbeitet, also entferne sie
                array_shift($zeilen);
                // Prüfe ob die beabeitete Zeile, die letzte für das bearbeitete
                // Datum ist
                $letztenEintragGefunden = false;
                while (!$letztenEintragGefunden) {
                    if (!$zeilen || !$zeilen[0] || chop($zeilen[0]) === '') {
                        break;
                    }
                    $tokens = explode(';',$zeilen[0]);
                    $naechstesDatum = $tokens[0];
                    $naechstesDatum = new Zend_Date($naechstesDatum, 'YYYY-MM-dd HH:mm');
                    $naechsteBemerkung = $tokens[count($tokens) - 1];
                    // Falls die nächste Zeile zum bearbeiteten Datum gehört,
                    // vergiss die vorige Zeile und füge die Bemerkung hinzu
                    if (($letztesDatum->equals($naechstesDatum, Zend_Date::DATES))) {
                        $letztesDatum = $naechstesDatum;
                        $bemerkung .=  ' ' . chop($naechsteBemerkung);
                        // Die Zeile ist bearbeitet und wird entfernt
                        array_shift($zeilen);
                        // Falls keine weiteren (nicht-leeren) Zeilen mehr folgen,
                        // sind wir fertig
                        if (!$zeilen || !$zeilen[0] || chop($zeilen[0]) === '') {
                            break;
                        }
                    } else {
                        // Der Tag ist fertig bearbeitet,
                        // also gehe zum nächsten Tag
                        $letztenEintragGefunden = true;
                    }
                }
            }
            // Hier werden die in den Bogen zu übernehmenden Tage gesammelt.
            array_push($termine, array(
                'beginn' => $datum,
                'ende' => $letztesDatum,
                'bemerkung' => $bemerkung,
            ));
        }
        return $termine;
    }

}

