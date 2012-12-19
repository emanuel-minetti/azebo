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

$paths = array(get_include_path(), '../library',);
set_include_path(implode(PATH_SEPARATOR, $paths));

defined('APPLICATION_PATH')
        or define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

defined('APPLICATION_ENV')
        or define(APPLICATION_ENV, 'development');

require_once 'Zend/Application.php';

$application = new Zend_Application(APPLICATION_ENV,
                APPLICATION_PATH . '/configs/app.ini');
$application->bootstrap();
$application->run();
