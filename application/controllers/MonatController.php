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

class MonatController extends AzeboLib_Controller_Abstract {

    public $tag;
    public $monat;
    public $jahr;
    public $tageImMonat;

    /**
     *
     * @var Zend_Date 
     */
    public $zuBearbeitendesDatum;

    /**
     * @var array
     */
    public $feiertage;

    /**
     * @var Azebo_Resource_Mitarbeiter_Item_Interface 
     */
    public $mitarbeiter;
    
    /**
     * @var array 
     */
    public $arbeitstage;

    public function init() {
        parent::init();

        $heute = new Zend_Date();

        // Hohle die Parameter
        $this->tag = $this->_getParam('tag', $heute->get(Zend_Date::DAY));
        $this->monat = $this->_getParam('monat', $heute->get(Zend_Date::MONTH));
        $this->jahr = $this->_getParam('jahr', $heute->get(Zend_Date::DAY));

        $this->view->tag = $this->tag;
        $this->view->monat = $this->monat;
        $this->view->jahr = $this->jahr;

        // Setze das Datum
        $this->zuBearbeitendesDatum = new Zend_Date(array(
                    'day' => $this->tag,
                    'month' => $this->monat,
                    'year' => $this->jahr,
                ));

        // Ermittle die Anzahl der Tage des Monats
        $this->tageImMonat = $this->zuBearbeitendesDatum
                ->get(Zend_Date::MONTH_DAYS);
        $this->view->tageImMonat = $this->tageImMonat;

        // Hohle ein Array mit den Feiertagsdaten des Monats
        $feiertagsservice = new Azebo_Service_Feiertag($this->jahr);
        $this->feiertage = $feiertagsservice->
                feiertage($this->zuBearbeitendesDatum);

        // Aktiviere Dojo
        $this->view->dojo()->enable()
                ->setDjConfigOption('parseOnLoad', true)
                ->requireModule('dojox.grid.DataGrid')
                ->requireModule('dojo.data.ItemFileReadStore')
                ->requireModule('dojo._base.connect');

        // lade den mitarbeiter und die Arbeitstage
        $authService = new Azebo_Service_Authentication();
        $this->mitarbeiter = $authService->getIdentity();
        $this->arbeitstage = $this->mitarbeiter
                ->getArbeitstageNachMonat($this->zuBearbeitendesDatum);
    }

    public function getSeitenName() {
        return 'Monatsübersicht';
    }

    public function indexAction() {
        $datum = new Zend_Date($this->zuBearbeitendesDatum);

        // setze den Seitennamen
        $this->erweitereSeitenName($datum->toString(' MMMM yyyy'));

        // befülle die Reihen
        $monatsDaten = new Zend_Dojo_Data();
        $monatsDaten->setIdentifier('datum');
        $anzahlHoheTage = 0;
        for ($tag = 1; $tag <= $this->tageImMonat; $tag++) {
            $datum->setDay($tag);
            $feiertag = $this->feiertage[$datum->toString('dd.MM.yyyy')];

            //prüfe ob noch Einträge in 'arbeitstage' vorhanden sind
            //und wenn ja ob der erste Eintrag zu diesem Tag passt.
            if ((count($this->arbeitstage)) !== 0 &&
                    ($datum->compareDate(
                            $this->arbeitstage[0]->tag, 'yyyy-MM-dd') === 0)) {
                $arbeitstag = array_shift($this->arbeitstage);
                $beginn = null;
                $ende = null;
                if ($arbeitstag->beginn !== null) {
                    $beginn = $datum->setTime($arbeitstag->beginn)->toString('HH:mm');
                }
                if ($arbeitstag->ende !== null) {
                    $ende = $datum->setTime($arbeitstag->ende)->toString('HH:mm');
                }
                $monatsDaten->addItem(array(
                    'datum' => $feiertag['name'] . ' ' . $datum->toString('EE, dd.MM.YYYY'),
                    'feiertag' => $feiertag['feiertag'],
                    'beginn' => $beginn,
                    'ende' => $ende,
                    'befreiung' => $arbeitstag->befreiung,
                    'bemerkung' => $arbeitstag->bemerkung,
                    'pause' => $arbeitstag->pause,
                ));
            } else { //kein Eintrag in 'arbeitstage' für diesen Tag
                $monatsDaten->addItem(array(
                    'datum' => $feiertag['name'] . ' ' . $datum->toString('EE, dd.MM.YYYY'),
                    'feiertag' => $feiertag['feiertag'],
                    'pause' => '-',
                ));
            }

            //Neujahr und Karfreitag passen in eine Zeile mit dem Wochentag,
            //sind also keine 'hohen' Tage.
            if ($feiertag['name'] != '') {
                if ($feiertag['name'] != 'Neujahr' &&
                        $feiertag['name'] != 'Karfreitag') {
                    $anzahlHoheTage++;
                }
            }
        } //for($tag)

        $this->view->monatsDaten = $monatsDaten;
        $this->view->hoheTageImMonat = $anzahlHoheTage;
    }

    public function editAction() {
        $datum = new Zend_Date($this->zuBearbeitendesDatum);

        // setze den Seitennamen
        $this->erweitereSeitenName('-Bearbeite ');
        $this->erweitereSeitenName($this->zuBearbeitendesDatum
                        ->toString('d.M.yy'));

        // befülle die obere Tabelle
        $anzahlHoheTageOben = 0;
        if ($this->tag != 1) {
            $monatsDatenOben = new Zend_Dojo_Data();
            $monatsDatenOben->setIdentifier('datum');
            for ($tagIndex = 1; $tagIndex < $this->tag; $tagIndex++) {
                $datum->setDay($tagIndex);
                $feiertag = $this->feiertage[$datum->toString('dd.MM.yyyy')];
                $monatsDatenOben->addItem(array(
                    'datum' => $feiertag['name'] . ' ' . $datum->toString('EE, dd.MM.YYYY'),
                    'feiertag' => $feiertag['feiertag'],
                ));
                if ($feiertag['name'] != '') {
                    if ($feiertag['name'] != 'Neujahr' && $feiertag['name'] != 'Karfreitag') {
                        $anzahlHoheTageOben++;
                    }
                }
            }
        } else {
            $monatsDatenOben = null;
        }

        //befülle die untere Tabelle
        $anzahlHoheTageUnten = 0;
        if ($this->tag != $this->tageImMonat) {
            $monatsDatenUnten = new Zend_Dojo_Data();
            $monatsDatenUnten->setIdentifier('datum');
            for ($tagIndex = $this->tag + 1; $tagIndex <= $this->tageImMonat; $tagIndex++) {
                $datum->setDay($tagIndex);
                $feiertag = $this->feiertage[$datum->toString('dd.MM.yyyy')];
                $monatsDatenUnten->addItem(array(
                    'datum' => $feiertag['name'] . ' ' . $datum->toString('EE, dd.MM.YYYY'),
                    'feiertag' => $feiertag['feiertag'],
                ));
                if ($feiertag['name'] != '') {
                    if ($feiertag['name'] != 'Neujahr' && $feiertag['name'] != 'Karfreitag') {
                        $anzahlHoheTageUnten++;
                    }
                }
            }
        } else {
            $monatsDatenUnten = null;
        }

        $this->view->monatsDatenOben = $monatsDatenOben;
        $this->view->monatsDatenUnten = $monatsDatenUnten;
        $this->view->hoheTageImMonatOben = $anzahlHoheTageOben;
        $this->view->hoheTageImMonatUnten = $anzahlHoheTageUnten;
    }

}
