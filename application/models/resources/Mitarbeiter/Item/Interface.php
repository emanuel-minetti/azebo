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
 *
 * @author Emanuel Minetti
 */
interface Azebo_Resource_Mitarbeiter_Item_Interface {

    public function setVorname($vorname);

    public function setNachname($nachname);

    public function setHochschule(array $gruppen);

    public function getHochschule();

    /**
     * Setzt die (ACL-)Rolle eines Mitarbeiters.
     * Erwartet ein Array mit den Namen
     * der (LDAP-)Gruppen in denen der Mitarbeiter Mitglied ist.
     * 
     * @param array $gruppen 
     */
    public function setRolle(array $gruppen);

    public function getRolle();

    public function getName();

    /**
     * @param Zend_Date $tag
     * @return Azebo_Resource_Arbeitstag_Item
     */
    public function getArbeitstagNachTag(Zend_Date $tag);

    /**
     * @param Zend_Date $monat
     * @return array 
     */
    public function getArbeitstageNachMonat(Zend_Date $monat);

    /**
     * @param Zend_Date $tag
     * @param array $daten
     */
    public function saveArbeitstag(Zend_Date $tag, array $daten);
    
    public function saveArbeitsmonat(Zend_Date $monat);
    
    /**
     *@return boolean 
     */
    public function getBeamter();
    
    /**
     * Gibt den Saldo-Übertrag des Mitarbeiters als array zurück.
     * 
     * Das Array enthält unter dem
     * Schlüssel 'saldo' ein Zend_Date-Objekt und unter dem Schlüssel 'positiv'
     * ein Bool-Wert, der anzeigt ob der Saldo positiv oder negativ zu sehen
     * ist. 
     *
     * @return array
     */
    public function getSaldouebertrag();
    
    public function getArbeitsmonate();
    
    public function getSaldoBisher(Zend_Date $bis);
    
    public function getSaldo(Zend_Date $monat);
    
    public function getSaldoGesamt(Zend_Date $monat, $differenz = false);
    
    public function getAbgeschlossenBis();
    
    public function getAbgelegtBis();
    
    public function setSaldoUebertrag(Azebo_Model_Saldo $saldo);
    
    public function setSaldo2007(Azebo_Model_Saldo $saldo);
    
    public function getArbeitsregeln();
    
    /**
     * @param Zend_Date $monat
     * @return Azebo_Resource__Arbeitsmonat_Item_Interface
     */
    public function getArbeitsmonat(Zend_Date $monat);
    
    public function deleteArbeitsmonat(Zend_Date $monat);
    
    public function arbeitsmonatAblegen(Zend_Date $monat);
    
    public function getUrlaub();
    
    public function getUrlaubVorjahr();

    public function setUrlaub($urlaub);
    
    public function setUrlaubVorjahr($urlaub);

    public function getUrlaubBisher(Zend_Date $bis);
    
    public function getUrlaubVorjahrBisher(Zend_Date $bis);
    
    public function getUrlaubNachMonat(Zend_Date $monat);
    
    public function getUrlaubGesamt(Zend_Date $monat);
    
    public function getKappungMonat();
    
    public function getKappungMonatStandard();
    
    public function setKappungMonat($kappung);
    
    public function getKappungGesamt();
    
    public function getKappungGesamtStandard();
    
    public function setKappungGesamt($kappung);
    
}
