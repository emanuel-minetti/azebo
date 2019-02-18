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
 *     Copyright 2012-19 Emanuel Minetti (e.minetti (at) posteo.de)
 */

/**
 * Description of Item
 *
 * @author Emanuel Minetti
 */
class Azebo_Resource_Arbeitstag_Item extends AzeboLib_Model_Resource_Db_Table_Row_Abstract implements Azebo_Resource_Arbeitstag_Item_Interface {

    protected $_session;
    protected $_feiertagsService;
    protected $_dzService;
    protected $_zeitrechnerService;
    protected $_feiertag;
    protected $_regel;
    protected $_ist;
    protected $_anwesend;
    protected $_soll;
    protected $_saldo;

    public function __construct($config) {
        parent::__construct($config);
        $this->_dzService = new Azebo_Service_DatumUndZeitUmwandler();
        $this->_session = new Zend_Session_Namespace();
        $this->_feiertagsService = $this->_session->feiertagsservice;
        $this->_zeitrechnerService = new Azebo_Service_Zeitrechner();
    }

    public function getBeginn() {
        return $this->_dzService->zeitSqlZuPhp($this->_row->beginn);
    }

    /**
     * Gibt den Beginn der nachmittäglichen Anwesenheit als Zend_Date zurück
     * oder null, falls der Mitarbeiter an diesem Tag keinen Nachmittagsbeginn
     * eingegeben hat.
     * 
     * @return null|Zend_Date
     */
    public function getBeginnNachmittag() {
        return $this->_dzService->zeitSqlZuPhp($this->_row->nachmittagbeginn);
    }

    public function getEnde() {
        return $this->_dzService->zeitSqlZuPhp($this->_row->ende);
    }

    /**
     * Gibt das Ende der nachmittäglichen Anwesenheit als Zend_Date zurück
     * oder null, falls der Mitarbeiter an diesem Tag keinen Nachmittagsende
     * eingegeben hat.
     * 
     * @return null|Zend_Date
     */
    public function getEndeNachmittag() {
        return $this->_dzService->zeitSqlZuPhp($this->_row->nachmittagende);
    }

    public function setBeginn($beginn) {
        $this->_row->beginn = $this->_dzService->zeitPhpZuSql($beginn);
    }

    public function setBeginnNachmittag($beginn) {
        $this->_row->nachmittagbeginn = $this->_dzService->zeitPhpZuSql($beginn);
    }

    public function setEnde($ende) {
        $this->_row->ende = $this->_dzService->zeitPhpZuSql($ende);
    }

    public function setEndeNachmittag($ende) {
        $this->_row->nachmittagende = $this->_dzService->zeitPhpZuSql($ende);
    }

    public function getTag() {
        return $this->_dzService->datumSqlZuPhp($this->_row->tag);
    }

    public function setTag($tag) {
        $this->_row->tag = $this->_dzService->datumPhpZuSql($tag);
    }

    /**
     * Liefert ein Array mit den Eigenschaften 'name' und 'feiertag'
     * zurück. 'name' ist ein string mit dem Namen des Feiertags.
     * 'feiertag' ist ein boolean, der true ist falls das Datum ein
     * Feiertag ist.
     * 
     * @return array
     */
    public function getFeiertag() {
        if ($this->_feiertag === null && $this->_feiertagsService !== null) {
            $this->_feiertag = $this->_feiertagsService->feiertag($this->getTag());
        }
        return $this->_feiertag;
    }

    /**
     * Holt die Arbeitsregel für diesen Tag aus der DB.
     * 
     * Gibt es für diesen Tag keine Regel oder ist dieser Tag ein Feier-, Sonn-
     * oder Samstag wird NULL zurückgegeben, ansonsten ein Objekt vom Typ
     * Azebo_Resource_Arbeitsregel_Item_Interface
     * 
     * @return null|Azebo_Resource_Arbeitsregel_Item_Interface die Regel
     */
    public function getRegel() {
        //Prüfe, ob die Regel für diesen Tag schon gesetzt ist. Falls ja,
        //gib sie einfach zurück.
        if ($this->_regel === null) {
            //Hole die Regeln für den ganzen Monat
            $arbeitsregelTabelle = new Azebo_Resource_Arbeitsregel();
            $arbeitsregeln = $arbeitsregelTabelle->
                    getArbeitsregelnNachMonatUndMitarbeiterId(
                    $this->getTag(), $this->mitarbeiter_id);

            //Iteriere über die Regeln
            foreach ($arbeitsregeln as $arbeitsregel) {
                if ($arbeitsregel->getVon()->compare($this->getTag()) != 1 &&
                        ($arbeitsregel->getBis() === null ||
                        $arbeitsregel->getBis()->compare($this->getTag()) != -1)) {
                    if ($arbeitsregel->wochentag == 'Alle') {
                        //Regel gilt für 'alle' Wochentage
                        if ($arbeitsregel->kalenderwoche == 'alle') {
                            $this->_regel = $arbeitsregel;
                            break;
                        } else {
                            $kwUngerade = $this->getTag()->get(Zend_Date::WEEK) % 2;
                            if (($kwUngerade && $arbeitsregel->kalenderwoche == 'ungerade') ||
                                    (!$kwUngerade && $arbeitsregel->kalenderwoche == 'gerade')) {
                                $this->_regel = $arbeitsregel;
                                break;
                            }
                        }
                    } else {
                        //Regel gilt für einen Wochentag
                        if ($arbeitsregel->wochentag == $this->getTag()->get(Zend_Date::WEEKDAY)) {
                            if ($arbeitsregel->kalenderwoche == 'alle') {
                                $this->_regel = $arbeitsregel;
                                break;
                            } else {
                                $kwUngerade = $this->getTag()->get(Zend_Date::WEEK) % 2;
                                if (($kwUngerade && $arbeitsregel->kalenderwoche == 'ungerade') ||
                                        (!$kwUngerade && $arbeitsregel->kalenderwoche == 'gerade')) {
                                    $this->_regel = $arbeitsregel;
                                    break;
                                }
                            }
                        }
                    }
                }
            }

            //Falls dieser Tag ein 'Feiertag' ist, gib NULL zurück
            $feiertag = $this->getFeiertag();
            if ($feiertag['feiertag']) {
                $this->_regel = null;
            }
        }

        return $this->_regel;
    }

    /**
     * Gibt die Anwesenheitszeit des Mitarbeiters an diesem Tag zurück. Falls der
     * Mitarbeiter an diesem Tag nicht anwesend war, wird NULL zurück gegeben.
     *
     * Die Anwesenheitszeit ist die Zeit, in der ein Mitarbeiter anwesend war.
     *
     * @return null|Zend_Date
     */
    public function getAnwesend() {
        if ($this->_anwesend === null) {
            if ($this->getBeginn() !== null && $this->getEnde() !== null) {
                $this->_anwesend = $this->_zeitrechnerService->anwesend(
                        $this->getBeginn(), $this->getEnde());
            }
            if ($this->getNachmittag() && $this->getBeginnNachmittag() !== null &&
                    $this->getEndeNachmittag() !== null) {
                $anwesendNachmittag = $this->_zeitrechnerService->anwesend(
                        $this->getBeginnNachmittag(), $this->getEndeNachmittag());
                $this->_anwesend = $this->_anwesend === null ?
                        $anwesendNachmittag :
                        $this->_anwesend->addTime($anwesendNachmittag);
            }
        }

        return $this->_anwesend;
    }

    /**
     * Gibt die Ist-Arbeitszeit für diesen Arbeitstag zurück.
     *
     * Die Ist-Zeit bezeichnet die Zeit, die dem Beschäftigten als Arbeits-
     * zeit angerechnet wird.
     * 
     * Für Mitarbeiter der HfM wird hier berechnet, ob eine Pause abgezogen
     * werden soll und wenn ja in welcher Länge.
     * 
     * @return Zend_Date Die Ist-Arbeitszeit für diesen Arbeitstag
     */
    public function getIst() {
        $model = new Azebo_Model_Mitarbeiter();
        $mitarbeiter = $model->getMitarbeiterNachId($this->mitarbeiter_id);
        $hochschule = $mitarbeiter->getHochschule();
        if ($hochschule != 'hfm') {
            if ($this->_ist === null) {
                if ($this->getAnwesend() !== null) {
                    $ohnePause = $this->pause == '-' ? false : true;
                    $this->_ist = $this->_zeitrechnerService->ist(
                            $this->_anwesend, $ohnePause);
                }
            }
        } else {
            //Hier wird die Pause für die Mitarbeiter der HfM berechnet.
            $pausenzeiten = $this->_session->zeiten->pause;
            $gesamt = $this->getAnwesend();
            $vormittag = $gesamt ?
                    $this->_zeitrechnerService->anwesend(
                            $this->getBeginn(), $this->getEnde()) :
                    null;
            $nachmittag = ($this->getNachmittag() &&
                    $this->getBeginnNachmittag() && $this->getEndeNachmittag()) ?
                        $this->_zeitrechnerService->anwesend(
                            $this->getBeginnNachmittag(), $this->getEndeNachmittag()) :
                            null;
            $zwischenzeit = ($vormittag && $nachmittag) ?
                    $this->_zeitrechnerService->anwesend(
                            $this->getEnde(), $this->getBeginnNachmittag()) :
                            null;

            if(!$gesamt) {
                $this->_ist = null;
            }
            else {
                if($gesamt->compareTime($pausenzeiten->kurz->ab) !== 1) {
                    $this->_ist = $this->_zeitrechnerService->ist($gesamt, true);
                } else {
                    if($gesamt->compareTime($pausenzeiten->lang->ab) !== 1) {
                        if($vormittag->compareTime($pausenzeiten->kurz->ab) === 1 ||
                                ($nachmittag && $nachmittag->compareTime($pausenzeiten->kurz->ab) === 1)) {
                            $this->_ist = $this->_zeitrechnerService->ist($gesamt, false, false);
                        }
                        else {
                            if($zwischenzeit && $zwischenzeit->compareTime($pausenzeiten->kurz->dauer) !== -1) {
                                $this->_ist = $this->_zeitrechnerService->ist($gesamt, true);
                            }
                            else {
                                $this->_ist = $this->_zeitrechnerService->ist($gesamt, false);
                            }
                        }
                    }
                    else {
                        if($vormittag->compareTime($pausenzeiten->lang->ab) === 1 || ($nachmittag && $nachmittag->compareTime($pausenzeiten->lang->ab) === 1)) {
                            $this->_ist = $this->_zeitrechnerService->ist($gesamt, false, true);
                        }
                        else {
                            if($zwischenzeit->compareTime($pausenzeiten->lang->dauer) === -1) {
                                $this->_ist = $this->_zeitrechnerService->ist($gesamt, false, true);
                            }
                            else {
                                if($vormittag->compareTime($pausenzeiten->kurz->ab) === 1 || ($nachmittag && $nachmittag->compareTime($pausenzeiten->kurz->ab) === 1)) {
                                    $this->_ist = $this->_zeitrechnerService->ist($gesamt, false, false);
                                }
                                else {
                                    $this->_ist = $this->_zeitrechnerService->ist($gesamt, true);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $this->_ist;
    }

    /**
     * Gibt das Soll für diesen Arbeitstag des Mitarbeiters zurück.
     *
     * In der Tabelle 'Arbeitsregel' der DB ist bei Vollzeit für das Soll
     * NULL abgelegt. In diesem Fall muss das effektive Soll berechnet werden!
     * Das Soll wird lazy berechnet und für spätere Abfragen als Eigenschaft
     * des Arbeitstages gespeichert.
     *
     * @return Zend_Date Das Soll für diesen Arbeitstag Im Zeit-Teil des Rückgabewertes
     */
    public function getSoll() {
        if ($this->_soll === null ) {
            // soll für diesen Arbeitstag noch nicht gesetzt, also ermittle es
            if ($this->getRegel() === null) {
                // keine Regel, also auch kein Arbeitstag
                $soll = '00:00';
                $soll = new Zend_Date($soll, 'HH:mm');
            } else {
                $soll = $this->getRegel()->getSollOrNull();
                if ($soll == null) {
                    // Beschäftigter arbeitet Vollzeit an diesem Tag, also
                    // ermittle Vollzeit für diesen Beschäftigten für diesen Tag:
                    // zunächst Beschäftigungsart ermitteln
                    $mitarbeiterModel = new Azebo_Model_Mitarbeiter();
                    $mitarbeiter = $mitarbeiterModel->getMitarbeiterNachId($this->_row->mitarbeiter_id);
                    $art = $mitarbeiter->getBeamter() ? 'beamter' : 'normal';

                    // Hole die Session und die Startdaten der Vollzeit
                    $ns = new Zend_Session_Namespace();
                    $vollzeitAbStringArray = $ns->zeiten->vollzeit->$art->ab->toArray();

                    // Ermittle, welcher Index des Vollzeit-Array verwendet werden muss
                    // Da nur einmal im Jahr die Vollzeit wechseln kann,
                    //müssen nur die beiden letzten Zeiten geprüft werden
                    $vollzeitIndex = count($vollzeitAbStringArray) - 1;
                    $vollzeitAbLetzte = new Zend_Date($vollzeitAbStringArray[$vollzeitIndex], 'dd.MM.YYYY');
                    if ($this->getTag()->compareDate($vollzeitAbLetzte) === -1) {
                        $vollzeitIndex--;
                    }

                    // Wochentag ermitteln
                    $kurzTagString = $this->getTag()->toString('EE');
                    $kurzTagString = substr($kurzTagString, 0, 2);

                    // Vollzeit-Arbeitszeit holen und speichern!
                    $vollzeitArray = $ns->zeiten->vollzeit->$art->$kurzTagString->toArray();
                    $soll = $vollzeitArray[$vollzeitIndex];
                    $soll = new Zend_Date($soll, 'HH:mm:ss');

                }
            }
            // Soll für spätere Abfragen speichern
            $this->_soll = $soll;
        }
        return $this->_soll;
    }

    /**
     * Gibt das Saldo dieses Arbeitstages zurück.
     *
     * Das Saldo wird lazy berechnet und in einer Eigenschaft gespeichert.
     *
     * @return Azebo_Model_Saldo das Saldo
     */
    public function getSaldo() {
        if ($this->_saldo === null) {
            if ($this->getIst() !== null) {
                $this->_saldo = $this->_zeitrechnerService->saldo(
                        $this->_ist, $this->getSoll());
            } else {
                //Gleitzeittage anrechnen
                if ($this->befreiung == 'fa') {
                    $this->_saldo = $this->_zeitrechnerService->saldo(
                            $this->_ist, $this->getSoll());
                } else {
                    $this->_saldo = new Azebo_Model_Saldo(0, 0, true);
                }
            }
        }

        // für die KHB den Tag der offenen Tür (So) doppelt berechnen.
        $ns = new Zend_Session_Namespace();
        $mitarbeiter = $ns->mitarbeiter;
        if ($mitarbeiter->getHochschule() == 'khb') {
            $feiertag = $this->getFeiertag();
            $tag = $this->getTag();
            if ($feiertag['name'] == 'Tag der offenen Tür' &&
                    $tag->get(Zend_Date::WEEKDAY_DIGIT) == 0) {
                $this->_saldo->add($this->_saldo);
            }
        }

        return $this->_saldo;
    }

    /**
     * Gibt zurück, ob der Nachmittag in der Tabelle gesetzt ist oder nicht.
     * 
     * @return boolean
     */
    public function getNachmittag() {
        return $this->_row->nachmittag == 'ja' ? true : false;
    }

    public function toggleNachmittag() {
        $this->_row->nachmittag = $this->_row->nachmittag == 'ja' ? 'nein' : 'ja';
        $this->save();
    }

}
