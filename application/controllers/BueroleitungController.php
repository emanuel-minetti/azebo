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
        
        $ns = new Zend_Session_Namespace();
        $this->mitarbeiter = $ns->mitarbeiter;
        
    }
    public function getSeitenName() {
        return 'Büroleitung';
    }

    public function indexAction() {
        $redirector = $this->_helper->getHelper('Redirector');
        $redirector->gotoSimple('mitarbeiter', 'bueroleitung');  
        
    }

    public function mitarbeiterAction() {
        $model = new Azebo_Model_Mitarbeiter();
        $hsMitarbeiter = $model->getMitarbeiterNachHochschule($this->mitarbeiter->getHochschule());
        foreach ($hsMitarbeiter as $mitarbeiter) {
            $this->_log->debug('Mitarbeiter: ' . $mitarbeiter->getName() );
        }
        
    }
    
    public function monateAction() {
        
    }


}

