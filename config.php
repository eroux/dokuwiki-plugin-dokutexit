<?php
/**
 * texit Configing Class
 * Copyright (C) 2006   Danjer <danjer@doudouke.org>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * --------------------------------------------------------------------
 * @author Danjer <danjer@doudouke.org>
 * @version v0.1
 * @package texitconfig
 *
 */
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('PLUGIN_TEXIT')) define('PLUGIN_TEXIT',DOKU_PLUGIN.'texit/');
if(!defined('PLUGIN_TEXIT_CONF')) define('PLUGIN_TEXIT_CONF',PLUGIN_TEXIT.'conf/');

class texit_config {
  /**
   * Required method, see https://www.dokuwiki.org/devel:helper_plugins
   * TODO
   */
  function getMethods(){
    return array();
  }
  var $id;
  var $namespace_mode;
  var $nsbpc;
  var $conf;
  // =======================================================================
  // constructor
  // =======================================================================
  function __constructor($id, $namespace_mode, $conf) {
    $this->id = $id;
    $this->ns = getNS(cleanID($id));
    $this->namespace_mode = $namespace_mode;
    $this->nsbpc = loadHelper('nsbpc');
    $this->conf = $conf;
  }

  function getMediaNS() {
    return 'media:'.getNS($id);
  }
  
  function getTexitNS() {
    return 'texit:'.getNS($id);
  }

  function getHeaderFN() {
    // first we look for nsbpc headers
    // the names are 'texit-namespace' or 'texit-page'
    $header_name = "texit-page";
    if ($this->namespace_mode) {
      $header_name = "texit-namespace";
    }
    $found = $this->nsbpc->getConfFN($header_name, $this->ns);
    if ($found) {
      return $found;
    }
    // No nsbpc configuration was found, now looking in the conf/ directory of
    // the plugin. Names are different here...
    $header_name = "header-page.tex";
    if ($this->namespace_mode) {
      $header_name = "header-namespace.tex";
    }
    if (is_readable(PLUGIN_TEXIT_CONF.$header_name)) {
      return PLUGIN_TEXIT_CONF.$header_name;
    }
    return false;
  }

  function getCommandsFN() {
    // first we look through nsbpc
    $found = $this->nsbpc->getConfFN("texit-commands", $this->ns);
    if ($found) {
      return $found;
    }
    // No nsbpc configuration was found, now looking in the conf/ directory of
    // the plugin.
    if (is_readable(PLUGIN_TEXIT_CONF."commands.tex")) {
      return PLUGIN_TEXIT_CONF."commands.tex";
    }
    return false;
  }

 /* This function returns an array of all IDs of pages to be rendered by TeXit.
  *
  */
  function get_all_IDs() {
    $global $conf;
    $list = array();
    if ($this->namespace_mode) {
      $opts = array('listdirs'  => false,
                    'listfiles' => true,
                    'pagesonly' => true,
                    'depth'     => 1,
                    'skipacl'   => false, // to check for read right
                    'sneakyacl' => true,
                    'showhidden'=> false,
                    );
      // we cannot use $opts in search_list or in search_namespaces, see
      // https://bugs.dokuwiki.org/index.php?do=details&task_id=2858
      search($list,$conf['datadir'],'search_universal',$opts,$this->id);
      return $list;
    } else {
      return array(array('id' => $this->id));
    }
  }

 /* Returns an array with base and destination filenames. Works with full paths.
  */
  function get_all_files() {
    // this gives us all the page ids that need txt->tex conversion:
    $id_array = $this->get_all_IDs();
    $result = array();
    // now we put them all in the $result array
    // TODO
    // and we add the header and command
    // TODO
  }
  
 /*
  * This functions returns true if $base is more recent that $dest, and
  * false otherwise.
  *
  */
  function needs_update($base, $dest) {
    return filemtime($base) > filemtime($dest);
  }
  
 /* This function sets the TeX compilation environment up by copying the files
  * in the good folders and renames them.
  */
  function setup_files() {
    $filenames = $this->get_all_files();
    foreach($filenames as $base => $dest) {
      list ($type, $destfn) = $dest;
      if ($this->needs_update($base, $destfn)) {
        switch($type) {
          case "header":
            $this->compile_header($base, $destfn);
            break;
          case "commands":
            $this->simple_copy($base, $destfn);
            break;
          case "tex":
            $this->compile_tex($base, $destfn);
            break;
          default:
            break;
        }
      }
    }
  }
  
}

?>
