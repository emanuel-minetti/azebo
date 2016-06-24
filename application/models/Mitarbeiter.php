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
 *     Copyright 2012-16 Emanuel Minetti (e.minetti (at) posteo.de)
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

    /**
     * Gibt ein Array mit den Mitarbeiter-Items aller Mitarbeiter einer Hochschule
     * zurück. Die Hochschule wird als 'khb', 'hfm' oder 'hfs' übergeben.
     * 
     * @param string $hochschule
     * @return array
     */
    public function getMitarbeiterNachHochschule($hochschule) {

        $gruppenNamen = Zend_Registry::get('gruppen');

        switch ($hochschule) {
            case 'hfm':
                $gruppe = $gruppenNamen->mitglied->hfm;
                break;
            case 'hfs':
                $gruppe = $gruppenNamen->mitglied->hfs;
                break;
            case 'khb':
                $gruppe = $gruppenNamen->mitglied->khb;
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

    public function getHochschuleNachBenutzernamen($benutzername) {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/ldap.ini');
        $options = $config->ldap->physalis->toArray();
        $gruppenNamen = Zend_Registry::get('gruppen');
        $gruppen = array();
        $attributes = array('cn');
        $ldap = new Zend_Ldap($options);
        $users = $ldap->search(
                '(&(objectClass=posixGroup)(memberUid=' . $benutzername . '))', 'OU=Groups,DC=verwaltung,DC=kh-berlin,DC=de', Zend_Ldap::SEARCH_SCOPE_SUB, $attributes);
        foreach ($users as $user) {
            $gruppen[] = $user['cn'][0];
        }
        $hochschule = '';
        foreach ($gruppen as $gruppe) {
            if ($gruppe == $gruppenNamen->mitglied->hfs) {
                $hochschule = 'hfs';
            } else if ($gruppe == $gruppenNamen->mitglied->hfm) {
                $hochschule = 'hfm';
            } else if ($gruppe == $gruppenNamen->mitglied->khb) {
                $hochschule = 'khb';
            }
        }

        return $hochschule;
    }
  
    public function getStudiHKNachBenutzernamen($benutzername) {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/ldap.ini');
        $options = $config->ldap->physalis->toArray();
        $gruppenNamen = Zend_Registry::get('gruppen');
        $gruppen = array();
        $attributes = array('cn');
        $ldap = new Zend_Ldap($options);
        $users = $ldap->search(
                '(&(objectClass=posixGroup)(memberUid=' . $benutzername . '))', 'OU=Groups,DC=verwaltung,DC=kh-berlin,DC=de', Zend_Ldap::SEARCH_SCOPE_SUB, $attributes);
        foreach ($users as $user) {
            $gruppen[] = $user['cn'][0];
        }
        $studiHK = false;
        foreach ($gruppen as $gruppe) {
            if ($gruppe == $gruppenNamen->mitglied->khbstudi) {
                $studiHK = true;
            }
        }

        return $studiHK;
    }
    
    /**
     * Gibt ein Array mit den Benutzernamen aller Mitarbeiter einer Hochschule
     * zurück. Die Hochschule wird als 'khb', 'hfm' oder 'hfs' übergeben.
     * 
     * @param string $hochschule
     * @return array
     */
    public function getBenutzernamenNachHochschule($hochschule) {

        $gruppenNamen = Zend_Registry::get('gruppen');

        switch ($hochschule) {
            case 'hfm':
                $gruppe = $gruppenNamen->mitglied->hfm;
                break;
            case 'hfs':
                $gruppe = $gruppenNamen->mitglied->hfs;
                break;
            case 'khb':
                $gruppe = $gruppenNamen->mitglied->khb;
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
        $benutzer = $ldap->getEntry(
                'uid=' . $benutzername . ',ou=Users,dc=verwaltung,dc=kh-berlin,dc=de');
//        $log = Zend_Registry::get('log');
//        $log->debug('Benutzer: ' . print_r($benutzer, true));
        $vorname = $benutzer['givenname'][0];
        $nachname = $benutzer['sn'][0];
        return $vorname . ' ' . $nachname;
    }

    public function saveMitarbeiter(Azebo_Resource_Mitarbeiter_Item_Interface $mitarbeiter, $daten) {
        $mitarbeiter->benutzername = $daten['benutzername'];
        $mitarbeiter->setUrlaubVorjahr($daten['urlaubVorjahr']);
        $mitarbeiter->setUrlaub($daten['urlaub']);
        $mitarbeiter->beamter = $daten['beamter'];

        // Saldo setzen
        $saldoString = $daten['saldo'];
        $preg = '^(\+|-) (\d{1,3}):(\d{1,2})$';
        $parts = array();
        preg_match("/$preg/", $saldoString, $parts);
        $positiv = $parts[1] == '+' ? true : false;
        $stunden = $parts[2];
        $minuten = $parts[3];
        $saldo = new Azebo_Model_Saldo($stunden, $minuten, $positiv);
        $mitarbeiter->setSaldoUebertrag($saldo);
        if (isset($daten['saldo2007']) && $daten['saldo2007'] != '') {
            $parts = array();
            preg_match("/$preg/", $daten['saldo2007'], $parts);
            $stunden = $parts[2];
            $minuten = $parts[3];
            $saldo2007 = new Azebo_Model_Saldo($stunden, $minuten, true);
            $mitarbeiter->setSaldo2007($saldo2007);
        }

        // KappungGesamt setzen
        $kappungGesamtString = $daten['kappunggesamt'];
        if ($kappungGesamtString !== null & $kappungGesamtString != '') {
            $preg = '^(\d{1,3}):(\d{1,2})$';
            $parts = array();
            preg_match("/$preg/", $kappungGesamtString, $parts);
            $stunden = $parts[1];
            $minuten = $parts[2];
            $kappunggesamt = new Azebo_Model_Saldo($stunden, $minuten, true);
        } else {
            $kappunggesamt = null;
        }
        // nicht setzen, falls sie nicht Standard ist
        $standardGesamt = $mitarbeiter->getKappungGesamtStandard();
        if ($standardGesamt !== null && $kappunggesamt !== null &&
                $kappunggesamt->vergleiche($standardGesamt) == 0) {
            $kappunggesamt = null;
        }
        $mitarbeiter->setKappungGesamt($kappunggesamt);

        // KappungMonat setzen, falls sie nicht Standard ist
        if (isset($daten['kappungmonat'])) {
            $kappungMonatString = $daten['kappungmonat'];
            if ($kappungMonatString !== null & $kappungMonatString != '') {
                $preg = '^(\d{1,3}):(\d{1,2})$';
                $parts = array();
                preg_match("/$preg/", $kappungMonatString, $parts);
                $stunden = $parts[1];
                $minuten = $parts[2];
                $kappungmonat = new Azebo_Model_Saldo($stunden, $minuten, true);
            } else {
                $kappungmonat = null;
            }
        }
        // nicht setzen, falls sie nicht Standard ist
        $standardMonat = $mitarbeiter->getKappungMonatStandard();
        if ($standardMonat !== null && $kappungmonat !== null &&
                $kappungmonat->vergleiche($standardMonat) == 0) {
            $kappungmonat = null;
        }
        $mitarbeiter->setKappungMonat($kappungmonat);

        // In die DB schreiben
        $mitarbeiter->save();
    }

    public function getArbeitsregelNachId($id) {
        $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
        return $arbeitsregelTabelle->getArbeitsregelNachId($id);
    }

    public function getArbeitsregelnNachBenutzername($benutzername) {
        $mitarbeiter = $this->getMitarbeiterNachBenutzername($benutzername);
        $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
        return $arbeitsregelTabelle->getArbeitsregelnNachMitarbeiterId(
                        $mitarbeiter->id);
    }

    public function saveArbeitsregel($daten) {
        // hole die Arbeitesregel, je nachdem einen neue oder die zu bearbeitende
        $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
        if ($daten['id'] == 0) {
            $arbeitsregel = $arbeitsregelTabelle->createRow();
        } else {
            $arbeitsregel = $arbeitsregelTabelle->getArbeitsregelNachId(
                    $daten['id']);
        }
        
        // hole den Mitarbeiter
        $mitarbeiter = $this->getMitarbeiterNachBenutzername(
                $daten['benutzername']);
        
        // setze die Daten
        $arbeitsregel->mitarbeiter_id = $mitarbeiter->id;
        $arbeitsregel->setVon($daten['von']);
        $arbeitsregel->setBis($daten['bis']);
        $arbeitsregel->setSoll($daten['soll']);
        $arbeitsregel->wochentag = $daten['wochentag'];
        $arbeitsregel->kalenderwoche = $daten['kw'];
        $arbeitsregel->setRahmenAnfang($daten['rahmenAnfang']);
        $arbeitsregel->setKernAnfang($daten['kernAnfang']);
        $arbeitsregel->setKernEnde($daten['kernEnde']);
        $arbeitsregel->setRahmenEnde($daten['rahmenEnde']);
        $arbeitsregel->setOhneKern($daten['ohneKern']);
        
        // falls dies die erste Regel ist,
        // setze 'uebertragenBis' in der Mitarbeiter-Tabelle
        if(count(
                $arbeitsregelTabelle->getArbeitsregelnNachMitarbeiterId(
                        $mitarbeiter->id)) == 0) {
            $uebertragenbis = new Zend_Date($daten['von']);
            $uebertragenbis->subYear(1);
            $uebertragenbis->setDay('31');
            $uebertragenbis->setMonth('12');
            $mitarbeiter->setUebertragenbis($uebertragenbis);
            $mitarbeiter->save();
        } 
        
        // schreibe die Regel in die DB
        $arbeitsregel->save();
        
    }

    public function deleteArbeitsregel($id) {
        $arbeitsregel = $this->getArbeitsregelNachId($id);
        $arbeitsregel->delete();
    }

    public function getAbgeschlossenAbgelegtNachMonatUndHochschule($monat, $hochschule) {
        $monatsTabelle = new Azebo_Resource_Arbeitsmonat();
        $arbeitsmonate = $monatsTabelle->getArbeitsmonateNachMonat($monat);
        $benutzernamen = $this->getBenutzernamenNachHochschule($hochschule);
        $erg = array();
        foreach ($arbeitsmonate as $arbeitsmonat) {
            $mitarbeiterId = $arbeitsmonat->mitarbeiter_id;
            $mitarbeiter = $this->getMitarbeiterNachId($mitarbeiterId);
            if(in_array($mitarbeiter->benutzername, $benutzernamen)) {
                $erg[] = $arbeitsmonat;
            }
        }

        $abgeschlossen = count($erg);
        $abgelegt = 0;
        foreach ($erg as $monat) {
            if ($monat->abgelegt == 'ja') {
                $abgelegt++;
            }
        }
        return array(
            'abgeschlossen' => $abgeschlossen,
            'abgelegt' => $abgelegt,
        );
    }

}
