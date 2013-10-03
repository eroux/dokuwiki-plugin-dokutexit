##TeXit plugin for Dokuwiki

This is a cloned repository of the [Dokuwiki TeXit plugin], the original website
was http://danjer.doudouke.org/tech/dokutexit, but the original author has
disapeared, as well as the whole doudouke.org domain (on which he had his mail
address).

### Changes

This version comes with a set of updates and changes:
 * name is now texit instead of dokutexit
 * code refactoring, simplifying and cleaning
 * documentation
 * integration with [nsbpc], simplification of configuration files
 * update of TeX template, LuaTeX and XeTeX integration
 * use of latexmk instead of manual compilation (thus working with BibTeX, indexes, etc.)
 * produced pdf and zip are now in the media namespace
 * possibility to export a whole namespace
 * less fancy exports (no ugly background link)
 * integration with [refnotes] for bibliography (only BibTeX entries)

### Configuration

Configuration is organized this way:

##### Usual plugin config

 * *conf/defaults.php* (or the configuration manager) holds the global configuration, where you can choose several things, such as the default renderer (LaTeX+dvipdf, pdfLaTeX, XeLaTeX or LuaLaTeX)
 * *conf/header-namespace.tex* is the header which will be included when a whole namespace will be exported
 * *conf/header-page.tex* is the same for one page export
 * *conf/commands.tex* is where the TeX macros for dokuwiki styles will be held (this is common for namespace and page)
 
##### NSBPC plugin config

You can use [nsbpc] to have per-namespace (and thus per-language) configuration. The configuration pages will be:
 * *nsbpc_texit-namespace* overriding *conf/header-namespace.tex*
 * *nsbpc_texit-page* overriding *conf/header-page.tex*
 * *nsbpc_texit-commands* overriding *conf/commands.tex*

### Output files

When clicking on the export button, the plugin will compute an .zip file containing all necessary TeX files as well as the produced PDF.

The output for page *namespace:subnamespace:id* will be placed in *media:namespace:subnamespace:id-texit.zip*, and the output for namespace *namespace:subnamespace* will be placed in *media:namespace:subnamespace:subnamespace-texit.zip*.

These archives will contain a *subnamespace-date* directory, in which files will be named *namespace_subnamespace_id.tex* (and *.pdf*).

The *commands.tex* file will appear separately (as *texitcommands.tex*). If the export is just one page, the file containing the content of the page will be *namespace_subnamespace_id-content.tex*. In the case of a namespace export, each page will appear as a *.tex* file with the same name convention, and the main TeX file including the others will be called *namespace_subnamespace.tex*.

### CMK integration

Note that you can define custom markups with [cmk].

### Documentation

There is a documentation in help/, but it seems Dokuwiki doesn't allow plugins
to install docs, so you'll have to install it by yourself (you can, for
instance, follow the link in the administration page). What seems to me as
the most elegant solution is to create your help pages in the
manual:pluginsmanual namespace and to add 

    ====== Plugins ======
    
    {{nstoc :fr:wiki:pluginsmanual 2}}

in *manual/start*. Feel free to do otherwise.

### Nice sidebar buttons

Sidebar buttons are really painful to add! The normal way would be to add your images in `lib/tpl/<yourtemplate>/images/pagetools` and to call `lib/tpl/<yourtemplate>/images/pagetools-build.php`. The problem is that this script uses the `imagelayereffect()` php function from php-[gd]. This library has [human issues][gdpb], and Debian doesn't ship a php-gd library with this function (see [here][gdpbdeb]). So if you have a Debian server, the only way to make it work is to either hack your gd library, or do what the `pagetools-build.php` does by hand.

If you do it by hand, basically you have to merge images from the `pagetools` directory (coming from the [retina icon set][retina]) in the `lib/tpl/<yourtemplate>/images/pagetools-sprite.png`. To do so, first duplicate them, with one version in grey and the other in blue, and apply them a gradiant. Then stack them vertically, each 45px, in the `pagetools-sprite.png`.

Once you have a good `pagetools-sprite.png`, then you can change your template this way:

##### actions

All these buttons will be associated with actions, and thus with [action plugins][actionplugins]. If you just want to test the button adding, before developping your action plugin, you can put some dumb values (that you'll have to remove later). To do so, you can follow the instructions on [pdfexport plugin doc][pdfexport], section *Dokuwiki-template: Export Link in Pagetools*.

##### css

Add this at the end of `lib/tpl/<yourtemplate>/css/pagetools.css`:

```css
#dokuwiki__pagetools ul li a.<myaction> {
    background-position: right -1090px;
}
#dokuwiki__pagetools ul li a.<myaction>:before {
    margin-top: -1090px;
}
#dokuwiki__pagetools ul li a.<myaction>:hover,
#dokuwiki__pagetools ul li a.<myaction>:active,
#dokuwiki__pagetools ul li a.<myaction>:focus {
    background-position: right -1135px;
}
```

replacing `<myaction>` by the name of your action. Add it for each button you want in the pagetools bar, adding each time 45px to the values.

### License

The plugin seems to be under GPLv2+ license, which I'll assume.

### Requirements

The plugin supposes you have a recent TeXLive installation (it assumes 2013, but
it should have almost no problem with 2012 version or with MikTeX), with 
latexmk.

Not that you need to install imagemagick for image conversion.

It has only been tested on a recent dokuwiki (Release 2013-05-10a "Weatherwax").


[Dokuwiki TeXit plugin]: https://www.dokuwiki.org/plugin:dokutexit
[nsbpc]: https://github.com/eroux/dokuwiki-plugin-nsbpc
[gd]: http://en.wikipedia.org/wiki/GD_Graphics_Library
[gdpbdeb]: https://bugs.launchpad.net/ubuntu/+source/php5/+bug/74647
[retina]: http://blog.twg.ca/2010/11/retina-display-icon-set/
[actionplugins]: https://www.dokuwiki.org/devel:action_plugins
[pdfexport]: https://www.dokuwiki.org/tips:pdfexport#dokuwiki-templateexport_link_in_pagetools
[refnotes]: https://www.dokuwiki.org/plugin:refnotes
[cmk]: https://github.com/eroux/dokuwiki-plugin-cmk
