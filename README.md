##TeXit

This is a cloned repository of the [Dokuwiki TeXit plugin], the original website
was http://danjer.doudouke.org/tech/dokutexit, but the original author has
disapeared, as well as the whole doudouke.org domain (on which he has his mail
address).

### License

The plugin seems to be under GPLv2 license, which I'll assume.

### Requirements

The plugin supposes you have a recent TeXLive installation (it assumes 2013, but
it should have almost no problem with 2012 version or with MikTeX), with 
latexmk.

Not that you need to install imagemagick for image conversion.

It has only been tested on a recent dokuwiki (Release 2013-05-10a "Weatherwax").

### Documentation

There is a documentation in help/, but it seems Dokywiki doesn't allow plugins
to install docs, so you'll have to install it by yourself (you can, for
instance, follow the link in the administration page). What seems to me as
the most elegant solution is to create your help pages in the
manual:pluginsmanual namespace and to add 

    ====== Plugins ======
    
    {{nstoc :fr:wiki:pluginsmanual 2}}

in *manual/start*. Feel free to do otherwise

  [Dokuwiki TeXit plugin]: https://www.dokuwiki.org/plugin:dokutexit
