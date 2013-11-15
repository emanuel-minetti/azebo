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

/**
 * Description of UebersichtController
 *
 * @author Emanuel Minetti
 */
class UebersichtController extends AzeboLib_Controller_Abstract {

    /**
     * @var Zend_Date 
     */
    public $jahr;

    /**
     * @var Azebo_Resource_Mitarbeiter_Item_Interface 
     */
    public $mitarbeiter;
    public $arbeitsmonate;

    public function init() {
        parent::init();

        // hole den Parameter und setze das Datum
        $jahr = $this->_getParam('jahr');
        $this->jahr = new Zend_Date($jahr, 'yyyy');

        // Lade den Mitarbeiter und die Monate
        $ns = new Zend_Session_Namespace();
        $this->mitarbeiter = $ns->mitarbeiter;
        $this->arbeitsmonate = $this->mitarbeiter->
                getArbeitsmonateNachJahr($this->jahr);

        // Aktiviere Dojo
        $this->view->dojo()->enable()
                ->setDjConfigOption('parseOnLoad', true)
                ->requireModule('dojox.grid.DataGrid')
                ->requireModule('dojo.data.ItemFileReadStore')
                ->requireModule('dojo._base.connect');
    }

    public function getSeitenName() {
        return 'Übersicht';
    }

    public function indexAction() {
        $this->erweitereSeitenName(' ' . $this->jahr->toString('yyyy'));

        // Initialisiere die Daten der Tabelle
        $jahresDaten = new Zend_Dojo_Data();
        $jahresDaten->setIdentifier('monat');

        // befülle die Tabelle
        foreach ($this->arbeitsmonate as $arbeitsmonat) {
            $monat = $arbeitsmonat->getMonat();
            $saldo = $arbeitsmonat->getSaldo();
            $abgeschlossen = $saldo->getStunden() === null ? 'Nein' : 'Ja';
            $urlaub = $arbeitsmonat->urlaub;
            $jahresDaten->addItem(array(
                'monat' => $monat->toString('MMMM'),
                'abgeschlossen' => $abgeschlossen,
                'saldo' => $saldo->getString(),
                'urlaub' => $urlaub,
            ));
        }

        $this->view->jahresDaten = $jahresDaten;

        // Falls der Mitarbeiter zur HfS gehört, soll der Vertreter-Link
        // angezeigt werden.
        // Also übergebe den Mitarbeiter und die Hochschule an den View
        $this->view->hochschule = $this->mitarbeiter->getHochschule();
    }
    
    public function vertreterAction() {
        // Falls der Mitarbeiter schon einen Vertreter hat, leite ihn direkt
        // auf die edit-Seite weiter.
        if ($this->mitarbeiter->hatVertreter()) {
            $vertreterId = $this->mitarbeiter->vertreter;
            $mitarbeiterTabelle = new Azebo_Resource_Mitarbeiter();
            $vertreter = $mitarbeiterTabelle->getMitarbeiterNachId($vertreterId);
            $urlHelper = $this->_helper->getHelper('url');
            $url = $urlHelper->url(array(
                'vertreter' => $vertreter->benutzername,
                    ), 'vertreter', true);
            $this->_redirect($url);
        }

        $this->erweitereSeitenName(' Vertreter auswählen');

        $mitarbeiterTabelleService = new Azebo_Service_MitarbeiterTabelle();
        $daten = $mitarbeiterTabelleService->_getMitarbeiterTabellenDaten($this->mitarbeiter, null);

        $this->view->mitarbeiterDaten = $daten['daten'];
        $this->view->zeilen = $daten['zeilen'];
    }

    public function vertretereditAction() {
        $this->erweitereSeitenName(' Vertreter einrichten');

        $neu = !$this->mitarbeiter->hatVertreter();
        $erfolg = false;

        $vertreter = $this->_getParam('vertreter');
        $mitarbeiterModel = new Azebo_Model_Mitarbeiter();
        $vertreterItem = $mitarbeiterModel->getMitarbeiterNachBenutzername($vertreter);
        $vertreterName = $vertreterItem->getName();

        $form = new Azebo_Form_Mitarbeiter_Vertreter();
        $form->getElement('vertreter')->setValue($vertreter);
        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
            'vertreter' => $vertreter,
                ), 'vertreter', true);
        $form->setAction($url);
        if ($neu) {
            $form->removeElement('entfernen');
        } else {
            $form->removeElement('abschliessen');
            $form->removeElement('zurueck');
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $postDaten = $request->getPost();
            $valid = $form->isValid($postDaten);
            if (isset($postDaten['abschliessen']) && $valid) {
                $this->mitarbeiter->vertreter = $vertreterItem->id;
                $this->mitarbeiter->save();
                $ns = new Zend_Session_Namespace();
                $ns->mitarbeiter = $this->mitarbeiter;
                $erfolg = true;
            } elseif (isset($postDaten['entfernen'])) {
                $this->mitarbeiter->vertreter = null;
                $this->mitarbeiter->save();
                $ns = new Zend_Session_Namespace();
                $ns->mitarbeiter = $this->mitarbeiter;
                $erfolg = true;
            } elseif (isset($postDaten['zurueck'])) {
                $url = $urlHelper->url(array(
                    'controller' => 'uebersicht',
                    'action' => 'vertreter',
                        ), 'default', true);
                $this->_redirect($url);
            }
        }
        $this->view->vertreter = $vertreterName;
        $this->view->neu = $neu;
        $this->view->erfolg = $erfolg;
        $this->view->form = $form;
    }
    
    public function farbenAction() {
         $this->erweitereSeitenName(' Farben einrichten');
    }

}

