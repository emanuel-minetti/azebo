<?php

class ErrorController extends Zend_Controller_Action {

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

    protected function _getLog() {
        $logger = Zend_Registry::get('log');
        if ($logger === null) {
            return false;
        }
        return $logger;
    }

    protected function _log($priority, $errors) {
        $log = $this->_getLog();
        if ($log) {
            $log->log($this->view->message . ': ' . $errors->exception->getMessage() , $priority);
            $log->log('Request Parameters: ' . var_export($errors->request->getParams(), true), $priority);
        }
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

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
        
    }

}

