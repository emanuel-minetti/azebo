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
 * Description of zehnStunden
 *
 * @author Emanuel Minetti
 */
class Azebo_Validate_ZehnStunden extends Zend_Validate_Abstract {

    const MEHR_ALS_ZEHN_STUNDEN = 'MehrAlsZehnStunden';

    protected $_messageTemplates = array(
        self::MEHR_ALS_ZEHN_STUNDEN => 'Die tägliche Arbeitszeit darf 10 Stunden nicht überschreiten!
            Bitte geben Sie eine Bemerkung an!'
    );

    public function isValid($value, $context = null) {
        $this->_setValue($value);
        $filter = new Azebo_Filter_ZeitAlsDate();
        $ns = new Zend_Session_Namespace();
        $zeitenConfig = $ns->zeiten;
        $maxZeit = new Zend_Date($zeitenConfig->maximal, Zend_Date::TIMES);

        if (is_array($context)) {
            if (isset($context['beginn']) && $context['beginn'] != '' &&
                    isset($context['ende']) && $context['ende'] != '') {
                $ende = $filter->filter($context['ende']);
                $beginn = $filter->filter($context['beginn']);
                $zeitService = new Azebo_Service_Zeitrechner();
                $anwesend = $zeitService->anwesend($beginn, $ende);
                $anwesendNachmittag = null;
                if (isset($context['nachmittag']) &&
                        isset($context['beginnnachmittag']) &&
                        isset($context['endenachmittag']) &&
                        $context['beginnnachmittag'] != '' &&
                        $context['endenachmittag'] != '') {
                    $beginnNachmittag = $filter->
                            filter($context['beginnnachmittag']);
                    $endeNachmittag = $filter->
                            filter($context['endenachmittag']);
                    $anwesendNachmittag = $zeitService->
                            anwesend($beginnNachmittag, $endeNachmittag);
                }
                $bemerkung = $context['bemerkung'];
                $bemerkung = trim($bemerkung);
                if ($anwesend !== null) {
                    if ($anwesend->compareTime($maxZeit) == 1 &&
                            $bemerkung == '') {
                        $this->_error(self::MEHR_ALS_ZEHN_STUNDEN);
                        return false;
                    }
                }
                if ($anwesendNachmittag !== null) {
                    if ($anwesendNachmittag->
                            compareTime($maxZeit) == 1 &&
                            $bemerkung == '') {
                        $this->_error(self::MEHR_ALS_ZEHN_STUNDEN);
                        return false;
                    }
                }
                if ($anwesend !== null && $anwesendNachmittag !== null) {
                    $anwesend = $anwesend->addTime($anwesendNachmittag);
                    if ($anwesend->compareTime($maxZeit) == 1 &&
                            $bemerkung == '') {
                        $this->_error(self::MEHR_ALS_ZEHN_STUNDEN);
                        return false;
                    }
                }
            }
        }
        return true;
    }

}

