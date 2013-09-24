#
# Makefile to publish a Dokuwiki plugin 
# Copyright (C) 2013 Elie Roux <elie.roux@telecom-bretagne.eu>
#
# This library is free software; you can redistribute it and/or
# modify it under the terms of the GNU Lesser General Public
# License as published by the Free Software Foundation; either
# version 2.1 of the License, or (at your option) any later version.
#
# This library is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# Lesser General Public License for more details.
#
# You should have received a copy of the GNU Lesser General Public
# License along with this library; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
# --------------------------------------------------------------------
#


NAME = texit
FILES = syntax.php admin.php texitrender.php latex.php class.texitimage.php class.texitconfig.php README LICENSE.GPLv2 plugin.info.txt
DIRS = conf/ lang/

all : tgz zip

zip: $(FILES) $(DIRS)
	@echo "Building zip file..."
	@zip -rq $(NAME).zip --exclude \*~ -- $(FILES) $(DIRS)

tgz: $(FILES) $(DIRS)
	@echo "Building tgz file..."
	@tar -czf $(NAME).tgz --exclude=\*\*/\*~ -- $(FILES) $(DIRS)

clean: 	
	rm -rf $(NAME).tgz $(NAME).zip

