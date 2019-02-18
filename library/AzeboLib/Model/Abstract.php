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
 * Die abstrakte Basisklasse von der alle Modelle erben.
 * 
 * DieserAnsatz wurde gewählt um eine saubere OO-Programmierung zu gewärleisten
 * und das 'Hat-Ein'-Entwurfsmuster für die Beziehung Modell -- 
 * Datenbank-Tabelle zu implementieren.
 *
 * @author emu
 */
abstract class AzeboLib_Model_Abstract implements AzeboLib_Model_Interface {

    /**
     * @var array mit den Klassenmethoden
     */
    protected $_classMethods;

    /**
     *
     * @var array mit den Resourcen (DB-Tabellen usw.) 
     */
    protected $_resources = array();

    /**
     * @var array mit den zum Modell gehörenden Forms. 
     */
    protected $_forms = array();

    /**
     * Konstruktor
     *
     * @param array|Zend_Config|null $options
     * @return void
     */
    public function __construct($options = null) {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        if (is_array($options)) {
            $this->setOptions($options);
        }

        $this->init();
    }

    /**
     * Konstruktor Erweiterungen
     */
    public function init() {
        
    }

    /**
     * Optionen setzen mit den setter-Methoden der konkreten Klasse.
     *
     * @param array $options
     * @return SF_Model_Abstract 
     */
    public function setOptions(array $options) {
        if (null === $this->_classMethods) {
            $this->_classMethods = get_class_methods($this);
        }
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $this->_classMethods)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Eine Resource des Modells holen
     *
     * @param string $name
     * @return SF_Model_Resource_Interface 
     */
    public function getResource($name) {
        if (!isset($this->_resources[$name])) {
            $class = join('_', array(
                $this->_getNamespace(),
                'Resource',
                $this->_getInflected($name)
                    ));
            $this->_resources[$name] = new $class();
        }
        return $this->_resources[$name];
    }

    /**
     * Eine Form des Modells holen
     * 
     * @param string $name
     * @return Zend_Form 
     */
    public function getForm($name) {
        if (!isset($this->_forms[$name])) {
            $class = join('_', array(
                $this->_getNamespace(),
                'Form',
                $this->_getInflected($name)
                    ));
            $this->_forms[$name] = new $class(array('model' => $this));
        }
        return $this->_forms[$name];
    }

    /**
     * Um den Zend-AutoLoader korrekt zu benuten wird der 'AzeboLib'-Namensraum
     * eingeführt. Die implementierenden Klassen können so Ihren Namensraum
     * bestimmen.
     *
     * @return string Den Namensraum dieser Klasse
     */
    private function _getNamespace() {
        $ns = explode('_', get_class($this));
        return $ns[0];
    }

    /**
     * Inflect the name using the inflector filter
     *
     * Changes camelCaseWord to Camel_Case_Word
     *
     * @param string $name The name to inflect
     * @return string The inflected string
     */
    private function _getInflected($name) {
        $inflector = new Zend_Filter_Inflector(':class');
        $inflector->setRules(array(
            ':class' => array('Word_CamelCaseToUnderscore')
        ));
        return ucfirst($inflector->filter(array('class' => $name)));
    }

}
