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
                'mitarbeiter' => $mitarbeiter->getName(),
                'abgeschlossen' => 'März 2012',
                'abgelegt' => 'Februar 2012',
            ));
        }

        $this->view->mitarbeiterDaten = $mitarbeiterDaten;
    }

    public function monateAction() {
        
    }

}

