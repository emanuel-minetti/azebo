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
 *     Copyright 2012 Emanuel Minetti (e.minetti (at) posteo.de)
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
    protected $_errors;

    public function __construct(Zend_Acl $acl) {
        $this->_auth = Zend_Auth::getInstance();
        $this->_acl = $acl;
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request) {

        $this->_init($request);

        if (!$this->_acl->hasRole($this->_role)) {
            //Die Rolle des Butzers existiert nicht in der ACL!
            //Also 500!
            $this->_errors->type =
                    Zend_Controller_Plugin_ErrorHandler::EXCEPTION_OTHER;
            $this->_errors->request = $request;
            $this->_errors->exception = new AzeboLib_Exception(
                            'Die Rolle des Benutzers existiert nicht!',
                            null, null);
            $request->setControllerName('error');
            $request->setActionName('error');
            $request->setParam('error_handler', $this->_errors);
        } elseif (!$this->_acl->has($this->_controller)) {
            //Der angefragte Controller existiert nicht in der ACL.
            //Also 404!
            $this->_errors->type =
                    Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER;
            $this->_errors->request = $request;
            $this->_errors->exception = new AzeboLib_Exception(
                            'Der angefragte Controller existiert nicht!',
                            null, null);
            $request->setControllerName('error');
            $request->setActionName('error');
            $request->setParam('error_handler', $this->_errors);
        } elseif (!$this->_acl->isAllowed(
                        $this->_role, $this->_controller, $this->_action)) {
            // der Benutzer darf die angefragte Seite nicht sehen
            if ($this->_role == 'gast') {
                // Der Benutzer ist nicht angemeldet
                // Also anmelden!
                $request->setControllerName('login');
                $request->setActionName('login');
            } else {
                // Der Besucher macht was böses!!
                //Also 403!
                $this->_errors->request = $request;
                $this->_errors->exception = new AzeboLib_Exception(
                                'Auf diese Seite haben Sie keinen Zugriff!',
                                null, null);
                $request->setParam('error_handler', $this->_errors);
                $request->setControllerName('error');
                $request->setActionName('nichterlaubt');
            }
        }
        else {
            // Der Besucher darf die angefragte Seite sehen.
            // Also nur noch prüfen ob die 'Büroleitung'-Navigation eigeblendet
            // werden soll.
            if($this->_acl->isAllowed($this->_role, 'bueroleitung')) {
                $request->setParam('istBueroleitung', true);
            }
        }
    }

    protected function _init($request) {
        $this->_action = $request->getActionName();
        $this->_controller = $request->getControllerName();
        $this->_role = $this->_getBenutzerRolle();
        $this->_errors = new ArrayObject(array());
    }

    protected function _getBenutzerRolle() {
        $ns = new Zend_Session_Namespace();
        if($ns->mitarbeiter !== null) {
            $rolle = $ns->mitarbeiter->getRolle();
        } else {
            $rolle = 'gast';
        }
        return $rolle;
    }
}

