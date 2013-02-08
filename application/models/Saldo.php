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
 * Eine Hilfsklasse um Arbeitszeitsalden zu berechnen.
 *
 * @author Emanuel Minetti
 */
class Azebo_Model_Saldo {

    private $_stunden;
    private $_minuten;
    private $_positiv;
    private $_rest2007;
    private $_restStunden;
    private $_restMinuten;

    function __construct($stunden, $minuten, $positiv, $rest = false, $restStunden = 0, $restMinuten = 0) {
        $this->_stunden = $stunden;
        $this->_minuten = $minuten;
        $this->_positiv = $positiv;
        $this->_rest2007 = $rest;
        $this->_restStunden = $restStunden;
        $this->_restMinuten = $restMinuten;
    }

    //TODO Saldo::add() kommentieren!!
    public function add(Azebo_Model_Saldo $saldo, $art) {
        //TODO Saldo2007 für HfM implementieren!
        //TODO Kappungsgrenzen!
        
        $stunden = $saldo->getStunden();
        $minuten = $saldo->getMinuten();
        $positiv = $saldo->getPositiv();
        
        if(($this->_positiv && $positiv) || (!$this->_positiv && !$positiv)) {
            // gleiches Vorzeichen
            $this->_minuten += $minuten;
            if($this->_minuten >= 60) {
                $this->_minuten -= 60;
                $this->_stunden++;
            }
            $this->_stunden += $stunden;
        } else {
            // ungleiches Vorzeichen
            
            // finde größeren Wert
            if($this->_stunden > $stunden) {
                $dieserIstGroesser = true;
            } elseif($this->_stunden < $stunden) {
                $dieserIstGroesser = false;
            } else {
                $dieserIstGroesser = $this->_minuten >= $minuten;
            }
            
            if($dieserIstGroesser) {
                if($this->_minuten >= $minuten) {
                    $this->_minuten -= $minuten;
                } else {
                    $this->_minuten = $this->_minuten - $minuten + 60;
                    $this->_stunden--;
                }
                $this->_stunden -= $stunden;
            } else {
                // der andere ist größer
                if($this->_minuten <= $minuten) {
                    $this->_minuten = $minuten - $this->_minuten;
                } else {
                    $this->_minuten = $minuten - $this->_minuten + 60;
                    $this->_stunden++;
                }
                $this->_stunden = $stunden - $this->_stunden;
                $this->_positiv = $positiv;
            }
        }
        
        if($this->_minuten == 0 && $this->_stunden == 0) {
            $this->_positiv = true;
        }
        
        //TODO Hier bin ich!!!!!!!!!!!!!!!!!!!!
        if($art != 'tag' && !$this->_positiv && $this->_rest2007) {
            if($this->_restStunden > $this->_stunden) {
                $restIstGroesser = true;
            } elseif($this->_restStunden < $this->_stunden) {
                $restIstGroesser = false;
            } else {
                $restIstGroesser = $this->_restMinuten >= $this->_minuten;
            }
            
            if($restIstGroesser) {
                if($this->_restMinuten >= $this->_minuten) {
                    $this->_restMinuten -= $this->_minuten;
                } else {
                    $this->_restMinuten = $this->_restMinuten - $this->_minuten + 60;
                    $this->_restStunden--;
                }
                $this->_restStunden -= $this->_stunden;
                $this->_minuten = 0;
                $this->_stunden = 0;
                $this->_positiv = true;
                if($this->_restStunden == 0 && $this->_restMinuten == 0) {
                    $this->_restStunden = null;
                    $this->_restMinuten = null;
                    $this->_rest2007 = false;
                }
            } else {
                // restIstGroesser == false
                if($this->_minuten >= $this->_restMinuten) {
                    $this->_minuten -= $this->_restMinuten;
                } else {
                    $this->_minuten = $this->_minuten - $this->_restMinuten + 60;
                    $this->_stunden--;
                }
                $this->_stunden -= $this->_restStunden;
                $this->_restStunden = null;
                $this->_restMinuten = null;
                $this->_rest2007 = false;
            }
        }

        return $this;
    }

    public function getStunden() {
        return $this->_stunden;
    }

    public function getMinuten() {
        return $this->_minuten;
    }

    public function getPositiv() {
        return $this->_positiv;
    }

    public function getRest() {
        return $this->_rest2007;
    }

    public function getRestStunden() {
        return $this->_restStunden;
    }

    public function getRestMinuten() {
        return $this->_restMinuten;
    }

    public function getString() {
        if ($this->_stunden === null) {
            return '+ 0:00';
        } else {
            $saldoString = $this->_positiv == true ? '+ ' : '- ';
            $saldoString .= $this->_stunden . ':';
            if ($this->_minuten <= 9) {
                $saldoString .= '0' . $this->_minuten;
            } else {
                $saldoString .= $this->_minuten;
            }
            return $saldoString;
        }
    }
    
    public function getRestString() {
        if($this->_restStunden === null || !$this->_rest2007) {
            return '+ 0:00';
        } else {
            $saldoString = $this->_restStunden . 'h:';
            if($this->_restMinuten <= 9) {
                $saldoString .= '0' . $this->_restMinuten;
            } else {
                $saldoString .= $this->_restMinuten . 'min';
            }
        }
        return $saldoString;
    }

}
