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
 * Schnittstelle zur MySQL-Tabelle 'arbeitsmonat'.
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Arbeitsmonat extends AzeboLib_Model_Resource_Db_Table_Abstract implements Azebo_Resource_Arbeitsmonat_Interface {

    protected $_name = 'arbeitsmonat';
    protected $_primary = 'id';
    protected $_rowClass = 'Azebo_Resource_Arbeitsmonat_Item';
    protected $_referenceMap = array(
        'Arbeitsmonat' => array(
            'columns' => 'mitarbeiter_id',
            'refTableClass' => 'Azebo_Resource_Mitarbeiter',
            'refColumns' => 'id',
        ),
    );
    
    /**
     * Holt die Arbeitsmonate eines Mitarbeiters nach seiner Id. Falls der
     * Parameter '$filter' true ist (Standard) werden nur die nicht bereits
     * übertragenen Arbeitsmonate zurückgegeben. Das ist wichtig um Saldo,
     * Saldo2007, Urlaub, Resturlaub und Azv richtig zu berechnen.
     * 
     * @param integer $mitarbeiterId
     * @param boolean $filter
     * @return Zend_Db_Table_Rowset_Abstract 
     */
    public function getArbeitsmonateNachMitarbeiterId($mitarbeiterId, $filter = true) {

        $select = $this->select();
        $select->where('mitarbeiter_id = ?', $mitarbeiterId);
        if ($filter) {
            $select->where('uebertragen = ?', 'nein');
        }
        $dbMonate = $this->fetchAll($select);

        return $dbMonate;
    }

    public function getArbeitsmonateNachJahrUndMitarbeiterId(Zend_Date $jahr, $mitarbeiterId) {
        $dzService = new Azebo_Service_DatumUndZeitUmwandler();

        $erster = new Zend_Date($jahr);
        $erster->setMonth(1);
        $erster->setDay(1);
        $letzter = new Zend_Date($jahr);
        $letzter->setMonth(12);
        $letzter->setDay(31);

        $select = $this->select();
        $select->where('mitarbeiter_id = ?', $mitarbeiterId)
                ->where('monat >= ?', $dzService->datumPhpZuSql($erster))
                ->where('monat <= ?', $dzService->datumPhpZuSql($letzter))
                ->order('monat ASC');
        $dbMonate = $this->fetchAll($select);
        $arbeitsmonate = array();
        $monat = new Zend_Date($erster);

        while ($monat->compareYear($jahr) == 0) {
            if ($dbMonate->current() !== null &&
                    $dbMonate->current()->getMonat()->equals(
                            $monat, Zend_Date::MONTH)) {
                $arbeitsmonate[] = $dbMonate->current();
                $dbMonate->next();
            } else {
                $arbeitsmonat = $this->createRow();
                $arbeitsmonat->setMonat($monat);
                $arbeitsmonat->mitarbeiter_id = $mitarbeiterId;
                $arbeitsmonate[] = $arbeitsmonat;
            }

            $monat->addMonth(1);
        }

        return $arbeitsmonate;
    }

    public function saveArbeitsmonat($mitarbeiterId, Zend_Date $monat, Azebo_Model_Saldo $saldo, $urlaub, $urlaubVorjahr, $azv) {
        $arbeitsmonat = $this->createRow();
        $arbeitsmonat->mitarbeiter_id = $mitarbeiterId;
        $arbeitsmonat->setMonat($monat);
        $arbeitsmonat->setSaldo($saldo);
        $arbeitsmonat->urlaub = $urlaub;
        $arbeitsmonat->urlaubvorjahr = $urlaubVorjahr;
        $arbeitsmonat->azv = $azv;
        $arbeitsmonat->save();
    }

    public function getArbeitsmonateNachMonat(Zend_Date $monat) {
        $select = $this->select();
        $erster = new Zend_Date($monat);
        $erster->setDay(1);
        $select->where('monat = ?', $erster->toString('yyyy-MM-dd'));
        $dbMonate = $this->fetchAll($select);

        return $dbMonate;
    }

    public function getArbeitsmonatNachMitabeiterIdUndMonat($mitarbeiterId, Zend_Date $monat) {
        $select = $this->select();
        $erster = new Zend_Date($monat);
        $erster->setDay(1);
        $select->where('monat = ?', $erster->toString('yyyy-MM-dd'))
                ->where('mitarbeiter_id = ?', $mitarbeiterId);
        $arbeitsmonat = $this->fetchRow($select);
        
        return $arbeitsmonat;
    }
    
    public function deleteArbeitsmonateBis(Zend_Date $bis, $mitarbeiterId) {
        $dzService = new Azebo_Service_DatumUndZeitUmwandler();
        $sqlBis = $dzService->datumPhpZuSql($bis);
        $where = array();
        $where[] = $this->getAdapter()->quoteInto('monat <= ?', $sqlBis);
        $where[] = $this->getAdapter()->quoteInto('mitarbeiter_id = ?',
                $mitarbeiterId);
        $this->delete($where);
    }

}

