<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2011 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Remi Collet
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginDatainjectionNetworkEquipmentInjection extends NetworkEquipment
                                                   implements PluginDatainjectionInjectionInterface {


   function __construct() {
      $this->table = getTableForItemType(get_parent_class($this));
   }


   function isPrimaryType() {
      return true;
   }


   function connectedTo() {
      return array();
   }


   function getOptions($primary_type = '') {
      global $LANG;

      $tab = Search::getOptions(get_parent_class($this));

      //Specific to location
      $tab[3]['linkfield']  = 'locations_id';
      $tab[12]['checktype'] = 'ip';
      $tab[13]['checktype'] = 'mac';

      //Virtual type : need to be processed at the end !
      $tab[200]['table']       = 'glpi_networking';
      $tab[200]['field']       = 'nb_ports';
      $tab[200]['name']        = $LANG['datainjection']['mappings'][1];
      $tab[200]['checktype']   = 'integer';
      $tab[200]['displaytype'] = 'virtual';
      $tab[200]['linkfield']   = 'nb_ports';
      $tab[200]['injectable']  = PluginDatainjectionCommonInjectionLib::FIELD_VIRTUAL;

      $blacklist = PluginDatainjectionCommonInjectionLib::getBlacklistedOptions();
      //Remove some options because some fields cannot be imported
      $notimportable = array(2, 91, 92, 93, 80, 100);
      $options['ignore_fields'] = array_merge($blacklist, $notimportable);
      $options['displaytype'] = array("dropdown"       => array(3, 4, 40, 31, 71, 11, 32, 33, 23),
                                      "bool"           => array(86),
                                      "user"           => array(70, 24),
                                      "multiline_text" => array(16, 90));
      return PluginDatainjectionCommonInjectionLib::addToSearchOptions($tab, $options, $this);
   }


   /**
    * Standard method to add an object into glpi
    * WILL BE INTEGRATED INTO THE CORE IN 0.80
    *
    * @param values fields to add into glpi
    * @param options options used during creation
    *
    * @return an array of IDs of newly created objects : for example array(Computer=>1, Networkport=>10)
   **/
   function addOrUpdateObject($values=array(), $options=array()) {

      $lib = new PluginDatainjectionCommonInjectionLib($this, $values, $options);
      $lib->processAddOrUpdate();
      return $lib->getInjectionResults();
   }


   function processAfterInsertOrUpdate($values, $add = true, $rights = array()) {

      if (isset($values['NetworkEquipment']['nb_ports'])) {
         for ($i=1 ; $i<=$values['NetworkEquipment']['nb_ports'] ; $i++) {
            $input   = array ();
            $netport = new NetworkPort;
            $add     = "";

            if ($i < 10) {
               $add = "0";
            }

            $input["logical_number"] = $i;
            $input["name"]           = $add . $i;
            $input["items_id"]       = $values['NetworkEquipment']['id'];
            $input["itemtype"]       = 'NetworkEquipment';
            $input["entities_id"]    = $values['NetworkEquipment']['entities_id'];
            $netport->add($input);
         }
      }
   }

}

?>