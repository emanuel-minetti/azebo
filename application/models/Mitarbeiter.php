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
 * Description of Mitarbeiter
 *
 * @author Emanuel Minetti
 */
class Azebo_Model_Mitarbeiter extends AzeboLib_Model_Abstract {

    public function getMitarbeiterNachId($id) {
        $id = (int) $id;
        return $this->getResource('Mitarbeiter')->getMitarbeiterNachId($id);
    }

    /**
     * @param string $benutzername
     * @return Azebo_Resource_Mitarbeiter_Item_Interface
     */
    public function getMitarbeiterNachBenutzername($benutzername) {
        return $this->getResource('Mitarbeiter')
                        ->getMitarbeiterNachBenutzername($benutzername);
    }

    public function getArbeitstageNachMonatUndMitarbeiter(
    Zend_Date $monat, Azebo_Resource_Mitarbeiter_Item_Interface $mitarbeiter) {
        return $this->getResource('Arbeitstag')
                        ->getArbeitstageNachMonatUndMitarbeiterId(
                                $monat, $mitarbeiter->id);
    }

    public function getMitarbeiterNachHochschule($hochschule) {
        switch ($hochschule) {
            case 'hfm':
                $gruppe = 'HFM-Mitglied';
                break;
            case 'hfs':
                $gruppe = 'HFS-Mitglied';
                break;
            case 'khb':
                $gruppe = 'KHB-Mitglied';
                break;
        }
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/ldap.ini');
        $options = $config->ldap->physalis->toArray();
        $ldap = new Zend_Ldap($options);
        $ldap->bind();
        $entry = $ldap->search('(&(objectClass=posixGroup)(cn=' . $gruppe . '))', 'OU=Groups,DC=verwaltung,DC=kh-berlin,DC=de', Zend_Ldap::SEARCH_SCOPE_SUB);
        foreach ($entry as $group) {
            $membersArray[] = $group['memberuid'];
        }
        $mitglieder = $membersArray[0];

        //$log = Zend_Registry::get('log');

        $hsMitarbeiter = array();
        foreach ($mitglieder as $mitglied) {
            $hsMitarbeiter[] = $this->getMitarbeiterNachBenutzername($mitglied);
        }

        $erg = array();
        foreach ($hsMitarbeiter as $mitarbeiter) {
            if ($mitarbeiter !== null) {
                $erg[] = $mitarbeiter;
                //$log->debug('Mitglied der ' . $hochschule . ': ' . $mitarbeiter->getName());
            }
        }
        return $erg;
    }
    
    public function getBenutzernamenNachHochschule($hochschule) {
        switch ($hochschule) {
            case 'hfm':
                $gruppe = 'HFM-Mitglied';
                break;
            case 'hfs':
                $gruppe = 'HFS-Mitglied';
                break;
            case 'khb':
                $gruppe = 'KHB-Mitglied';
                break;
        }
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/ldap.ini');
        $options = $config->ldap->physalis->toArray();
        $ldap = new Zend_Ldap($options);
        $ldap->bind();
        $entry = $ldap->search('(&(objectClass=posixGroup)(cn=' . $gruppe . '))', 'OU=Groups,DC=verwaltung,DC=kh-berlin,DC=de', Zend_Ldap::SEARCH_SCOPE_SUB);
        foreach ($entry as $group) {
            $membersArray[] = $group['memberuid'];
        }
        $mitglieder = $membersArray[0];
        
        return $mitglieder;
    }
    
    public function getNameNachBenutzername($benutzername) {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/ldap.ini');
        $options = $config->ldap->physalis->toArray();
        $ldap = new Zend_Ldap($options);
        $ldap->bind();
        $benutzer = $ldap->getEntry('uid=' . $benutzername . ',ou=Users,dc=verwaltung,dc=kh-berlin,dc=de');
//        $log = Zend_Registry::get('log');
//        $log->debug('Benutzer: ' . print_r($benutzer, true));
        $vorname = $benutzer['givenname'][0];
        $nachname = $benutzer['sn'][0];
        return $vorname . ' ' . $nachname;
    }
    
    public function saveMitarbeiter(Azebo_Resource_Mitarbeiter_Item_Interface $mitarbeiter, $daten) {
        $mitarbeiter->benutzername = $daten['benutzername'];
        $mitarbeiter->urlaub = $daten['urlaub'];
        $mitarbeiter->beamter = $daten['beamter'];
        $saldoString = $daten['saldo'];
        $preg = '^(\+|-) (\d{1,3}):(\d{1,2})$';
        preg_match("/$preg/", $saldoString, $parts);
        $positiv = $parts[1] == '+' ? true : false;
        $stunden = $parts[2];
        $minuten = $parts[3];
        $saldo = new Azebo_Model_Saldo($stunden, $minuten, $positiv);
        $mitarbeiter->setSaldoUebertrag($saldo);
        $mitarbeiter->save();
    }
    
    public function getArbeitsregelNachId($id) {
        $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
        return $arbeitsregelTabelle->getArbeitsregelNachId($id);
    }
    
    public function getArbeitsregelnNachBenutzername($benutzername) {
        $mitarbeiter = $this->getMitarbeiterNachBenutzername($benutzername);
        $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
        return $arbeitsregelTabelle->getArbeitsregelnNachMitarbeiterId($mitarbeiter->id);
    }
    
    public function saveArbeitsregel($daten) {
        $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
        if($daten['id'] == 0) {
            $arbeitsregel = $arbeitsregelTabelle->createRow();
        } else {
            $arbeitsregel = $arbeitsregelTabelle->getArbeitsregelNachId($daten['id']);
        }
        $mitarbeiter = $this->getMitarbeiterNachBenutzername($daten['benutzername']);
        $arbeitsregel->mitarbeiter_id = $mitarbeiter->id;
        $arbeitsregel->setVon($daten['von']);
        $arbeitsregel->setBis($daten['bis']);
        $arbeitsregel->setSoll($daten['soll']);
        $arbeitsregel->wochentag = $daten['wochentag'];
        $arbeitsregel->kalenderwoche = $daten['kw'];
        
        $arbeitsregel->save();
    }
    
    public function deleteArbeitsregel($id) {
        $arbeitsregel = $this->getArbeitsregelNachId($id);
        $arbeitsregel->delete();
    }
    
    public function getAbgeschlossenNachMonat($monat) {
        $monatsTabelle = new Azebo_Resource_Arbeitsmonat();
        
    }

}
