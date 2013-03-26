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

class IndexController extends AzeboLib_Controller_Abstract {

    public function init() {
        parent::init();
    }
    public function getSeitenName() {
        return 'Index';
    }

    public function indexAction() {
        $now = new Zend_Date();
        $jahr = $now->toString('yyyy');
        $monat = $now->toString('MM');
        $redirector = $this->_helper->getHelper('Redirector');
        $redirector->gotoRoute(array(
            'jahr' => $jahr,
            'monat' => $monat,
        ), 'monat');        
    }
    
}

