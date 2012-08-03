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


//TODO Authentifizierungs-Service Kommentieren
/**
 * Description of Authentication
 *
 * @author Emanuel Minetti
 */
class Azebo_Service_Authentication {
    protected $_logger;

    protected $_authAdapter;
    protected $_mitarbeiterModell;
    protected $_auth;

    public function __construct(Azebo_Model_Mitarbeiter $model = null) {
        $this->_logger = Zend_Registry::get('log');
        $this->_logger->info('Azebo_Service_Authentication ' . __METHOD__);
        
        $this->_mitarbeiterModell = null === $model ?
                new Azebo_Model_Mitarbeiter() : $model;
    }

    public function authenticate($daten) {
        $this->_logger->info('Azebo_Service_Authentication ' . __METHOD__);
        
        $adapter = $this->getAuthAdapter($daten);
        $auth = $this->getAuth();
        $ergebnis = $auth->authenticate($adapter);

        if (!$ergebnis->isValid()) {
            return false;
        }

        $mitarbeiter = $this->_mitarbeiterModell
                ->getMitarbeiterNachBenutzername($daten['benutzername']);
        $auth->getStorage()->write($mitarbeiter);

        return true;
    }

    public function getAuth() {
        if (null === $this->_auth) {
            $this->_auth = Zend_Auth::getInstance();
        }
        return $this->_auth;
    }

    public function getIdentity() {
        $auth = $this->getAuth();
        if ($auth->hasIdentity()) {
            return $auth->getIdentity();
        }
        return false;
    }

    public function clear() {
        $this->getAuth()->clearIdentity();
    }

    public function setAuthAdapter(Zend_Auth_Adapter_Interface $adapter) {
        $this->_authAdapter = $adapter;
    }
     
    //TODO Dros nach testusern und LDAP fragen
    //TODO LDAP implementieren
    public function getAuthAdapter($daten) {
        if (null === $this->_authAdapter) {
            $authAdapter = new Zend_Auth_Adapter_DbTable(
                            Zend_Db_Table_Abstract::getDefaultAdapter(),
                            'mitarbeiter',
                            'benutzername',
                            'passwort',
                            '');
            $this->setAuthAdapter($authAdapter);
            $this->_authAdapter->setIdentity($daten['benutzername']);
            $this->_authAdapter->setCredential($daten['passwort']);
        }
        return $this->_authAdapter;
    }
}
