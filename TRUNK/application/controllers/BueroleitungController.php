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

class BueroleitungController extends AzeboLib_Controller_Abstract {

    /**
     * @var Azebo_Resource_Mitarbeiter_Item_interface 
     */
    public $mitarbeiter;

    /**
     * @var Azebo_Model_Mitarbeiter 
     */
    public $model;

    /**
     * @var Zend_Session_Namespace
     */
    public $ns;

    public function init() {
        parent::init();

        // Lade den Mitarbeiter
        $ns = new Zend_Session_Namespace();
        $this->ns = $ns;
        $this->mitarbeiter = $ns->mitarbeiter;

        // lade das Modell
        $this->model = new Azebo_Model_Mitarbeiter();

        // Aktiviere Dojo
        $this->view->dojo()->enable()
                ->setDjConfigOption('parseOnLoad', true)
                ->requireModule('dojox.grid.DataGrid')
                ->requireModule('dojo.data.ItemFileReadStore')
                ->requireModule('dojo._base.connect');
    }

    public function getSeitenName() {
        return 'Büroleitung';
    }

    public function indexAction() {
        $redirector = $this->_helper->getHelper('Redirector');
        $redirector->gotoSimple('mitarbeiter', 'bueroleitung');
    }

    public function mitarbeiterAction() {
        $this->erweitereSeitenName(' Übersicht Mitarbeiter');

        // intialisiere die Tabelle
        $mitarbeiterDaten = new Zend_Dojo_Data();
        $mitarbeiterDaten->setIdentifier('mitarbeiter');
        $zeilen = 0;

        // hole die Mitarbeiter der Hochschule
        $hsMitarbeiter = $this->model->getMitarbeiterNachHochschule(
                $this->mitarbeiter->getHochschule());

        // füge die Mitarbeiter der Tabelle hinzu
        foreach ($hsMitarbeiter as $mitarbeiter) {
            $mitarbeiterDaten->addItem(array(
                'mitarbeiter' => $mitarbeiter->benutzername,
                'mitarbeitername' => $mitarbeiter->getName(),
                'abgeschlossen' => $mitarbeiter->getAbgeschlossenBis(),
                'abgelegt' => $mitarbeiter->getAbgelegtBis(),
            ));
            $zeilen++;
        }

        $this->view->mitarbeiterDaten = $mitarbeiterDaten;
        $this->view->zeilen = $zeilen;
    }

    public function detailAction() {

        // hole und setzte den Namen des Mitarbeiters
        $benutzername = $this->_getParam('benutzername');
        $zuBearbeitenderMitarbeiter = $this->model->
                getMitarbeiterNachBenutzername($benutzername);
        if ($zuBearbeitenderMitarbeiter !== null) {
            $name = $zuBearbeitenderMitarbeiter->getName();
            $neu = false;
        } else {
            $mitarbeiterTabelle = new Azebo_Resource_Mitarbeiter();
            $zuBearbeitenderMitarbeiter = $mitarbeiterTabelle->createRow();
            $name = $this->model->getNameNachBenutzername($benutzername);
            $neu = true;
        }
        $this->erweitereSeitenName(' Bearbeite ' . $name);
        $formDetail = $this->_getMitarbeiterDetailForm($benutzername, $neu);
        $this->view->form = $formDetail;
        $this->view->neu = $neu;

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postDaten = $request->getPost();
            if (isset($postDaten['absenden'])) {
                $valid = $formDetail->isValid($postDaten);
                if ($valid) {
                    $daten = $formDetail->getValues();
                    $this->model->saveMitarbeiter(
                            $zuBearbeitenderMitarbeiter, $daten);
                    $neu = false;
                    $this->view->neu = false;
                }
            }
        }

        // zeige die Arbeitszeitentabelle, falls der Mitarbeiter
        // nicht neu angelegt wurde
        if (!$neu) {
            $arbeitsregeln = $zuBearbeitenderMitarbeiter->getArbeitsregeln();
            $this->view->zeitDaten =
                    $this->_befuelleDieZeitenTabelle($arbeitsregeln);
            $this->view->zeilen = count($arbeitsregeln);
            $this->view->mitarbeiter = $benutzername;
        }
    }

    public function neuAction() {
        $this->erweitereSeitenName(' Neuer Mitarbeiter');
        $hochschule = $this->mitarbeiter->getHochschule();
        $mitglieder = $this->model->getBenutzernamenNachHochschule($hochschule);
        $form = $this->_getNeuerMitarbeiterForm($mitglieder);
        $request = $this->getRequest();
        if ($request->isPost()) {
            $postDaten = $request->getPost();
            $valid = $form->isValid($postDaten);
            if ($valid) {
                $daten = $form->getValues();
                $redirector = $this->getHelper('Redirector');
                $redirector->gotoRoute(array(
                    'benutzername' => $daten['auswahl'],
                        ), 'mitarbeiterdetail');
            }
        }
        $this->view->form = $form;
    }

    public function arbeitsregelAction() {
        $benutzername = $this->_getParam('mitarbeiter');
        $zuBearbeitenderMitarbeiter = $this->model->
                getMitarbeiterNachBenutzername($benutzername);
        $id = $this->_getParam('id');
        $form = $this->_getArbeitsregelForm($benutzername, $id);
        
        $this->erweitereSeitenName(' Bearbeite Arbeitszeit ' . $zuBearbeitenderMitarbeiter->getName());

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postDaten = $request->getPost();
            if (isset($postDaten['absenden'])) {
                $valid = $form->isValid($postDaten);
                $daten = $form->getValues();
                $datumFilter = new Azebo_Filter_DatumAlsDate();
                $zeitFilter = new Azebo_Filter_ZeitAlsDate();
                if ($valid) {
                    $daten['von'] = $datumFilter->filter($daten['von']);
                    $daten['bis'] = $datumFilter->filter($daten['bis']);
                    $daten['rahmenAnfang'] =
                            $zeitFilter->filter($daten['rahmenAnfang']);
                    $daten['kernAnfang'] =
                            $zeitFilter->filter($daten['kernAnfang']);
                    $daten['kernEnde'] =
                            $zeitFilter->filter($daten['kernEnde']);
                    $daten['rahmenEnde'] =
                            $zeitFilter->filter($daten['rahmenEnde']);
                    $daten['soll'] = $zeitFilter->filter($daten['soll']);

                    $this->model->saveArbeitsregel($daten);

                    $redirector = $this->_helper->getHelper('Redirector');
                    $redirector->gotoRoute(array(
                        'benutzername' => $daten['benutzername'],
                            ), 'mitarbeiterdetail');
                }
            } elseif (isset($postDaten['loeschen'])) {
                $this->model->deleteArbeitsregel($postDaten['id']);

                $redirector = $this->_helper->getHelper('Redirector');
                $redirector->gotoRoute(array(
                    'benutzername' => $postDaten['benutzername'],
                        ), 'mitarbeiterdetail');
            }
        }

        $arbeitsregeln = $zuBearbeitenderMitarbeiter->getArbeitsregeln();
        $arbeitsregelnOben = array();
        $arbeitsregelnUnten = array();
        if ($id != 0) {
            foreach ($arbeitsregeln as $arbeitsregel) {
                if ($arbeitsregel->id < $id) {
                    $arbeitsregelnOben[] = $arbeitsregel;
                } elseif ($arbeitsregel->id > $id) {
                    $arbeitsregelnUnten[] = $arbeitsregel;
                }
            }
        } else {
            $arbeitsregelnOben = $arbeitsregeln;
        }
        if (count($arbeitsregelnOben) != 0) {
            $this->view->zeitDatenOben =
                    $this->_befuelleDieZeitenTabelle($arbeitsregelnOben);
            $this->view->zeilenOben = count($arbeitsregelnOben);
        }
        if (count($arbeitsregelnUnten) != 0) {
            $this->view->zeitDatenUnten =
                    $this->_befuelleDieZeitenTabelle(
                    $arbeitsregelnUnten, count($arbeitsregelnOben) + 1);
            $this->view->zeilenUnten = count($arbeitsregelnUnten);
        }

        $this->view->form = $form;
        $this->view->mitarbeiter = $benutzername;
    }

    public function monateAction() {
        $this->erweitereSeitenName(' Monatsauswahl');

        $heute = new Zend_Date();
        $heute->setMonth(1);
        //$this->_log->debug($heute->toString('MMMM yyyy'));
        $monate = array();
        for ($index = 0; $index < 12; $index++) {
            $monat = new Zend_Date($heute);
            $monate[] = $monat;
            $heute->add(1, Zend_Date::MONTH);
        }
        $heute->add(-2, Zend_Date::YEAR);
        for ($index = 0; $index < 12; $index++) {
            $monat = new Zend_Date($heute);
            $monate[] = $monat;
            $heute->addMonth(1);
        }

        $mitarbeiterModell = $this->model;
        $hochschule = $this->mitarbeiter->getHochschule();
        $monatsDaten = new Zend_Dojo_Data();
        $monatsDaten->setIdentifier('id');
        foreach ($monate as $monat) {
            //$this->_log->debug($monat->toString('MMMM yyyy'));
            $abgeschlossenUndAbgelegt =
                    $mitarbeiterModell->
                    getAbgeschlossenAbgelegtNachMonatUndHochschule(
                    $monat, $hochschule);

            $monatsDaten->addItem(array(
                'id' => $monat->toString('MMyyyy'),
                'monat' => $monat->toString('MMMM yyyy'),
                'abgeschlossen' => $abgeschlossenUndAbgelegt['abgeschlossen'],
                'abgelegt' => $abgeschlossenUndAbgelegt['abgelegt'],
            ));
        }
        $this->view->monatsDaten = $monatsDaten;
    }

    public function monatsdetailAction() {
        $para = $this->_getParam('monat');
        $para = substr($para, 1);
        $monat = new Zend_Date($para, 'MMyyyy');
        $this->erweitereSeitenName(' Monatsdetail ' .
                $monat->toString('MMMM yyyy'));
        $this->view->monat = $monat->toString('MM_yyyy');

        // intialisiere die Tabelle
        $mitarbeiterDaten = new Zend_Dojo_Data();
        $mitarbeiterDaten->setIdentifier('mitarbeiter');
        $zeilen = 0;

        // hole die Mitarbeiter der Hochschule
        $hsMitarbeiter = $this->model->getMitarbeiterNachHochschule(
                $this->mitarbeiter->getHochschule());

        // füge die Mitarbeiter der Tabelle hinzu
        foreach ($hsMitarbeiter as $mitarbeiter) {
            $arbeitsmonat = $mitarbeiter->getArbeitsmonat($monat);
            if ($arbeitsmonat === null) {
                $abgeschlossen = 'Nein';
                $abgelegt = 'Nein';
            } else {
                $abgeschlossen = 'Ja';
                $abgelegt = $arbeitsmonat->abgelegt == 'ja' ? 'Ja' : 'Nein';
            }
            $mitarbeiterDaten->addItem(array(
                'mitarbeiter' => $mitarbeiter->benutzername,
                'mitarbeitername' => $mitarbeiter->getName(),
                'abgeschlossen' => $abgeschlossen,
                'abgelegt' => $abgelegt,
            ));
            $zeilen++;
        }

        $this->view->mitarbeiterDaten = $mitarbeiterDaten;
        $this->view->zeilen = $zeilen;
    }

    public function monatseditAction() {
        $monatPara = $this->_getParam('monat');
        $monat = new Zend_Date($monatPara, 'MM_yyyy');
        $benutzername = $this->_getParam('benutzername');
        $zuBearbeitenderMitarbeiter = $this->model->
                getMitarbeiterNachBenutzername($benutzername);

        $this->erweitereSeitenName(' ' . $monat->toString('MMM yyyy') .
                ' ' . $zuBearbeitenderMitarbeiter->getName());

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postDaten = $request->getPost();
            if (isset($postDaten['zurueck'])) {
                $zuBearbeitenderMitarbeiter->deleteArbeitsmonat($monat);
            } elseif (isset($postDaten['ablegen'])) {
                $zuBearbeitenderMitarbeiter->arbeitsmonatAblegen($monat);
            } elseif ($postDaten['anzeigen']) {
                $this->_helper->
                        redirector('monatanzeigen', 'bueroleitung', null, array(
                            'monat' => $monatPara,
                            'mitarbeiter' => $benutzername,
                        ));
            }
        }

        $form = new Azebo_Form_Mitarbeiter_Monatsedit();
        if ($zuBearbeitenderMitarbeiter->getArbeitsmonat($monat) === null) {
            $form->removeElement('ablegen');
            $form->removeElement('zurueck');
        } elseif ($zuBearbeitenderMitarbeiter->getArbeitsmonat($monat)->abgelegt
                == 'ja') {
            $form->removeElement('ablegen');
            $form->removeElement('zurueck');
        }

        $url = $this->view->url(array(
            'benutzername' => $benutzername,
            'monat' => $monat->toString('MM_yyyy'),
                ), 'monatsedit');
        $form->setAction($url);
        $this->view->form = $form;
    }

    public function monatanzeigenAction() {
        $benutzername = $this->_getParam('mitarbeiter');
        $mitarbeiter = $this->model->
                getMitarbeiterNachBenutzername($benutzername);
        $monatParam = $this->_getParam('monat');
        $monat = new Zend_Date($monatParam, 'MM_yyyy');

        $this->erweitereSeitenName(' Anzeigen ' . $mitarbeiter->getName() .
                ' ' . $monat->toString('MMM yyyy'));

        // befülle die Reihen der Tabelle
        $tageImMonat = $monat->get(Zend_Date::MONTH_DAYS);
        $erster = new Zend_Date($monat);
        $letzter = new Zend_Date($monat);
        $erster->setDay(1);
        $letzter->setDay($tageImMonat);
        $tabelle = $this->_helper->
                MonatsTabelle($erster, $letzter, $mitarbeiter);

        // füge die Tabelle dem View hinzu
        $this->view->tageImMonat = $tageImMonat;
        $this->view->monatsDaten = $tabelle['tabellenDaten'];
        $this->view->hoheTageImMonat = $tabelle['hoheTage'];
        $this->view->extraZeilen = $tabelle['extraZeilen'];
        
        // übergebe dem View die Hochschule
        $this->view->hochschule = $mitarbeiter->getHochschule();
        
        // setze die Salden
        $saldoBisher = $mitarbeiter->getSaldoBisher($monat);
        $this->view->saldoBisher = $saldoBisher->getString();
        $saldo = $mitarbeiter->getSaldo($monat, true);
        $this->view->saldo = $saldo->getString();
        $saldoGesamt = Azebo_Model_Saldo::copy($saldoBisher);
        $saldoGesamt->add($saldo, true);
        $this->view->saldoGesamt = $saldoGesamt->getString();
        if ($mitarbeiter->getHochschule() == 'hfm' &&
                $saldoBisher->getRest()) {
            $this->view->hatRest = true;
            $this->view->saldoBisher2007 = $saldoBisher->getRestString();
            $this->view->saldoGesamt2007 = $saldoGesamt->getRestString();
        }
        $this->view->urlaubBisher = $mitarbeiter->getUrlaubBisher();
        $this->view->urlaub = $mitarbeiter->getUrlaubNachMonat($monat);
    }

    private function _getNeuerMitarbeiterForm($mitglieder) {
        $form = new Azebo_Form_Mitarbeiter_Neuermitarbeiter();

        // befülle die Auswahl
        $mitgliederOptions = array();
        foreach ($mitglieder as $mitglied) {
            $mitgliederOptions[$mitglied] = $mitglied;
        }
        $auswahlElement = $form->getElement('auswahl');
        $auswahlElement->setAttrib('options', $mitgliederOptions);

        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
            'controller' => 'bueroleitung',
            'action' => 'neu'));
        $form->setAction($url);
        $form->setMethod('post');
        $form->setName('neuermitarbeiterForm');

        return $form;
    }

    private function _getMitarbeiterDetailForm($benutzername, $neu) {
        $form = new Azebo_Form_Mitarbeiter_Mitarbeiterdetail();
        if (!$neu) {
            $mitarbeiter =
                    $this->model->getMitarbeiterNachBenutzername($benutzername);
            $beamter = $mitarbeiter->getBeamter() == 'ja' ? true : false;
            $saldo = $mitarbeiter->getSaldouebertrag();
            $urlaub = $mitarbeiter->urlaub;
        } else {
            $mitarbeiterTabelle = new Azebo_Resource_Mitarbeiter();
            $mitarbeiter = $mitarbeiterTabelle->createRow();
            $beamter = false;
            $saldo = new Azebo_Model_Saldo(0, 0, true);
            $urlaub = 0;
        }

        $elemente = $form->getElements();
        $elemente['beamter']->setAttrib('checked', $beamter);
        $elemente['saldo']->setValue($saldo->getString());
        $elemente['urlaub']->setValue($urlaub);
        
        if($this->mitarbeiter->getHochschule() != 'hfm' || $beamter) {
            $form->removeElement('saldo2007');
        }

        $form->addElement('hidden', 'benutzername', array(
            'value' => $benutzername,
        ));

        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
            'benutzername' => $benutzername,
                ), 'mitarbeiterdetail', true);
        $form->setAction($url);
        $form->setMethod('post');
        $form->setName('detailForm');

        return $form;
    }

    private function _getArbeitsregelForm($benutzername, $id) {
        $form = new Azebo_Form_Mitarbeiter_Arbeitsregel();

        //bevölkere die Form mit den nötigen Feldern
        $arbeitsregel = $this->model->getArbeitsregelNachId($id);
        $elemente = $form->getElements();
        $elemente['benutzername']->setValue($benutzername);
        $elemente['id']->setValue($id);


        //bevölkere den Rest der Form, falls die Regel nicht neu ist
        if ($id != 0) {
            $von = $arbeitsregel->getVon()->toString('dd.MM.yyyy');
            $bis = $arbeitsregel->getBis() === null ? '' :
                    $arbeitsregel->getBis()->toString('dd.MM.yyyy');
            $wochentag = strtolower($arbeitsregel->getWochentag());
            $kw = $arbeitsregel->kalenderwoche;
            $rahmenAnfang = $arbeitsregel->getRahmenAnfang() === null ? '' :
                    $arbeitsregel->getRahmenAnfang()->toString('HHmm');
            $kernAnfang = $arbeitsregel->getKernAnfang() === null ? '' :
                    $arbeitsregel->getKernAnfang()->toString('HHmm');
            $kernEnde = $arbeitsregel->getKernEnde() === null ? '' :
                    $arbeitsregel->getKernEnde()->toString('HHmm');
            $rahmenEnde = $arbeitsregel->getRahmenEnde() === null ? '' :
                    $arbeitsregel->getRahmenEnde()->toString('HHmm');
            $soll = $arbeitsregel->getSoll()->toString('HHmm');
            $elemente['von']->setDijitParam('displayedValue', $von);
            $elemente['bis']->setDijitParam('displayedValue', $bis);
            $elemente['wochentag']->setValue($wochentag);
            $elemente['kw']->setValue($kw);
            $elemente['rahmenAnfang']->setDijitParam(
                    'displayedValue', $rahmenAnfang);
            $elemente['kernAnfang']->setDijitParam(
                    'displayedValue', $kernAnfang);
            $elemente['kernEnde']->setDijitParam(
                    'displayedValue', $kernEnde);
            $elemente['rahmenEnde']->setDijitParam(
                    'displayedValue', $rahmenEnde);
            $elemente['soll']->setDijitParam('displayedValue', $soll);
        } else {
            // neue Regel, also entferne den löschen-Button und belege das
            // Soll vor
            $form->removeElement('loeschen');

            $zeiten = $this->ns->zeiten;
            $zuBearbeitenderMitarbeiter = $this->model->
                    getMitarbeiterNachBenutzername($benutzername);
            $beamter = $zuBearbeitenderMitarbeiter->getBeamter();
            $soll = $beamter ? $zeiten->soll->beamter : $zeiten->soll->normal;
            $soll = new Zend_Date($soll, 'HH:mm:ss');
            $form->getElement('soll')->
                    setDijitParam('displayedValue', $soll->toString('HHmm'));
        }

        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
            'benutzername' => $benutzername,
            'id' => $id,
                ), 'arbeitsregel', true);
        $form->setAction($url);
        $form->setMethod('post');
        $form->setName('regelForm');

        return $form;
    }

    private function _befuelleDieZeitenTabelle($arbeitsregeln, $offset = 0) {
        // initialisiere die Tabellendaten
        $zeitDaten = new Zend_Dojo_Data();
        $zeitDaten->setIdentifier('id');
        $lfdNr = $offset;

        // befülle die Tabellendaten
        foreach ($arbeitsregeln as $arbeitsregel) {
            $lfdNr++;
            $von = $arbeitsregel->getVon()->toString('dd.MM.yyyy');
            $bis = $arbeitsregel->getBis();
            $bis = $bis === null ? 'auf Weiteres' : $bis->toString('dd.MM.yyyy');
            $wochentag = $arbeitsregel->getWochentag();
            $kw = $arbeitsregel->kalenderwoche;
            $rahmenAnfang = $arbeitsregel->getRahmenAnfang();
            $rahmenAnfang = $rahmenAnfang === null ? 'normal' :
                    $rahmenAnfang->toString('HH:mm');
            $kernAnfang = $arbeitsregel->getKernAnfang();
            $kernAnfang = $kernAnfang === null ? 'normal' :
                    $kernAnfang->toString('HH:mm');
            $kernEnde = $arbeitsregel->getKernEnde();
            $kernEnde = $kernEnde === null ? 'normal' :
                    $kernEnde->toString('HH:mm');
            $rahmenEnde = $arbeitsregel->getRahmenEnde();
            $rahmenEnde = $rahmenEnde === null ? 'normal' :
                    $rahmenEnde->toString('HH:mm');
            $soll = $arbeitsregel->getSoll()->toString('HH:mm');
            $zeitDaten->addItem(array(
                'id' => $arbeitsregel->id,
                'lfdNr' => $lfdNr,
                'von' => $von,
                'bis' => $bis,
                'wochentag' => $wochentag,
                'kw' => $kw,
                'rahmenanfang' => $rahmenAnfang,
                'kernanfang' => $kernAnfang,
                'kernende' => $kernEnde,
                'rahmenende' => $rahmenEnde,
                'soll' => $soll,
            ));
        }

        return $zeitDaten;
    }

}
