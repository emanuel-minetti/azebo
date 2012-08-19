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

    protected $_log;
    protected $_authAdapter;
    /**
     * @var Azebo_Model_Mitarbeiter 
     */
    protected $_mitarbeiterModell;
    protected $_auth;

    public function __construct(Azebo_Model_Mitarbeiter $model = null) {
        $this->_log = Zend_Registry::get('log');

        $this->_mitarbeiterModell = null === $model ?
                new Azebo_Model_Mitarbeiter() : $model;
    }

    public function authenticate($daten) {
        $this->_log->info('Azebo_Service_Authentication ' . __METHOD__);

        $adapter = $this->getAuthAdapter($daten);
        $auth = $this->getAuth();
        $ergebnis = $auth->authenticate($adapter);

//        $nachrichten = $ergebnis->getMessages();
//        foreach ($nachrichten as $i => $nachricht) {
//            if ($i-- > 1) { // $messages[2] and up are log messages
//                $nachricht = str_replace("\n", "\n  ", $nachricht);
//                $this->_log->log("Ldap: $i: $nachricht", Zend_Log::DEBUG);
//            }
//        }

        if (!$ergebnis->isValid()) {
            return 'FehlerLDAP';
        }

        //hole die Gruppen in denen der Benutzer Mitglied ist
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/ldap.ini');
        $options = $config->ldap->physalis->toArray();
        $gruppen = array();
        $attributes = array('cn');
        $ldap = new Zend_Ldap($options);
        $users = $ldap->search('(&(objectClass=posixGroup)(memberUid=' . $daten['benutzername'] . '))',
                'OU=Groups,DC=verwaltung,DC=kh-berlin,DC=de',
                Zend_Ldap::SEARCH_SCOPE_SUB, $attributes);
        foreach ($users as $user) {
            $gruppen[] = $user['cn'][0];
        }
        $this->_log->debug('Gruppen: ' . print_r($gruppen, true));
        
        //Hole den Mitarbeiter aus dem Modell und setze Rolle und Hochschule
        $mitarbeiter = $this->_mitarbeiterModell
                ->getMitarbeiterNachBenutzername($daten['benutzername']);
        if($mitarbeiter === null) {
            $this->clear();
            return 'FehlerDB';
        }
        $mitarbeiter->setRolle($gruppen);
        $mitarbeiter->setHochschule($gruppen);
               
        //sicherstellen, dass ein Azebo_Mitarbeiter_Resource_Item in der
        //Session gespeichert wird. Darauf wird häufig zugegriffen.
        $auth->getStorage()->write($mitarbeiter);

        return 'Erfolg';
    }

    public function getAuth() {
        if (null === $this->_auth) {
            $this->_auth = Zend_Auth::getInstance();
        }
        return $this->_auth;
    }

    /**
     * Gibt ein 'Azebo_Mitarbeiter_Resource_Item' zurück, oder 'null' falls
     * der Benuter nicht angemeldet ist.
     * 
     * @return Azebo_Mitarbeiter_Resource_Item
     */
    public function getIdentity() {
        $auth = $this->getAuth();
        if ($auth->hasIdentity()) {
            $this->_log->info('Identität: ' . $auth->getIdentity()->getName());
            return $auth->getIdentity();
        }
        return null;
    }

    public function clear() {
        $this->getAuth()->clearIdentity();
    }

    public function setAuthAdapter(Zend_Auth_Adapter_Interface $adapter) {
        $this->_authAdapter = $adapter;
    }

    public function getAuthAdapter($daten) {
        if (null === $this->_authAdapter) {
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/ldap.ini');
            $options = $config->ldap->toArray();
            $authAdapter = new Zend_Auth_Adapter_Ldap($options, $daten['benutzername'], $daten['passwort']);
            $this->setAuthAdapter($authAdapter);
        }
        return $this->_authAdapter;
    }

}
