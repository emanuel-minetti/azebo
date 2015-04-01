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
 * Schnittstelle zur MySQL-Tabelle 'arbeitstag'.
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Arbeitsregel extends AzeboLib_Model_Resource_Db_Table_Abstract implements Azebo_Resource_Arbeitsregel_Interface {

    protected $_name = 'arbeitsregel';
    protected $_id = 'id';
    protected $_rowClass = 'Azebo_Resource_Arbeitsregel_Item';
    protected $_referenceMap = array(
        'Arbeitsregel' => array(
            'columns' => 'mitarbeiter_id',
            'refTableClass' => 'Azebo_Resource_Mitarbeiter',
            'refColumns' => 'id',
        ),
    );

    public function getArbeitsregelnNachMonatUndMitarbeiterId(
    Zend_Date $monat, $mitarbeiterId) {

        // Ersten und Letzten des Monats finden
        $erster = new Zend_Date($monat);
        $letzter = new Zend_Date($monat);
        $erster->setDay(1);
        $letzter->setDay($monat->get(Zend_Date::MONTH_DAYS));

        // alle Regeln für den Mitarbeiter finden
        $select = $this->select();
        $select->where('mitarbeiter_id = ?', $mitarbeiterId);
        $dbRegeln = $this->fetchAll($select);
        
        // die Regeln, die diesen Monat gelten finden
        $regeln = array();
        foreach ($dbRegeln as $dbRegel) {
            if ($dbRegel->von->compare($letzter) != 1) {
                if ($dbRegel->bis === null) {
                    array_push($regeln, $dbRegel);
                } else {
                    if ($dbRegel->bis->compare($erster) != -1) {
                        array_push($regeln, $dbRegel);
                    }
                }
            }
        }

        return $regeln;
    }

    public function getArbeitsregelnNachMitarbeiterId($mitarbeiterId) {
        $select = $this->select();
        $select->where('mitarbeiter_id = ?', $mitarbeiterId)
                ->order('id ASC');
        $regeln = $this->fetchAll($select);
        return $regeln;
    }

    public function getArbeitsregelNachId($id) {
        return $this->find($id)->current();
    }

    /**
     * Gibt den ersten Arbeitstag eines Mitarbeiters in einem Jahr zurück.
     * Liegt der erste Arbeitstag des Mitarbeiters vor diesem Jahr, wird Neujahr
     * des gegebenen Jahres zurückgegeben.
     * 
     * @param int $mitarbeiterId die Mitarbeiter-Id.
     * @param Zend_Date $jahr das Jahr für den der erste Arbeitstag gesucht wird.
     * @return Zend_Date der erste Arbeitstag in dem übergebenen Jahr, bzw.
     * Neujahr des gegebenen Jahres.
     */
    public function getArbeitsbeginnNachMitarbeiterIdUndJahr($mitarbeiterId, Zend_Date $jahr) {
        // hole die Arbeitsregeln ...
        $arbeitsregeln = $this->getArbeitsregelnNachMitarbeiterId($mitarbeiterId);
        // und filtere diejenigen heraus, die erst nach dem gegebenen Jahr
        // beginnen
        $gefiltert = array();
        foreach ($arbeitsregeln as $arbeitsregel) {
            if ($arbeitsregel->getVon()->compareYear($jahr) != 1) {
                $gefiltert[] = $arbeitsregel;
            }
        }
        
        // setze $ergebnis auf den Silvester des gegebenen Jahres ...
        $ergebis = new Zend_Date($jahr);
        $ergebis->setMonth(12);
        $ergebis->setDay(31);
        // und laufe durch die gefilterten Regeln bis der früheste Beginn
        // gefunden ist. Setze dieses Datum als $ergebnis.
        foreach ($gefiltert as $arbeitsregel) {
            if($arbeitsregel->getVon()->compareDate($ergebis) == -1) {
                $ergebis = $arbeitsregel->getVon();
            }
        }
        
        // setze $neujahr auf den Neujahr des gegebenen Jahres.
        $neujahr = new Zend_Date($jahr);
        $neujahr->setMonth(1);
        $neujahr->setDay(1);
        
        // falls das bisherige Ergebnis vor Neujahr liegt, gib Neujahr zurück
        if ($ergebis->compareDate($neujahr) == -1) {
            $ergebis = $neujahr;
        }
        
        return $ergebis;
    }
    
    /**
     * Gibt den ersten Arbeitstag eines Mitarbeiters als Zend_Date zurück.
     * 
     * @param int $mitarbeiterId die Mitarbeiter-Id.
     * @return Zend_Date der erste Arbeitstag.
     */
    public function getArbeitsbeginnNachMitarbeiterId($mitarbeiterId) {
        $select = $this->select();
        $select->where('mitarbeiter_id = ?', $mitarbeiterId);
        $select->order('von ASC');
        $regeln = $this->fetchAll($select);
        
        return $regeln[0]->getVon(); 
    }
 
}
