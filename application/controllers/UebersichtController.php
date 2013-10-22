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
        $this->view->mitarbeiter = $this->mitarbeiter->benutzername;
        $this->view->hochschule = $this->mitarbeiter->getHochschule();
    }
    
    public function vertreterAction() {
        $this->erweitereSeitenName(' Vertreter einrichten');
    }
}

