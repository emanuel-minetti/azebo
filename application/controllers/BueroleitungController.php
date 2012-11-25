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

    public function init() {
        parent::init();

        // Lade den Mitarbeiter
        $ns = new Zend_Session_Namespace();
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
        }

        $this->view->mitarbeiterDaten = $mitarbeiterDaten;
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
            // intialisiere die Tabelle
            $zeitDaten = new Zend_Dojo_Data();
            $zeitDaten->setIdentifier('id');

            $arbeitsregeln = $zuBearbeitenderMitarbeiter->getArbeitsregeln();
            foreach ($arbeitsregeln as $arbeitsregel) {
                $von = $arbeitsregel->getVon()->toString('dd.MM.YYYY');
                $bis = $arbeitsregel->getBis();
                $bis = $bis === null ? 'immer' : $bis->toString('dd.MM.YYYY');
                $wochentag = $arbeitsregel->getWochentag();
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
                    'von' => $von,
                    'bis' => $bis,
                    'wochentag' => $wochentag,
                    'rahmenanfang' => $rahmenAnfang,
                    'kernanfang' => $kernAnfang,
                    'kernende' => $kernEnde,
                    'rahmenende' => $rahmenEnde,
                    'soll' => $soll,
                ));
            }
            $this->view->zeitDaten = $zeitDaten;
            $this->view->mitarbeiter = $benutzername;
        }
    }

    public function neuauswahlAction() {
        $this->erweitereSeitenName(' Neuer Mitarbeiter');
    }

    public function neuAction() {
        $this->erweitereSeitenName(' Neuer Mitarbeiter');
        $hochschule = $this->_getParam('hochschule');
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
        $this->erweitereSeitenName(' Bearbeite Arbeitszeit');
        $benutzername = $this->_getParam('mitarbeiter');
        $id = $this->_getParam('id');
        $form = $this->_getArbeitsregelForm($benutzername, $id);

        $this->view->form = $form;
        //TODO Zeige übrige Regeln in einer/zwei Tabellen an!
    }

    public function monateAction() {
        
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
        
        //TODO bevölkere die Form!

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

}
