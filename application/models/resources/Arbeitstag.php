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
 * Schnittstelle zur MySQL-Tabelle 'arbeitstag'.
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Arbeitstag extends AzeboLib_Model_Resource_Db_Table_Abstract implements Azebo_Resource_Arbeitstag_Interface {

    protected $_name = 'arbeitstag';
    protected $_id = 'id';
    protected $_rowClass = 'Azebo_Resource_Arbeitstag_Item';
    protected $_referenceMap = array(
        'Arbeitstag' => array(
            'columns' => 'mitarbeiter_id',
            'refTableClass' => 'Azebo_Resource_Mitarbeiter',
            'refColumns' => 'id',
        ),
    );

    /**
     * @param Zend_Date $tag
     * @param $mitarbeiterId
     * @return Azebo_Resource_Arbeitstag_Item_Interface 
     */
    public function getArbeitstagNachTagUndMitarbeiterId(Zend_Date $tag, $mitarbeiterId) {
        $select = $this->select();
        $select->where('mitarbeiter_id = ?', $mitarbeiterId)
                ->where('tag = ?', Azebo_Service_DatumUndZeitUmwandler::datumPhpZuSql($tag));
        $arbeitstag = $this->fetchRow($select);
        if ($arbeitstag === null) {
            $arbeitstag = $this->createRow();
            $arbeitstag->setTag($tag);
            $arbeitstag->mitarbeiter_id = $mitarbeiterId;
        }
        return $arbeitstag;
    }

    /**
     * Gibt ein Array von Azebo_Resource_Arbeitstag_Item_Interface zurück.
     * 
     * @param Zend_Date $monat
     * @param $mitarbeiterId
     * @return array 
     */
    public function getArbeitstageNachMonatUndMitarbeiterId(Zend_Date $monat, $mitarbeiterId) {
        $erster = new Zend_Date($monat);
        //$log = Zend_Registry::get('log');

        $erster->setDay(1);
        //$log->debug('Erster: ' . $erster->toString());
        $letzter = new Zend_Date($monat);
        $letzter->setDay($monat->get(Zend_Date::MONTH_DAYS));
        //$log->debug('Letzter: ' . $letzter->toString());

        $select = $this->select();
        $select->where('mitarbeiter_id = ?', $mitarbeiterId)
                ->where('tag >= ?', Azebo_Service_DatumUndZeitUmwandler::datumPhpZuSql($erster))
                ->where('tag <= ?', Azebo_Service_DatumUndZeitUmwandler::datumPhpZuSql($letzter))
                ->order('tag ASC');
        $dbTage = $this->fetchAll($select);
        $arbeitstage = array();

        $tag = new Zend_Date($erster);
        while ($tag->compareMonth($monat) == 0) {

            if ($dbTage->current() !== null &&
                    $dbTage->current()->getTag()->equals(
                            $tag, Zend_Date::DATE_MEDIUM)) {
                array_push($arbeitstage, $dbTage->current());
                $dbTage->next();
            } else {
                $arbeitstag = $this->createRow();
                $arbeitstag->setTag($tag);
                $arbeitstag->mitarbeiter_id = $mitarbeiterId;
                array_push($arbeitstage, $arbeitstag);
            }

            $tag->addDay(1);
        }

        //$log->debug('Arbeitstage: ' . print_r($arbeitstage, true));
        return $arbeitstage;
    }

    public function saveArbeitstag(Zend_Date $tag, $mitarbeiterId, array $daten) {
        //Hohle den Arbeitstag aus der DB
        $arbeitstag = $this->getArbeitstagNachTagUndMitarbeiterId($tag, $mitarbeiterId);

        //Falls der Arbeitstag noch nicht in der DB existierte,
        //initialisiere ihn
        if ($arbeitstag === null) {
            $arbeitstag = $this->createRow();
            $arbeitstag->mitarbeiter_id = $mitarbeiterId;
            $arbeitstag->tag = Azebo_Service_DatumUndZeitUmwandler::datumPhpZuSql($tag);
        }

        //Setze die Daten
        $arbeitstag->setBeginn($daten['beginn']);
        $arbeitstag->setEnde($daten['ende']);
        $arbeitstag->befreiung = $daten['befreiung'];
        $arbeitstag->bemerkung = $daten['bemerkung'];
        $arbeitstag->pause = $daten['pause'];
        if (isset($daten['beginnnachmittag'])) {
            $arbeitstag->setBeginnNachmittag($daten['beginnnachmittag']);
        }
        if (isset($daten['endenachmittag'])) {
            $arbeitstag->setEndeNachmittag($daten['endenachmittag']);
        }

        $arbeitstag->save();
    }
    
    /**
     * Gibt die Ist-Arbeitszeit einer Kalenderwoche, gegeben als int zwischen 1
     * und 53, und eines Mitarbeiters, gegeben als Id, zurück.
     * Der Rückgabewert ist ein Azebo_Model_Saldo.
     * 
     * @param int $kalenderwoche
     * @param int $mitarbeiterId
     * @return \Azebo_Model_Saldo 
     */
    public function getIstNachKalenderwocheUndMitarbeiterId(
            $kalenderwoche, $mitarbeiterId) {
        $montag = new Zend_Date();
        $montag->setWeek($kalenderwoche);
        $montag->setWeekday(1);
        $sonntag = new Zend_Date($montag);
        $sonntag->setWeekday(7);

        $select = $this->select();
        $select->where('mitarbeiter_id = ?', $mitarbeiterId)
                ->where('tag >= ?', Azebo_Service_DatumUndZeitUmwandler::datumPhpZuSql($montag))
                ->where('tag <= ?', Azebo_Service_DatumUndZeitUmwandler::datumPhpZuSql($sonntag))
                ->order('tag ASC');
        $dbTage = $this->fetchAll($select);
        $arbeitstage = array();

        $tag = new Zend_Date($montag);
        while ($tag->compareWeek($kalenderwoche) == 0) {

            if ($dbTage->current() !== null &&
                    $dbTage->current()->getTag()->equals(
                            $tag, Zend_Date::DATE_MEDIUM)) {
                array_push($arbeitstage, $dbTage->current());
                $dbTage->next();
            } else {
                $arbeitstag = $this->createRow();
                $arbeitstag->setTag($tag);
                $arbeitstag->mitarbeiter_id = $mitarbeiterId;
                array_push($arbeitstage, $arbeitstag);
            }

            $tag->addDay(1);
        }
        
        $gesamt = new Azebo_Model_Saldo(0,0,true);
        foreach ($arbeitstage as $arbeitstag) {
            $ist = $arbeitstag->getIst();
            if ($ist !== NULL) {
                $istSaldo = new Azebo_Model_Saldo(
                        $ist->get(Zend_Date::HOUR),
                        $ist->get(Zend_Date::MINUTE),
                        true);
                $gesamt->add($istSaldo);
            }
        }
        return $gesamt;
    }

    public function deleteArbeitstageBis(Zend_Date $bis, $mitarbeiterId) {
        $dzService = new Azebo_Service_DatumUndZeitUmwandler();
        $sqlBis = $dzService->datumPhpZuSql($bis);
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('tag <= ?', $sqlBis);
        $where[] = $this->getAdapter()->quoteInto('mitarbeiter_id = ?',
                $mitarbeiterId);
        $this->delete($where);
    }

}

