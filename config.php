<?php
/**
 * texit multifunction Class
 * Copyright (C) 2013   Elie Roux <elie.roux@telecom-bretagne.eu>
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
 *
 */
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
if(!defined('PLUGIN_TEXIT')) define('PLUGIN_TEXIT',DOKU_PLUGIN.'texit/');
if(!defined('PLUGIN_TEXIT_CONF')) define('PLUGIN_TEXIT_CONF',PLUGIN_TEXIT.'conf/');
require_one(PLUGIN_TEXIT.'texitrender.php');

class texit_config_plugin_texit {
  var $id;
  var $ns;
  var $namespace_mode;
  var $nsbpc;
  var $conf;
  var $mediadir;
  var $texitdir;
  var $prefix;
  var $all_files;
  var $texit_render_obj; // not initialized by constructor, done only if needed
 /*
  * I didn't use a helper plugin because I needed a constructor.
  *
  */
  function __constructor($id, $namespace_mode, $conf) {
    $this->id = cleanID($id);
    $this->ns = getNS(cleanID($id));
    $this->namespace_mode = $namespace_mode;
    $this->nsbpc = loadHelper('nsbpc');
    $this->conf = $conf;
    $this->prefix = $this->set_prefix();
    $this->_set_texit_dir();
    $this->_set_media_dir();
    $this->get_all_files();
  }

  function set_prefix() {
    if (!$this->conf['use_prefix']) {
      $this->prefix = '';
      return;
    } else {
      $this->prefix = $this->conf['pre_prefix'];
      $this->prefix .= ':'.$this->ns;
      if ($this->conf['prefix_separator']) {
        str_replace(':', $this->conf['prefix_separator'], $this->prefix);
      } // else we keep it this way
      $this->prefix .= $this->conf['prefix_separator'];
    }
  }

  function _create_dir($path) {
    $path = init_path($path);
    if(empty($path)) {
      // let's create it, recursively
      $res = io_mkdir_p($path);
      if(!$res){
        nice_die("Unable to create directory $path, please create it.");
      }
    }
  }

// This function escapes a filename so that it doesn't contain _ character:
  function _escape_fn($fn) {
    return str_replace('_', '-', $fn);
  }

  function _set_media_dir() {
    global $conf;
    $path = $conf['mediadir'];
    $path .= '/'.str_replace(':','/',$this->ns);
    // taken from init_paths in inc/init.php
    $this->_create_dir($path);
    $this->mediadir = $path;
  }
  
  function _set_texit_dir() {
    $path = $this->conf['texitdir'];
    // taken from init_paths in inc/init.php
    $path = empty($path) ? $conf['savedir'].'/texit' : $path;
    $path .= '/'.str_replace(':','/',$this->ns);
    $this->_create_dir($path);
    $this->texitdir = $path;
  }
  
  function get_zip_fn() {
    return $this->mediadir.'/'.$this->get_common_basename().".zip";
  }
  
  function get_pdf_media_fn() {
    return $this->mediadir.'/'.$this->prefix.$this->get_common_basename().".pdf";
  }
  
  function get_pdf_texit_fn() {
    return $this->texitdir.'/'.get_common_basename().".pdf";
  }



 /* This returns 'all' if in namespace-mode, or the escaped ID, without extension.
  *
  */
  function get_common_basename() {
    if ($this->namespace_mode) {
      return all;
    } else {
      return $this->_escape_fn(noNS($this->id));
    }
  }

  /* This returns the full path of the entities.cfg config file. Not searched
   * through nsbpc.
   */
  function get_entities_fn() {
      return PLUGIN_TEXIT_CONF.'entities.cfg';
  }

  /* This returns the full path of the base header file we take as reference
   * for this compilation. In case nothing is found, false is returned.
   */
  function get_base_header_fn() {
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

  /* This returns the full path of the header file we want in the destination
   * texit namespace.
   */
  function get_dest_header_fn() {
    if ($this->namespace_mode) {
      return $this->texitdir."/all.tex";
    } else {
      return $this->texitdir.$this->get_common_basename().".tex";
    }
  }
  /* This returns the full path of the base coommands file we take as reference
   * for this compilation.
   */
  function get_base_commands_fn() {
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
  /* This returns the full path of the commands file we want in the destination
   * texit namespace.
   */
  function get_dest_command_fn() {
    return $this->texitdir."/commands.tex";
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
  *
  * The returned array has the following structure:
  *    [base] => (type, fn)
  * where:
  *  * base is the base filename (like /path/to/dkwiki/pages/ns/id.txt)
  *  * type is either "header", "commands" or "tex"
  *  * fn is the absolute destination filename (prefix included)
  */
  function get_all_files() {
   // this gives us all the page ids that need txt->tex conversion:
   $id_array = $this->get_all_IDs();
   $result = array();
   // now we put them all in the $result array
   foreach($id_array as $value) {
     if (!is_array($value) || !$value['id']) { // I did'nt find any more elegant way to do so
       continue;
     }
     $fn = wikiFN($this->id);
     $dest = $this->texitdir.noNS($value['id'])."-content.tex";
     $dest = $this->_escape_fn($dest);
     $result[$fn] = array('type' => 'tex', 'fn' => $dest);
   }
   // and we add the header and command
   $base = $this->get_base_header_fn();
   if (!$base) {
     nice_die("TeXit: Unable to find a header file!");
   }
   $result[$base] = array('type' => 'header', 'fn' => $this->get_dest_header_fn());
   $base = $this->get_base_commands_fn();
   if (!$base) {
     nice_die("TeXit: Unable to find a commands file!");
   }
   $result[$base] = array('type' => 'commands', 'fn' => $this->get_dest_commands_fn());
   $this->all_files = $result;
  }

 /* This function takes three arguments:
  *    * base is the full path of the base header file
  *           (for instance /path/to/dkwiki/lib/plugin/texit/conf/header-page.tex)
  *    * dest is the full path of the destination header file
  *    * all_files is the table returned by get_all_files()
  *
  * It reads $base, adds \input lines for $all_files and writes the result in
  * $dest.
  */
  function compile_header($base, $dest, $all_files) {
    // first we simply copy the file
    $this->simple_copy($base, $dest);
    // we prepare a string to append at the end:
    $toappend = '\n';
    foreach($this->all_files as $value) {
      $toappend .= '\dokuinclude{'.basename($value['fn']).'}\n';
    }
    $toappend .= '\n\end{document}';
    // the we open it in append mode to write things at the end:
    $fh = fopen($dest, 'a') or die("can't open file");
    fwrite($fh, $toappend);
    fclose($fh);
  }

 /* This function takes two arguments:
  *    * base is the full path of the base page file
  *           (for instance /path/to/dkwiki/data/pages/ns/id.txt)
  *    * dest is the full path of the destination tex file
  *
  * It reads $base, renders it into TeX and writes $dest.
  */
  function compile_tex($base, $dest) {
    if (!$this->texit_render_obj)
      {
        $this->texit_render_obj = new texitrender_plugin_texit($this);
      }
    $this->texit_render_obj->process($base, $dest);
  }

 /* This function takes two arguments:
  *    * base is the full path of the base file
  *    * dest is the full path of the destination tex file
  *
  * It copies $base into $dest.
  */
  function simple_copy($base, $dest) {
    if (!copy($base, $dest)) {
      nice_die("TeXit: unable to copy $base into $dest.");
    }
  }

 /*
  * This functions returns true if $base is more recent that $dest, and
  * false otherwise.
  *
  */
  function _needs_update($base, $dest) {
    return filemtime($base) > filemtime($dest);
  }
  
 /* This function sets the TeX compilation environment up by copying the files
  * in the good folders and renames them. It uses file modification timestamps
  * to evaluate if files need to be recompiled or recopied.
  */
  function setup_files() {
    foreach($this->all_files as $base => $dest) {
      list ($type, $destfn) = $dest;
      if ($this->_needs_update($base, $destfn)) {
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
  
 /* This function calls latexmk with the good options on the good files.
  */
  function _do_latexmk() {
    chdir($this->get_texit_ns());
    $basecmdline = '';
    if (isset($this->conf['path']) 
      && trim($this->_texit_conf['latexmk_path']) != "") {
      $basecmdline = $this->_texit_conf['latexmk_path'] . DIRECTORY_SEPARATOR;
    } else {
      $basecmdline = '';
    }
    $cmdline = $basecmdline."latexmk -f ";
    switch ($this->_texit_conf['mode'])
    {
    case "latex":
      // TODO: test, comes from http://users.phys.psu.edu/~collins/software/latexmk-jcc/
      $cmdline .= "-e '\$dvipdf = \"dvipdfm %O -o %D %S\";' -pdfdvi "; 
      break;
    case "pdflatex":
      $cmdline .= "-pdf ";
      break;
    case "lualatex":
      $cmdline .= "-pdf -pdflatex=lualatex ";
      break;
    case "xelatex":
      $cmdline .= "-latex=xelatex -e '\$dvipdf = \"dvipdfmx %O -o %D %S\";' -pdfdvi ";
      break;    
    }
    $cmdline .= $this->dest_header . ' 2>&1 ';
    $log = @exec($cmdline, $output, $ret);
    if ($ret) {
      print($ret);
    }
    // at the end, we clean temporary files. There is currently no way to tell
    // latexmk to clean at the end of the compilation... quite a shame...
    // An email has been written to the author in this sense.
    $cmdline = $basecmdline."latexmk -c 2>&1";
    $log = @exec($cmdline, $output, $ret);
    if ($ret) {
      print($ret);
    }
  }

 /* This function zips the good files in the texit namespace in a .zip archive
  * in the media namespace.
  */
  function compile_zip() {
    $zipfn = $this->get_zip_name();
    // TODO: if the file already exists, remove it.
    if (@file_exists($zipfn)) {
      unlink($zipfn);
    }
    $zip = new ZipArchive();
    if ($zip->open($zipfn, ZipArchive::CREATE) !== true) {
      exit("Unable to create $zipfn\n");
    }
    // First argument of addFile is the absolute, second is the name we want
    // in the archive (in our case, the basename).
    $zip->addFile($this->get_pdf_texit_fn(), basename($this->get_pdf_texit_fn()));
    foreach($this->all_files as $base => $dest) {
      $zip->addFile($dest['fn'], basename($dest['fn']));
    }
    $zip->close();
  }
  
 /* My mind is too used to C programming and thus this is a bit too
  * iterative and not object-oriented enough...
  *
  * This function processes everything when the user asks for a PDF.
  */
  function process() {
    $this->setup_files();
    $this->_do_latexmk();
    // then copy the pdf to media
    $this->simple_copy($this->get_pdf_texit_fn(), $this->get_pdf_media_fn());
    $this->compile_zip();
  }
}

?>
