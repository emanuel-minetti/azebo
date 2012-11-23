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

    public function init() {
        parent::init();

        // Lade den Mitarbeiter
        $ns = new Zend_Session_Namespace();
        $this->mitarbeiter = $ns->mitarbeiter;

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
        $model = new Azebo_Model_Mitarbeiter();
        $hsMitarbeiter = $model->getMitarbeiterNachHochschule($this->mitarbeiter->getHochschule());

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
        $this->erweitereSeitenName(' Bearbeite Mitarbeiter');

        $benutzername = $this->_getParam('benutzername');
        $model = new Azebo_Model_Mitarbeiter();

        $zuBearbeitenderMitarbeiter = $model->
                getMitarbeiterNachBenutzername($benutzername);
        if ($zuBearbeitenderMitarbeiter !== null) {
            $name = $zuBearbeitenderMitarbeiter->getName();
        } else {
            $name = $model->getNameNachBenutzername($benutzername);
        }
        
        $this->view->mitarbeiter = $name;
    }

    public function neuauswahlAction() {
        $this->erweitereSeitenName(' Neuer Mitarbeiter');
    }

    public function neuAction() {
        $this->erweitereSeitenName(' Neuer Mitarbeiter');
        $hochschule = $this->_getParam('hochschule');
        $model = new Azebo_Model_Mitarbeiter();
        $mitglieder = $model->getBenutzernamenNachHochschule($hochschule);
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

    public function monateAction() {
        
    }

    private function _getNeuerMitarbeiterForm($mitglieder) {
        $mitgliederOptions = array();
        foreach ($mitglieder as $mitglied) {
            $mitgliederOptions[$mitglied] = $mitglied;
        }
        $form = new Azebo_Form_Mitarbeiter_Neuermitarbeiter();
        $auswahlElement = new Zend_Dojo_Form_Element_FilteringSelect('auswahl', array(
                    'label' => 'Neuer Mitarbeiter: ',
                    'multiOptions' => $mitgliederOptions,
                    'invalidMessage' => Azebo_Form_Mitarbeiter_Neuermitarbeiter::UNGUELTIGE_OPTION,
                    'filters' => array('StringTrim', 'Alpha'),
                    'tabindex' => 1,
                    'autofocus' => true,
                ));
        $form->addElement($auswahlElement);
        $form->addElement('SubmitButton', 'hinzufügen', array(
            'required' => false,
            'ignore' => true,
            'label' => 'Hinzufügen',
            'decorators' => array('DijitElement'),
            'tabindex' => 2,
        ));
        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
            'controller' => 'bueroleitung',
            'action' => 'neu'));
        $form->setAction($url);
        $form->setMethod('post');
        $form->setName('neuermitarbeiterForm');

        return $form;
    }

}

