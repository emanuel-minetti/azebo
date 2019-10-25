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
 *     Copyright 2012-19 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Stellt den Authentifizierungs-Service zur Verfügung. Die Authentifizierung
 * des Benutzers wird via LDAP vorgenommen.
 *
 * @author Emanuel Minetti
 */
class Azebo_Service_Authentifizierung {

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

    /**
     * Authentifiziert den Benutzer via LDAP. Falls das erfolgreich ist, werden
     * die Gruppen aus dem LDAP geholt in denen der Mitarbeiter Mitglied ist.
     * Mithilfe dieser Gruppen wird die Rolle und die Hochschulzugehörikeit
     * des Mitarbeiters festgelegt. Ist der Benutzer noch nicht in der DB 
     * angelegt, so wird eine Fehlermeldung zurückgegeben. War die gesammte
     * Authentifizierung erfolgreich wird ein
     * Azebo_Mitarbeiter_Resource_Item_Interface in der Session abgelegt.
     * 
     * @param array $daten Ein Array mit den Felder 'benutzername' und 'passwort'.
     * 
     * @return string Entweder 'FehlerLDAP' oder 'FehlerDB' oder 'Erfolg'.
     */
    public function authentifiziere(array $daten) {
        $adapter = $this->_getAuthAdapter($daten);
        $auth = $this->_getAuth();

        //TODO Hier schlägt #ZF-9378 zu!
        $ergebnis = $auth->authenticate($adapter);

        // Den folgenden Absatz auskommentieren, falls zu Debugging-Zwecken
        // keine Passwort-Prüfung stattfinden soll
        if (!$ergebnis->isValid()) {
            return 'FehlerLDAP';
        }

        //hole die Gruppen in denen der Benutzer Mitglied ist
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/ldap.ini');
        $options = $config->ldap->physalis->toArray();
        $gruppen = array();
        $attributes = array('cn');
        $ldap = new Zend_Ldap($options);
        $users = $ldap->search('(&(objectClass=posixGroup)(memberUid=' . $daten['benutzername'] . '))', 'OU=Groups,DC=verwaltung,DC=kh-berlin,DC=de', Zend_Ldap::SEARCH_SCOPE_SUB, $attributes);
        foreach ($users as $user) {
            $gruppen[] = $user['cn'][0];
        }

        //Hole den Namen aus dem LDAP
        $ldap->bind();
        $benutzer = $ldap->getEntry('uid=' . $daten['benutzername'] . ',ou=Users,dc=verwaltung,dc=kh-berlin,dc=de');
        $vorname = $benutzer['givenname'][0];
        $nachname = $benutzer['sn'][0];

        //Hole den Mitarbeiter aus dem Modell
        $mitarbeiter = $this->_mitarbeiterModell
                ->getMitarbeiterNachBenutzername($daten['benutzername']);
        if ($mitarbeiter === null) {
            $this->clear();
            return 'FehlerDB';
        }

        
        //Setze Rolle, Hochschule, Studi-Status, Vor- und Nachname
        $mitarbeiter->setRolle($gruppen);
        $mitarbeiter->setHochschule($gruppen);
        $mitarbeiter->setStudiHK($gruppen);
        $mitarbeiter->setVorname($vorname);
        $mitarbeiter->setNachname($nachname);

        //sicherstellen, dass ein Azebo_Mitarbeiter_Resource_Item
        //und die Hochschule in der
        //Session gespeichert wird. Darauf wird häufig zugegriffen.
        $auth->getStorage()->write($mitarbeiter);
        $ns = new Zend_Session_Namespace();
        $ns->mitarbeiter = $mitarbeiter;
        $hochschule = $mitarbeiter->getHochschule();
        $ns->hochschule = $hochschule;

        // die configs/zeiten.ini einlesen und in die Session geben
        $ns->zeiten = new Zend_Config_Ini(
                        APPLICATION_PATH . '/configs/zeiten.ini', $hochschule);
        
        // die configs/strings.ini einlesen und in die Session geben
        $ns->strings = new Zend_Config_Ini(
                        APPLICATION_PATH . '/configs/strings.ini', $hochschule);

        return 'Erfolg';
    }

    /**
     * @return Zend_Auth  
     */
    private function _getAuth() {
        if (null === $this->_auth) {
            $this->_auth = Zend_Auth::getInstance();
        }
        return $this->_auth;
    }

    /**
     * Gibt ein 'Azebo_Mitarbeiter_Resource_Item' zurück, oder 'null' falls
     * der Benuter nicht angemeldet ist.
     * 
     * @return null/Azebo_Mitarbeiter_Resource_Item
     */
    public function getIdentity() {
        $auth = $this->_getAuth();
        if ($auth->hasIdentity()) {
            $this->_log->debug('Identität: ' . $auth->getIdentity()->getName());
            return $auth->getIdentity();
        }
        return null;
    }

    /**
     * Löscht den Mitarbeiter aus der Session. 
     */
    public function clear() {
        $this->_getAuth()->clearIdentity();
    }

    /**
     * Zum Debuggen und Testen.
     * @param Zend_Auth_Adapter_Interface $adapter 
     */
    public function setAuthAdapter(Zend_Auth_Adapter_Interface $adapter) {
        $this->_authAdapter = $adapter;
    }

    /**
     *
     * @param array $daten Ein Array mit den Feldern 'benutzername' und 'passwort'.
     * @return Zend_Auth_Adapter_Ldap
     */
    private function _getAuthAdapter($daten) {
        if (null === $this->_authAdapter) {
            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/ldap.ini');
            $options = $config->ldap->physalis->toArray();
            //Der Konstruktor von Zend_Auth_Adapter_Ldap erwartet als Optionen
            //ein Array von Arrays, deren key ein Servername ist.
            $options = array(
                'physalis' => $options,
            );

            $authAdapter = new Zend_Auth_Adapter_Ldap($options, $daten['benutzername'], $daten['passwort']); 
            $this->setAuthAdapter($authAdapter);
        }
        return $this->_authAdapter;
    }

}
