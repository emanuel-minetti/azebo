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
 * Description of Acl
 *
 * @author Emanuel Minetti
 */
class Azebo_Plugin_Acl extends Zend_Controller_Plugin_Abstract {

    protected $_auth;
    protected $_acl;
    
    protected $_controller;
    protected $_action;
    protected $_role;

    public function __construct(Zend_Acl $acl) {       
        $this->_auth = Zend_Auth::getInstance();
        $this->_acl = $acl;
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request) {
        
        $this->_init($request);

        if (!$this->_acl->isAllowed($this->_role, $this->_controller, $this->_action)) {
            // der Benutzer darf die angefragte Seite nicht sehen
            if ($this->_role == 'gast') {
                // der Benutzer ist nicht angemeldet
                $request->setControllerName('login');
                $request->setActionName('login');
            } else {
                $request->setControllerName('error');
                $request->setActionName('nichterlaubt');
            }
        }
    }

    protected function _init($request) {
        $this->_action = $request->getActionName();
        $this->_controller = $request->getControllerName();
        $this->_role = $this->_getCurrentUserRole();
    }

    protected function _getCurrentUserRole() {
        if ($this->_auth->hasIdentity()) {
            $authData = $this->_auth->getIdentity();
            $role = isset($authData->rolle) ?
                    strtolower($authData->rolle) : 'gast';
        }
        else {
            $role = 'gast';
        }    
        return $role;
    }

}

