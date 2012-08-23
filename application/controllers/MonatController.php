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

        // Lade den mitarbeiter und die Arbeitstage
        $authService = new Azebo_Service_Authentifizierung();
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
        $erg = $this->_befuelleDieTabelle($datum, 1, $this->tageImMonat);

        $this->view->monatsDaten = $erg['tabellenDaten'];
        $this->view->hoheTageImMonat = $erg['hoheTage'];
    }

    public function editAction() {
        $request = $this->getRequest();
        if($request->isPost()) {
            //TODO 'zuruecksetzen' implementieren
            //TODO Tag-Form: post-Daten filtern und validieren!
            $daten = $request->getPost();
            $this->mitarbeiter->saveArbeitstag($this->zuBearbeitendesDatum, $daten) ;            
        }
        $datum = new Zend_Date($this->zuBearbeitendesDatum);

        // setze den Seitennamen
        $this->erweitereSeitenName(' - Bearbeite ');
        $this->erweitereSeitenName($this->zuBearbeitendesDatum
                        ->toString('d.M.yy'));

        // befülle die obere Tabelle
        if ($this->tag != 1) {
            $erg = $this->_befuelleDieTabelle($datum, 1, $this->tag - 1);
            $this->view->monatsDatenOben = $erg['tabellenDaten'];
            $this->view->hoheTageImMonatOben = $erg['hoheTage'];
        } else {
            $this->view->monatsDatenOben = null;
            $this->view->hoheTageImMonatOben = 0;
        }

        //Falls ein arbeitstag für diesen Tag existiert, entferne
        //ihn aus dem 'arbeitstage'-Array.
        $arbeitstag = null;
        if (count($this->arbeitstage) !== 0 &&
                $this->zuBearbeitendesDatum->compareDate(
                        $this->arbeitstage[0]->tag, 'yyyy-MM-dd') === 0) {
            $arbeitstag = array_shift($this->arbeitstage);
        }
        
        $model = new Azebo_Model_Mitarbeiter();
        $form = $model->getForm('mitarbeiterTag');
        $urlHelper = $this->_helper->getHelper('url');
        $url = $urlHelper->url(array(
                    'tag' => $this->tag,
                    'monat' => $this->monat,
                    'jahr' => $this->jahr,
                ), 'monatEdit', true);
        //$url .= '#form';
        $form->setAction($url);
        $form->setMethod('post');
        $form->setName('tagForm');
        $this->view->tagForm = $form;

        //befülle die untere Tabelle
        if ($this->tag != $this->tageImMonat) {
            $erg = $this->_befuelleDieTabelle($datum, $this->tag + 1, $this->tageImMonat);
            $this->view->monatsDatenUnten = $erg['tabellenDaten'];
            $this->view->hoheTageImMonatUnten = $erg['hoheTage'];
        } else {
            $this->view->monatsDatenUnten = null;
            $this->view->hoheTageImMonatUnten = 0;
        }
    }

    private function _befuelleDieTabelle(Zend_date $datum, $erster, $letzter) {
        //TODO Die lästigen 'keine' loswerden in der Tabelle!
        $tabellenDaten = new Zend_Dojo_Data();
        $tabellenDaten->setIdentifier('datum');
        $anzahlHoheTage = 0;
        for ($tag = $erster; $tag <= $letzter; $tag++) {
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
                $tabellenDaten->addItem(array(
                    'datum' => $feiertag['name'] . ' ' . $datum->toString('EE, dd.MM.YYYY'),
                    'feiertag' => $feiertag['feiertag'],
                    'beginn' => $beginn,
                    'ende' => $ende,
                    'befreiung' => $arbeitstag->befreiung,
                    'bemerkung' => $arbeitstag->bemerkung,
                    'pause' => $arbeitstag->pause,
                ));
            } else { //kein Eintrag in 'arbeitstage' für diesen Tag
                $tabellenDaten->addItem(array(
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

        return array(
            'tabellenDaten' => $tabellenDaten,
            'hoheTage' => $anzahlHoheTage,
        );
    }

}
