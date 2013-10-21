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
 * Description of Item
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Mitarbeiter_Item extends AzeboLib_Model_Resource_Db_Table_Row_Abstract implements Azebo_Resource_Mitarbeiter_Item_Interface {

    private $_vorname = null;
    private $_nachname = null;
    private $_rolle = null;
    private $_hochschule = null;
    private $_zeiten = null;

    public function getName() {

        if ($this->_vorname === null || $this->_nachname === null) {

            $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/ldap.ini');
            $options = $config->ldap->physalis->toArray();
            $ldap = new Zend_Ldap($options);
            $ldap->bind();
            $benutzer = $ldap->getEntry('uid=' . $this->_row->benutzername . ',ou=Users,dc=verwaltung,dc=kh-berlin,dc=de');
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

    public function setRolle(array $gruppen) {

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

        $arbeitsmonatTabelle = new Azebo_Resource_Arbeitsmonat();
        $arbeitsmonatTabelle->saveArbeitsmonat($this->id, $monat, $saldo, $urlaubGesamt['diff'], $urlaubGesamt['diffVorjahr']);
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
            $uebertrag = new Azebo_Model_Saldo($stunden, $minuten, $positiv, true, $restStunden, $restMinuten);
        }
        return $uebertrag;
    }

    public function getArbeitsmonate() {
        $monatsTabelle = new Azebo_Resource_Arbeitsmonat();
        return $monatsTabelle->getArbeitsmonateNachMitarbeiterId($this->id);
    }

    /**
     *
     * @return Azebo_Model_Saldo 
     */
    public function getSaldoBisher(Zend_Date $bis) {
        $saldo = $this->getSaldouebertrag();
        $monate = $this->getArbeitsmonate();
        foreach ($monate as $monat) {
            if ($monat->getMonat()->compare($bis, Zend_Date::MONTH) == -1) {
                $monatsSaldo = $monat->getSaldo();
                $saldo->add($monatsSaldo, true);
            }
        }
        return $saldo;
    }

    public function getUrlaubBisher(Zend_Date $bis) {
        $urlaub = $this->getUrlaub();
        $monate = $this->getArbeitsmonate();
        foreach ($monate as $monat) {
            if ($monat->getMonat()->compare($bis, Zend_Date::MONTH) == -1) {
                $urlaub -= $monat->urlaub;
            }
        }
        return $urlaub;
    }

    public function getUrlaubVorjahrBisher(Zend_Date $bis) {
        $zeiten = $this->_getZeiten();
        $vorjahrRestBis = $zeiten->urlaub->resturlaubbis;
        $vorjahrRestBis = new Zend_Date($vorjahrRestBis, 'dd.MM.');
        if ($bis->compareMonth($vorjahrRestBis) != 1) {
            // Vorjahresurlaub noch gültig
            $urlaub = $this->getUrlaubVorjahr();
            $monate = $this->getArbeitsmonate();
            foreach ($monate as $monat) {
                if ($monat->getMonat()->compare($bis, Zend_Date::MONTH) == -1) {
                    $urlaub -= $monat->urlaubvorjahr;
                }
            }
        } else {
            // Vorjahresurlaub nicht mehr gültig
            $urlaub = 0;
        }
        return $urlaub;
    }

    /**
     * Gibt das Saldo des laufenden Monats (also des im Parameter $monat
     * übergebenen Monats zurück. Ist $vorlaeufig == true, so werden nur die
     * Tage berücksichtigt, bei denen Beginn und Ende bzw. eine Dienstbefreiung
     * eingetragen sind.
     * 
     * @param Zend_Date $monat
     * @param type $vorlaeufig
     * @return \Azebo_Model_Saldo 
     */
    public function getSaldo(Zend_Date $monat, $vorlaeufig = false) {
        $arbeitstage = $this->getArbeitstageNachMonat($monat);
        $saldoBisher = $this->getSaldoBisher($monat);
        if ($saldoBisher->getRest()) {
            $saldo = new Azebo_Model_Saldo(0, 0, true, true,
                            $saldoBisher->getRestStunden(),
                            $saldoBisher->getRestMinuten());
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
        $saldoBisher = $this->getSaldoBisher($monat);
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
            $saldoGesamt = new Azebo_Model_Saldo($kappungGesamt->getStunden(),
                            $kappungGesamt->getMinuten(), true,
                            $saldoGesamt->getRest(),
                            $saldoGesamt->getRestStunden(),
                            $saldoGesamt->getRestMinuten());
        }

        if ($differenz) {
            $saldoBisher = new Azebo_Model_Saldo($saldoBisher->getStunden(),
                            $saldoBisher->getMinuten(), !$saldoBisher->getPositiv());
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
        return $monatsTabelle->getArbeitsmonateNachJahrUndMitarbeiterId($jahr, $this->id);
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
        $heute = new Zend_Date();
        $arbeitsmonate = $this->getArbeitsmonateNachJahr($heute);
        $letzter = null;
        for ($index = count($arbeitsmonate) - 1; $index >= 0; $index--) {
            if ($arbeitsmonate[$index]->getSaldo()->getStunden() !== null) {
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
                        getArbeitsmonatNachMitabeiterIdUndMonat($this->id, $monat);
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

    public function setSaldo2007(Azebo_Model_Saldo $saldo) {
        $this->_row->saldo2007stunden = $saldo->getStunden();
        $this->_row->saldo2007minuten = $saldo->getMinuten();
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

}