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

class ErrorController extends AzeboLib_Controller_Abstract {
    
    public function init() {
        parent::init();
    }


    public function getSeitenName() {
        return 'Fehler';
    }
    
    public function errorAction() {
        $errors = $this->_getParam('error_handler');

        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'Es ist ein Fehler aufgetreten!';
            return;
        }

        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $priority = Zend_Log::NOTICE;
                $this->view->message = 'Die angefragte Seite existiert nicht!';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $priority = Zend_Log::CRIT;
                $this->view->message = 'Es ist ein Fehler in der Anwendung aufgetreten!';
                break;
        }

        // Log exception, if logger available
        $this->_log($priority, $errors);

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
    }

    protected function _log($priority, $errors) {
        $this->_log->log($this->view->message . ': ' . $errors->exception->getMessage() , $priority);
        $this->_log->log('Request Parameters: ' . var_export($errors->request->getParams(), true), $priority);
            
    }

    public function nichterlaubtAction() {
        $errors = $this->_getParam('error_handler');

        if (!$errors || !$errors instanceof ArrayObject) {
            $this->view->message = 'Es ist ein Fehler aufgetreten!';
            return;
        }
        $this->getResponse()->setHttpResponseCode(403);
        $priority = Zend_Log::WARN;
        $this->view->message = 'Sie haben keinen Zugriff auf diese Seite!';

        // Log exception, if logger available
        $this->_log($priority, $errors);
        //TODO Richtig machen!
        $this->_log->debug('Hallo');

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;        
    }

}

