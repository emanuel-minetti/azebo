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
 * Erstellt eine Zend_Acl-Klasse aus einer config-Datei
 *
 * @author Emanuel Minetti
 */
class AzeboLib_Acl_Acl extends Zend_Acl {


    public function __construct() {
        $aclConfig = Zend_Registry::get('acl');
        $roles = $aclConfig->acl->roles;
        $resources = $aclConfig->acl->resources;
        $this->_addRoles($roles);
        $this->_addResources($resources);
    }

    protected function _addRoles($roles) {
        foreach ($roles as $name => $parents) {
            if (!$this->hasRole($name)) {
                if (empty($parents)) {
                    $parents = null;
                } else {
                    $parents = explode(',', $parents);
                }
                $this->addRole(new Zend_Acl_Role($name), $parents);
            }
        }
    }

    protected function _addResources($resources) {
        foreach ($resources as $permissions => $controllers) {
            foreach ($controllers as $controller => $actions) {
                if ($controller == 'all') {
                    $controller = null;
                } else {
                    if (!$this->has($controller)) {
                        $this->add(new Zend_Acl_Resource($controller));
                    }
                }
                foreach ($actions as $action => $role) {
                    if ($action == 'all') {
                        $action = null;
                    }
                    if ($permissions == 'allow') {
                        $this->allow($role, $controller, $action);
                    }
                    if ($permissions == 'deny') {
                        $this->deny($role, $controller, $action);
                    }
                }
            }
        }
    }

}

