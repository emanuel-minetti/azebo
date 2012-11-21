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

    public function init() {
        parent::init();
        
        // hole den Parameter und setze das Datum
        $jahr = $this->_getParam('jahr');
        $this->jahr = new Zend_Date($jahr, 'yyyy');
        
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
        $this->view->jahr = $this->_getParam('jahr'); 
    }
}

