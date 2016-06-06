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
 * Description of Item
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Mitarbeiter_Item extends AzeboLib_Model_Resource_Db_Table_Row_Abstract implements Azebo_Resource_Mitarbeiter_Item_Interface {

    private $_vorname = null;
    private $_nachname = null;
    private $_rolle = null;
    private $_hochschule = null;
    private $_studiHK = null;
    private $_zeiten = null;
    private $_farben = null;

    public function getName() {

        if ($this->_vorname === null || $this->_nachname === null) {

            $config = new Zend_Config_Ini(
                    APPLICATION_PATH . '/configs/ldap.ini');
            $options = $config->ldap->physalis->toArray();
            $ldap = new Zend_Ldap($options);
            $ldap->bind();
            $benutzer = $ldap->getEntry(
                    'uid=' . $this->_row->benutzername .
                    ',ou=Users,dc=verwaltung,dc=kh-berlin,dc=de');
            $this->_vorname = $benutzer['givenname'][0];
            $this->_nachname = $benutzer['sn'][0];
        }
        return $this->_vorname . ' ' . $this->_nachname;
    }

    public function getSortierName() {
        if ($this->_vorname === null || $this->_nachname === null) {
            $this->getName();
        }
        return $this->_nachname . ', ' . $this->_vorname;
    }

    /**
     * Gibt einen Arbeitstag für den angegbenen Tag zurück.
     * Falls ein Arbeitstag in der DB für den angegebenen Tag existiert,
     * wird dieser zurückgegeben, ansonsten ein frisch initialisierter
     * Arbeitstag.
     * 
     * @param Zend_Date $tag
     * @return Azebo_Resource_Arbeitstag_Item_Interface 
     */
    public function getArbeitstagNachTag(Zend_Date $tag) {
        $arbeitstagTabelle = new Azebo_Resource_Arbeitstag();
        return $arbeitstagTabelle->getArbeitstagNachTagUndMitarbeiterId(
                        $tag, $this->id);
    }

    /**
     * Gibt ein Array von Arbeitstagen für den angegebenen Monat zurück.
     * Falls ein Arbeitstag in der DB existiert wird dieser eingefügt, falls
     * nicht wird ein frisch intialisierter Arbeitstag eingefügt.
     * 
     * @param Zend_Date $monat
     * @return array 
     */
    public function getArbeitstageNachMonat(Zend_Date $monat) {
        $arbeitstagTabelle = new Azebo_Resource_Arbeitstag();
        return $arbeitstagTabelle->getArbeitstageNachMonatUndMitarbeiterId(
                        $monat, $this->id);
    }

    /**
     * Setzt die Rolle eines Mitarbeiters. Die Rolle bestimmt, welche Seiten
     * ein Mitarbeiter angezeigt bekommen darf und welche Aktionen er ausführen
     * darf. Wird ein Mitarbeiter von einem anderen vertreten, so soll er nicht
     * dessen Rolle erben.
     * 
     * Als Parameter werden die LDAP-Gruppen übergeben, denen der Mitarbeiter
     * angehört. Wird der Mitarbeiter vertreten so wird diese Methode ohne
     * Parameter aufgerufen und der Mitarbeiter erhält nur die Rolle
     * 'mitarbeiter'.
     * 
     * 
     * @param array $gruppen 
     */
    public function setRolle(array $gruppen = null) {

        if ($gruppen !== null) {
            $gruppenNamen = Zend_Registry::get('gruppen');

            foreach ($gruppen as $gruppe) {
                if ($gruppe == $gruppenNamen->buero->hfs ||
                        $gruppe == $gruppenNamen->buero->hfm ||
                        $gruppe == $gruppenNamen->buero->khb) {
                    $this->_rolle = 'bueroleitung';
                }
                if ($gruppe == $gruppenNamen->scit) {
                    $this->_rolle = 'scit';
                }
            }
            if ($this->_rolle === null) {
                $this->_rolle = 'mitarbeiter';
            }
        } else {
            $this->_rolle = 'mitarbeiter';
        }
    }

    public function getRolle() {
        return $this->_rolle;
    }

    public function setHochschule(array $gruppen) {

        $gruppenNamen = Zend_Registry::get('gruppen');

        foreach ($gruppen as $gruppe) {
            if ($gruppe == $gruppenNamen->mitglied->hfs) {
                $this->_hochschule = 'hfs';
            } else if ($gruppe == $gruppenNamen->mitglied->hfm) {
                $this->_hochschule = 'hfm';
            } else if ($gruppe == $gruppenNamen->mitglied->khb) {
                $this->_hochschule = 'khb';
            }
        }
    }

    public function getHochschule() {
        if ($this->_hochschule === null) {
            $model = new Azebo_Model_Mitarbeiter();
            $this->_hochschule = $model->
                    getHochschuleNachBenutzernamen($this->benutzername);
        }
        return $this->_hochschule;
    }
    
    /**
     * Setze StudiHK für diejenigen Mitglieder der KHB, die
     * 'Stdentische Hilfskräfte sind.
     * 
     * @param array $gruppen die LDAP-Gruppen zu denen der Mitarbeiter gehört.
     */
    public function setStudiHK(array $gruppen) {
        $gruppenNamen = Zend_Registry::get('gruppen');
        
        $this->_studiHK = false;
        foreach ($gruppen as $gruppe) {
            if ($gruppe == $gruppenNamen->mitglied->khbstudi) {
                $this->_studiHK = true;
            }
        }
    }
    
    /**
     * Gibt zurück, ob der Mitarbeiter 'Studentische Hilfskraft ist oder nicht.
     * 
     * @return boolean Der Wahrheitswert, ob der Mitarbeiter
     *                  'Stdentische Hilfskraft ist oder nicht.
     */
    public function getStudiHK() {
        if ($this->_studiHK === null) {
            $model = new Azebo_Model_Mitarbeiter();
            $this->_studiHK = $model->
                    getStudiHKNachBenutzernamen($this->benutzername);
        }
        return $this->_studiHK;
    }

    //TODO Die Namen der Hochschulen in die Konfig-Datei auslagern!
    //TODO Kommentieren!
    public function getHochschulString() {
        $hochschulString = $this->getHochschule();
        switch ($hochschulString) {
            case 'hfm':
                $hochschulString = 'Hochschule für Musik "Hanns Eisler"';
                break;
            case 'hfs':
                $hochschulString = 'Hochschule für Schauspielkunst \'Ernst Busch\'';
                break;
            case 'khb':
                $hochschulString = 'weißensee kunsthochschule berlin';
                break;

            default:
                break;
        }
        return $hochschulString;
    }

    public function saveArbeitstag(Zend_Date $tag, array $daten) {
        $arbeitstagTabelle = new Azebo_Resource_Arbeitstag();
        $arbeitstagTabelle->saveArbeitstag($tag, $this->id, $daten);
    }

    public function saveArbeitsmonat(Zend_Date $monat) {
        $saldo = $this->getSaldoGesamt($monat, true);
        $urlaubGesamt = $this->getUrlaubGesamt($monat);
        $azv = $this->getAzvTageNachMonat($monat);

        $arbeitsmonatTabelle = new Azebo_Resource_Arbeitsmonat();
        $arbeitsmonatTabelle->saveArbeitsmonat(
                $this->id, $monat, $saldo, $urlaubGesamt['diff'], $urlaubGesamt['diffVorjahr'], $azv);
    }

    public function setNachname($nachname) {
        $this->_nachname = $nachname;
    }

    public function setVorname($vorname) {
        $this->_vorname = $vorname;
    }

    public function getBeamter() {
        return $this->getRow()->beamter == 'ja' ? true : false;
    }

    /**
     *
     * @return Azebo_Model_Saldo 
     */
    public function getSaldouebertrag() {
        $stunden = $this->getRow()->saldouebertragstunden;
        $minuten = $this->getRow()->saldouebertragminuten;
        $positiv = $this->getRow()->saldouebertragpositiv == 'ja' ?
                true : false;
        if ($this->getRow()->saldo2007stunden === null) {
            $uebertrag = new Azebo_Model_Saldo($stunden, $minuten, $positiv);
        } else {
            $restStunden = $this->getRow()->saldo2007stunden;
            $restMinuten = $this->getRow()->saldo2007minuten;
            $uebertrag = new Azebo_Model_Saldo(
                    $stunden, $minuten, $positiv, true, $restStunden, $restMinuten);
        }
        return $uebertrag;
    }

    /**
     * Gibt die Arbeitsmonate des Mitarbeiters zurück. Falls $filter == true
     * ist, werden nur die nicht übertragenen Monate zurückgegeben.
     * 
     * @param boolean $filter gibt an, ob übertragene Monate gefiltert werden
     * sollen 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getArbeitsmonate($filter = true) {
        $monatsTabelle = new Azebo_Resource_Arbeitsmonat();
        return $monatsTabelle->getArbeitsmonateNachMitarbeiterId($this->id, $filter);
    }

    /**
     * Gibt die Arbeitsmonate des Mitarbeiters bis zu einem
     * übergebenen $monat zurück. Ist $filter == true (Default), werden nur
     * die nicht übertragenen Monate zurückgegeben.
     * 
     * @param Zend_Date $monat der Monat bis zu dem die Arbeitsmonate geholt
     * werden. 
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function getArbeitsmonateBis(Zend_Date $monat, $filter = true) {
        $monatsTabelle = new Azebo_Resource_Arbeitsmonat();
        return $monatsTabelle->getArbeitsmonateNachMitarbeiterIdUndBis(
                        $this->id, $monat, $filter);
    }

    /**
     * Gibt die nicht-übertragenen und nicht-abgeschlossenen Monate bis $monat
     * zurück.
     * 
     * @param Zend_Date $monat der Monat bis zu dem die Fehlmonate gesucht
     * werden.
     * @return array ein Array von Zend_Date, die Monate, die weder
     * abgeschlossen noch übertragen sind.
     */
    public function getFehlmonateBis(Zend_Date $monat) {
        $result = array();
        //betrachte die Monate seit Arbeitsbeginn oder *nach* dem letzten
        //Übertrag, je nachdem was später liegt
        $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
        $arbeitsbeginn = $arbeitsregelTabelle->
                getArbeitsbeginnNachMitarbeiterId($this->id);
        $uebertragenBis = $this->getUebertragenBis()->addMonth(1);
        $tempMonat = $arbeitsbeginn->compareDate($uebertragenBis) == 1 ?
                $arbeitsbeginn : $uebertragenBis;

        //in $result alle *nicht-übertragenen* Monate (seit Arbeitsbeginn) bis
        //$monat abspeichern! 
        while ($tempMonat->compare($monat) == -1) {
            $result[] = new Zend_Date($tempMonat);
            $tempMonat->addMonth(1);
        }

        //die *abgeschlossenen* Monate aus $result entfernen!
        $monateAbgeschlossen = $this->getArbeitsmonateBis($monat);
        for ($i = 0; $i < count($result); $i++) {
            for ($j = 0; $j < count($monateAbgeschlossen); $j++) {
                if ($result[$i]->compareMonth(
                                $monateAbgeschlossen[$j]->getMonat()) == 0 &&
                        $result[$i]->compareYear(
                                $monateAbgeschlossen[$j]->getMonat()) == 0) {
                    array_splice($result, $i, 1);
                }
            }
        }

        return $result;
    }

    /**
     *
     * @return Azebo_Model_Saldo 
     */
    public function getSaldoBisher(Zend_Date $bis, $anzeigen = false) {
        
        $uebertragenBis = $this->getUebertragenBis();
        if ($bis->compareYear($uebertragenBis) == 1) {
            // falls $bis nach dem letzten Übertrag liegt berechne das Saldo wie
            // gewöhnlich
            $saldo = $this->getSaldouebertrag();
            $monate = $this->getArbeitsmonateBis($bis);
        } else {
            // $bis liegt vor dem letzten Übertrag, also berechne das Saldo
            // ausgehend von den Daten des Vorjahres und berücksichtige auch
            // Monate die schon übertragen sind
            $saldo = $this->getVorjahr()->getSaldouebertrag();
            $monate = $this->getArbeitsmonateBis($bis, false);
        }
        
        // Prüfe ob der Mitarbeiter eine StudiHK ist. Falls ja dürfen nur die 
        // Monate bis zum letzten Semesteranfang berücksichtigt werden.
        if ($this->getStudiHK()) {
            // Hole die Semesteranfänge des Jahres
            $zeiten = $this->_getZeiten();
            $jahr = 'jahr' . $bis->toString('yyyy');
            $soSeAnfang = $zeiten->semesteranfang->sommer->$jahr;
            $wiSeAnfang = $zeiten->semesteranfang->winter->$jahr;
            $soSeAnfang = new Zend_Date($soSeAnfang, 'dd.MM.yyyy');
            $wiSeAnfang = new Zend_Date($wiSeAnfang, 'dd.MM.yyyy');
            // Hole Neujahr
            $neujahr = '1.1.' . $bis->toString('yyyy');
            $neujahr = new Zend_Date($neujahr);
            // Besorge ein 'Null'-Saldo
            $nullsaldo = new Azebo_Model_Saldo(0, 0, true);
            // Wandle '$monate' in ein array um
            $monateArray = array();
            foreach ($monate as $monat) {
                array_push($monateArray, $monat);
            }
            $monate = $monateArray;                
            if ($bis->compareMonth($neujahr) !== -1 &&
                    $bis->compareMonth($soSeAnfang) === -1) {
                // '$bis' liegt zwischen Januar und März: berücksichtige alle Monate
                // wie bisher, und beücksichtige den Übertrag. Im Klartext:
                // tue nichts
            } else if ($bis->compareMonth($soSeAnfang) !== -1 &&
                    $bis->compareMonth($wiSeAnfang) === -1) {
                // '$bis' liegt zwischen April und September: berücksichtige alle Monate
                // seit dem April und keinen Übertrag
                $saldo = $nullsaldo;
                while (count($monate) > 0 &&
                        $monate[0]->
                        getMonat()->compareMonth($soSeAnfang) === -1) {
                    array_shift($monate);
                }
            } else {
                // '$bis' liegt zwischen Oktober und Neujahr: berücksichtige alle Monate
                // seit dem Oktober und keinen Übertrag
                $saldo = $nullsaldo;
                while (count($monate) > 0 &&
                        $monate[0]->
                        getMonat()->compareMonth($wiSeAnfang) === -1) {
                    array_shift($monate);
                }
            }
        }
        
        foreach ($monate as $monat) {
            $monatsSaldo = $monat->getSaldo();
            $saldo->add($monatsSaldo, true);
        }
        if ($anzeigen && count($monate) != 0) {
            $saldoRest = $monate[count($monate) - 1]->getSaldo()->getRest();
            if ($saldoRest) {
                $saldo = new Azebo_Model_Saldo(
                        $saldo->getStunden(), $saldo->getMinuten(), $saldo->getPositiv(), $saldoRest, $monate[count($monate) - 1]->
                                getSaldo()->getRestStunden(), $monate[count($monate) - 1]->
                                getSaldo()->getRestMinuten());
            }
        }
        return $saldo;
    }

    public function getUrlaubBisher(Zend_Date $bis) {
        $uebertragenBis = $this->getUebertragenBis();
        if ($bis->compareYear($uebertragenBis) == 1) {
            // falls $bis nach dem letzten Übertrag liegt berechne das Saldo wie
            // gewöhnlich
            $urlaub = $this->getUrlaub();
            $monate = $this->getArbeitsmonateBis($bis);
        } else {
            // $bis liegt vor dem letzten Übertrag, also berechne das Saldo
            // ausgehend von den Daten des Vorjahres und berücksichtige auch
            // Monate die schon übertragen sind
            $urlaub = $this->getVorjahr()->getUrlaub();
            $monate = $this->getArbeitsmonateBis($bis, false);
        }
        foreach ($monate as $monat) {
            $urlaub -= $monat->urlaub;
        }
        return $urlaub;
    }

    public function getUrlaubVorjahrBisher(Zend_Date $bis) {
        $zeiten = $this->_getZeiten();
        $vorjahrRestBis = $zeiten->urlaub->resturlaubbis;
        $vorjahrRestBisDate = new Zend_Date($vorjahrRestBis, 'dd.MM.');
        $uebertragenBis = $this->getUebertragenBis();
        if ($bis->compareMonth($vorjahrRestBisDate) != 1) {
            // Vorjahresurlaub noch gültig
            if ($bis->compareYear($uebertragenBis) == 1) {
                // falls $bis nach dem letzten Übertrag liegt berechne das Saldo wie
                // gewöhnlich
                $urlaub = $this->getUrlaubVorjahr();
                $monate = $this->getArbeitsmonateBis($bis);
            } else {
                // $bis liegt vor dem letzten Übertrag, also berechne das Saldo
                // ausgehend von den Daten des Vorjahres und berücksichtige auch
                // Monate die schon übertragen sind
                $urlaub = $this->getVorjahr()->getUrlaubVorjahr();
                $monate = $this->getArbeitsmonateBis($bis, false);
            }
            foreach ($monate as $monat) {
                $urlaub -= $monat->urlaubvorjahr;
            }
        } else {
            // Vorjahresurlaub nicht mehr gültig
            $urlaub = 0;
        }
        return $urlaub;
    }

    /**
     * Gibt das Saldo des laufenden Monats (also des im Parameter $monat
     * übergebenen Monats) zurück. Ist $vorlaeufig == true, so werden nur die
     * Tage berücksichtigt, bei denen Beginn und Ende bzw. eine Dienstbefreiung
     * eingetragen sind.
     * 
     * @param Zend_Date $monat
     * @param boolean $vorlaeufig
     * @return Azebo_Model_Saldo 
     */
    public function getSaldo(Zend_Date $monat, $vorlaeufig = false) {
        $arbeitstage = $this->getArbeitstageNachMonat($monat);
        $saldoBisher = $this->getSaldoBisher($monat);
        if ($saldoBisher->getRest()) {
            $saldo = new Azebo_Model_Saldo(0, 0, true, true, $saldoBisher->getRestStunden(), $saldoBisher->getRestMinuten());
        } else {
            $saldo = new Azebo_Model_Saldo(0, 0, true);
        }

        foreach ($arbeitstage as $arbeitstag) {
            if ($vorlaeufig) {
                if (!($arbeitstag->getBeginn() === null &&
                        $arbeitstag->getEnde() === null) ||
                        $arbeitstag->befreiung != 'keine') {
                    $tagesSaldo = $arbeitstag->getSaldo();
                    $saldo->add($tagesSaldo);
                }
            } else {
                $tagesSaldo = $arbeitstag->getSaldo();
                $saldo->add($tagesSaldo);
            }
        }

        return $saldo;
    }

    /**
     * Gibt das Gesamtsaldo für einen Monat zurück. Ist $differenz == true,
     * so wird die in der Monatstabelle einzutragende Differenz zurückgegeben.
     * 
     * @param Zend_Date $monat der Monat
     * @param type $differenz
     * @return \Azebo_Model_Saldo 
     */
    public function getSaldoGesamt(Zend_Date $monat, $differenz = false) {
        $saldoBisher = $this->getSaldoBisher($monat, true);
        $saldo = $this->getSaldo($monat);

        // die Monats-Kappungs-Grenze anwenden
        $kappungMonat = $this->getKappungMonat();
        if ($kappungMonat !== null && $saldo->getPositiv() &&
                $saldo->vergleiche($kappungMonat) == 1) {
            $saldo = $kappungMonat;
        }

        $saldoGesamt = Azebo_Model_Saldo::copy($saldoBisher);
        $saldoGesamt->add($saldo, true);

        // die Gesamt-Kappungs-Grenze anwenden
        $kappungGesamt = $this->getKappungGesamt();
        if ($kappungGesamt !== null && $saldoGesamt->getPositiv() &&
                $saldoGesamt->vergleiche($kappungGesamt) == 1) {
            // $saldoGesamt darf nicht einfach überschrieben werden,
            // sonst geht u.U. der Rest 2007 bei der HfM verloren!
            $saldoGesamt = new Azebo_Model_Saldo($kappungGesamt->getStunden(), $kappungGesamt->getMinuten(), true, $saldoGesamt->getRest(), $saldoGesamt->getRestStunden(), $saldoGesamt->getRestMinuten());
        }

        if ($differenz) {
            $saldoBisher = new Azebo_Model_Saldo($saldoBisher->getStunden(), $saldoBisher->getMinuten(), !$saldoBisher->getPositiv());
            $saldoGesamt->add($saldoBisher);
        }

        return $saldoGesamt;
    }

    public function getUrlaubNachMonat(Zend_Date $monat) {
        $arbeitstage = $this->getArbeitstageNachMonat($monat);
        $urlaub = 0;

        foreach ($arbeitstage as $arbeitstag) {
            if ($arbeitstag->befreiung == 'urlaub') {
                $urlaub++;
            }
        }
        return $urlaub;
    }

    public function getArbeitsmonateNachJahr(Zend_Date $jahr) {
        $monatsTabelle = new Azebo_Resource_Arbeitsmonat();
        return $monatsTabelle->getArbeitsmonateNachJahrUndMitarbeiterId(
                        $jahr, $this->id);
    }

    public function getAbgelegtBis() {
        $heute = new Zend_Date();
        $arbeitsmonate = $this->getArbeitsmonateNachJahr($heute);
        $letzter = null;
        for ($index = count($arbeitsmonate) - 1; $index >= 0; $index--) {
            if ($arbeitsmonate[$index]->getSaldo()->getStunden() !== null &&
                    $arbeitsmonate[$index]->abgelegt == 'ja') {
                $letzter = $arbeitsmonate[$index];
                break;
            }
        }
        if ($letzter !== null) {
            return $letzter->getMonat()->toString('MMMM yyyy');
        } else {
            return null;
        }
    }

    public function getAbgeschlossenBis() {
        $arbeitsmonate = $this->getArbeitsmonate(false);
        if (count($arbeitsmonate) !== 0) {
            return $arbeitsmonate[count($arbeitsmonate) - 1]->getMonat()->toString('MMMM yyyy');
        }
    }

    public function setSaldoUebertrag(Azebo_Model_Saldo $saldo) {
        $this->_row->saldouebertragstunden = $saldo->getStunden();
        $this->_row->saldouebertragminuten = $saldo->getMinuten();
        $this->_row->saldouebertragpositiv = $saldo->getPositiv() ? 'ja' : 'nein';
    }

    public function getArbeitsregeln() {
        $regelTabelle = new Azebo_Resource_Arbeitsregel();
        return $regelTabelle->getArbeitsregelnNachMitarbeiterId($this->id);
    }

    public function getArbeitsmonat(Zend_Date $monat) {
        $monatTabelle = new Azebo_Resource_Arbeitsmonat();
        return $monatTabelle->
                        getArbeitsmonatNachMitabeiterIdUndMonat(
                                $this->id, $monat);
    }

    public function deleteArbeitsmonat(Zend_Date $monat) {
        $monatTabelle = new Azebo_Resource_Arbeitsmonat();
        $arbeitsmonat = $monatTabelle->
                getArbeitsmonatNachMitabeiterIdUndMonat($this->id, $monat);
        $arbeitsmonat->delete();
    }

    public function arbeitsmonatAblegen(Zend_Date $monat) {
        $monatTabelle = new Azebo_Resource_Arbeitsmonat();
        $arbeitsmonat = $monatTabelle->
                getArbeitsmonatNachMitabeiterIdUndMonat($this->id, $monat);
        $arbeitsmonat->abgelegt = 'ja';
        $arbeitsmonat->save();
    }

    public function setSaldo2007($saldo) {
        if ($saldo !== null) {
            $this->_row->saldo2007stunden = $saldo->getStunden();
            $this->_row->saldo2007minuten = $saldo->getMinuten();
        } else {
            $this->_row->saldo2007stunden = null;
            $this->_row->saldo2007minuten = null;
        }
    }

    public function getUrlaub() {
        return $this->_row->urlaub;
    }

    public function getUrlaubVorjahr() {
        return $this->_row->urlaubvorjahr;
    }

    public function setUrlaub($urlaub) {
        $this->_row->urlaub = $urlaub;
    }

    public function setUrlaubVorjahr($urlaub) {
        $this->_row->urlaubvorjahr = $urlaub;
    }

    /**
     * Berechnet den Resturlaub inklusive des als Parameter übergebenen Monats.
     * Zurückgegeben wird ein Array mit den Schlüsseln 'rest' und 'vorjahr' für
     * den Resturlaub des laufenden und des vorangegangenen Jahres, sowie den
     * Schlüsseln 'diff' und 'diffVorjahr' für die DB-Eintäge bei 'urlaub' und
     * 'urlaubvorjahr'.
     * 
     * Falls der im Monat $monat eingetragene Urlaub den gesamten Resturlaub
     * überschreitet, wird für 'rest' ein negativer Wert zurückgegeben! Es ist 
     * Aufgabe des entsprechenden Validators (also z.B. Azebo_Validate_Urlaub)
     * dafür zu sorgen, dass ein solcher Monat nicht abgeschlossen werden kann!
     * 
     * @param Zend_Date $monat
     * @return array
     */
    public function getUrlaubGesamt(Zend_Date $monat) {
        $urlaub = $this->getUrlaubBisher($monat);
        $urlaubVorjahr = $this->getUrlaubVorjahrBisher($monat);
        $urlaubMonat = $this->getUrlaubNachMonat($monat);
        $diff = 0;
        $diffVorjahr = 0;
        $gesamt = array();
        if ($urlaubMonat != 0) {
            // diesen Monat wurde Urlaub genommen, also berechne neue Restwerte
            if ($urlaubVorjahr >= $urlaubMonat) {
                // der Resturlau des Vorjahres übersteigt den genommenen Urlaub,
                // also ziehe ihn vom Vorjahresrest ab.
                $urlaubVorjahr -= $urlaubMonat;
                $diffVorjahr = $urlaubMonat;
                $diff = 0;
            } else {
                // diesen Monat wurde mehr Urlaub genommen, als Rest vom Vorjahr
                // vorhanden ist, also passe Resturlaub von diesem und vom
                // Vorjahr an. Der diesjährige Rest kann auch negativ werden!
                $ueberschuss = $urlaubMonat - $urlaubVorjahr;
                $diffVorjahr = $urlaubVorjahr;
                $urlaubVorjahr = 0;
                $urlaub -= $ueberschuss;
                $diff = $ueberschuss;
            }
        }
        // gib die (evtl. angepassten) Werte zurück
        $gesamt['rest'] = $urlaub;
        $gesamt['vorjahr'] = $urlaubVorjahr;
        $gesamt['diff'] = $diff;
        $gesamt['diffVorjahr'] = $diffVorjahr;
        return $gesamt;
    }

    private function _getZeiten() {
        if ($this->_zeiten === null) {
            $ns = new Zend_Session_Namespace();
            $this->_zeiten = $ns->zeiten;
        }
        return $this->_zeiten;
    }

    public function getKappungGesamt() {
        $stunden = $this->_row->kappungtotalstunden;
        $minuten = $this->_row->kappungtotalminuten;
        if ($stunden === null && $minuten === null) {
            // keine Kappungsgrenze in der Tabelle, also Standard-Kappung
            $zeiten = $this->_getZeiten();
            $stunden = $zeiten->kappung->gesamt->stunden;
            $minuten = $zeiten->kappung->gesamt->minuten;
        }
        if ($stunden != -1) {
            $kappung = new Azebo_Model_Saldo($stunden, $minuten, true);
        } else {
            // keine Standard-Kappung, also gib 'null' zurück
            $kappung = null;
        }
        return $kappung;
    }

    public function getKappungMonat() {
        $stunden = $this->_row->kappungmonatstunden;
        $minuten = $this->_row->kappungmonatminuten;
        if ($stunden === null && $minuten === null) {
            // keine Kappungsgrenze in der Tabelle, also Standard-Kappung
            $zeiten = $this->_getZeiten();
            $stunden = $zeiten->kappung->monat->stunden;
            $minuten = $zeiten->kappung->monat->minuten;
        }
        if ($stunden != -1) {
            $kappung = new Azebo_Model_Saldo($stunden, $minuten, true);
        } else {
            // keine Standard-Kappung, also gib 'null' zurück
            $kappung = null;
        }
        return $kappung;
    }

    public function setKappungGesamt($kappung) {
        if ($kappung === null) {
            $this->_row->kappungtotalstunden = null;
            $this->_row->kappungtotalminuten = null;
        } else {
            $this->_row->kappungtotalstunden = $kappung->getStunden();
            $this->_row->kappungtotalminuten = $kappung->getMinuten();
        }
    }

    public function setKappungMonat($kappung) {
        if ($kappung === null) {
            $this->_row->kappungmonatstunden = null;
            $this->_row->kappungmonatminuten = null;
        } else {
            $this->_row->kappungmonatstunden = $kappung->getStunden();
            $this->_row->kappungmonatminuten = $kappung->getMinuten();
        }
    }

    public function getKappungGesamtStandard() {
        $zeiten = $this->_getZeiten();
        $stunden = $zeiten->kappung->gesamt->stunden;
        $minuten = $zeiten->kappung->gesamt->minuten;
        if ($stunden != -1) {
            $kappung = new Azebo_Model_Saldo($stunden, $minuten, true);
        } else {
            $kappung = null;
        }
        return $kappung;
    }

    public function getKappungMonatStandard() {
        $zeiten = $this->_getZeiten();
        $stunden = $zeiten->kappung->monat->stunden;
        $minuten = $zeiten->kappung->monat->minuten;
        if ($stunden != -1) {
            $kappung = new Azebo_Model_Saldo($stunden, $minuten, true);
        } else {
            $kappung = null;
        }
        return $kappung;
    }

    public function hatVertreter() {
        return $this->_row->vertreter === null ? false : true;
    }

    public function getVertretene() {
        $mitarbeiterTabelle = new Azebo_Resource_Mitarbeiter();
        $vertretene = $mitarbeiterTabelle->getIstVertreterFuerNachId($this->id);
        return $vertretene;
    }

    public function istVertreter() {
        $vertretene = $this->getVertretene();
        $anzahl = count($vertretene);
        return $anzahl == 0 ? false : true;
    }

    public function getAzvTage() {
        $zeiten = $this->_getZeiten();
        return $this->getBeamter() ? $zeiten->azv->tage : 0;
    }

    public function getAzvTageBisher(Zend_Date $monat) {
        $azvGesamt = 0;
        $arbeitsmonate = $this->getArbeitsmonate();
        foreach ($arbeitsmonate as $arbeitsmonat) {
            $azv = $arbeitsmonat->azv;
            if ($azv !== null &&
                    $arbeitsmonat->getMonat()->compareMonth($monat) == -1) {
                $azvGesamt += $azv;
            }
        }

        return $azvGesamt;
    }

    public function getAzvTageNachMonat(Zend_Date $monat) {
        $arbeitstage = $this->getArbeitstageNachMonat($monat);
        $anzahl = 0;
        foreach ($arbeitstage as $arbeitstag) {
            if ($arbeitstag->befreiung == 'azv') {
                $anzahl++;
            }
        }
        return $anzahl;
    }

    /**
     * @return Zend_Date 
     */
    public function getUebertragenBis() {
        $dzService = new Azebo_Service_DatumUndZeitUmwandler();
        $ergebnis = $dzService->datumSqlZuPhp($this->_row->uebertragenbis);
        if ($ergebnis === null) {
            $ergebnis = new Zend_Date('31.12.2012');
        }
        return $ergebnis;
    }

    public function setUebertragenbis(Zend_Date $uebertragenbis) {
        $dzService = new Azebo_Service_DatumUndZeitUmwandler();
        $this->_row->uebertragenbis = $dzService->datumPhpZuSql($uebertragenbis);
    }

    public function jahresabschlussFehlt(Zend_Date $monat) {
        return $this->getUebertragenBis()->get(Zend_Date::YEAR) <
                $monat->get(Zend_Date::YEAR) - 1;
    }

    public function getFarben() {
        if ($this->_farben === null) {
            $this->_farben = new Azebo_Model_Farben();
            $config = new Zend_Config_Ini(
                    APPLICATION_PATH . '/configs/farben.ini');
            $configFarben = $config->farben;
            $kopf = $this->_row->farbekopf !== null ?
                    $this->_row->farbekopf : $configFarben->kopf;
            $hoover = $this->_row->farbehoover !== null ?
                    $this->_row->farbehoover : $configFarben->hoover;
            $link = $this->_row->farbelink !== null ?
                    $this->_row->farbelink : $configFarben->link;
            $zeile = $this->_row->farbezeile !== null ?
                    $this->_row->farbezeile : $configFarben->zeile;
            $kopf = '#' . $kopf;
            $hoover = '#' . $hoover;
            $link = '#' . $link;
            $zeile = '#' . $zeile;

            $this->_farben->kopf = $kopf;
            $this->_farben->hoover = $hoover;
            $this->_farben->link = $link;
            $this->_farben->zeile = $zeile;
        }
        return $this->_farben;
    }

    public function setFarben($farben) {
        if ($farben !== null) {
            $this->_farben = $farben;

            $kopf = substr($farben->kopf, 1);
            $hoover = substr($farben->hoover, 1);
            $link = substr($farben->link, 1);
            $zeile = substr($farben->zeile, 1);

            $this->_row->farbekopf = $kopf;
            $this->_row->farbehoover = $hoover;
            $this->_row->farbelink = $link;
            $this->_row->farbezeile = $zeile;
        } else { // $farben === null
            $config = new Zend_Config_Ini(
                    APPLICATION_PATH . '/configs/farben.ini');
            $configFarben = $config->farben;

            $kopf = '#' . $configFarben->kopf;
            $hoover = '#' . $configFarben->hoover;
            $link = '#' . $configFarben->link;
            $zeile = '#' . $configFarben->zeile;

            $this->_farben = new Azebo_Model_Farben();
            $this->_farben->kopf = $kopf;
            $this->_farben->hoover = $hoover;
            $this->_farben->link = $link;
            $this->_farben->zeile = $zeile;

            $this->_row->farbekopf = null;
            $this->_row->farbehoover = null;
            $this->_row->farbelink = null;
            $this->_row->farbezeile = null;
        }
    }

    /**
     * @return Azebo_Resource_Vorjahr_Item
     */
    public function getVorjahr() {
        $vorjahrTabelle = new Azebo_Resource_Vorjahr();
        $vorjahr = $vorjahrTabelle->getVorjahrNachMitarbeiterId($this->id);
        return $vorjahr;
    }

    public function abschlussZuruecknehmen(Zend_Date $ab) {
        $monate = $this->getArbeitsmonate(false);
        // entferne die Monate aus der Arbeitsmonat-Tabelle, die in dem selben
        // Jahr wie $ab liegen und nicht vor $ab sind
        foreach ($monate as $monat) {
            if ($monat->getMonat()->compareMonth($ab) !== -1 &&
                    $monat->getMonat()->compareYear($ab) === 0) {
                $this->deleteArbeitsmonat($monat->getMonat());
            }
        }

        if ($ab->compareYear($this->getUebertragenBis()) !== 1) {
            // $ab liegt vor dem letzten Übertrag ...
            // also entferne die Monate aus der Tabelle, die in dem Jahr nach
            // $ab liegen und ...
            foreach ($monate as $monat) {
                if ($monat->getMonat()->compareYear($ab) == 1) {
                    $this->deleteArbeitsmonat($monat->getMonat());
                }
            }

            // mache den Jahresabschluss rückgängig und ...
            $vorjahr = $this->getVorjahr();
            $saldouebertrag = $vorjahr->getSaldouebertrag();
            $this->setSaldoUebertrag($saldouebertrag);
            if ($saldouebertrag->getRest()) {
                $this->setSaldo2007(new Azebo_Model_Saldo(
                        $saldouebertrag->getRestStunden(), $saldouebertrag->getRestMinuten(), true));
            }
            $this->setUrlaub($vorjahr->getUrlaub());
            $this->setUrlaubVorjahr($vorjahr->getUrlaubVorjahr());
            $uebertragenBis = $this->getUebertragenBis();
            $uebertragenBis->addYear(-1);
            $this->setUebertragenbis($uebertragenBis);
            $this->save();

            // markiere die Monate des Vorjahrs als nicht übertragen und ...
            $monate = $this->getArbeitsmonate(false);
            foreach ($monate as $monat) {
                if ($monat->getMonat()->compareYear($ab) === 0) {
                    $monat->setUebertragen(false);
                    $monat->save();
                }
            }

            // lösche das Vorjahr aus der Tabelle
            $vorjahr->delete();
        }
    }

}
