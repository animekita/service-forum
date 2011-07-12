from __future__ import with_statement

import os
import sys
import hashlib

from fabric.api import *
from fabric import utils

VFORUM_VERSION = '2.0.18.b2'
VFORUM_URL = 'http://vanillaforums.org/uploads/addons/A0WQWALCWMSM.zip'
VFORUM_TMP_FILE = '/tmp/vforum-%s.zip' % VFORUM_VERSION

PLUGIN_FILE_UPLOAD_VERSION = '1.4.4'
PLUGIN_FILE_UPLOAD_URL = 'http://vanillaforums.org/uploads/addons/72AMYWIGOUXK.zip'
PLUGIN_FILE_UPLOAD_TMP_FILE = '/tmp/vforum-plugin-file-upload-%s.zip' % PLUGIN_FILE_UPLOAD_VERSION

PLUGIN_WHOISONLINE_VERSION = '1.3'
PLUGIN_WHOISONLINE_URL = 'http://vanillaforums.org/uploads/addons/WA621F1NX5T0.zip'
PLUGIN_WHOISONLINE_TMP_FILE = '/tmp/vforum-plugin-whoisonline-%s.zip' % PLUGIN_WHOISONLINE_VERSION

PLUGIN_QUOTE_VERSION = '1.2'
PLUGIN_QUOTE_URL = 'http://vanillaforums.org/uploads/addons/A46J6T6GG5C8.zip'
PLUGIN_QUOTE_TMP_FILE = '/tmp/vforum-plugin-quote-%s.zip' % PLUGIN_QUOTE_VERSION

PLUGIN_ANALYTICS_VERSION = '1.0'
PLUGIN_ANALYTICS_URL = 'http://vanillaforums.org/uploads/Z2HHMBXN7OJH.zip'
PLUGIN_ANALYTICS_TMP_FILE = '/tmp/vforum-plugin-analytics-%s.zip' % PLUGIN_ANALYTICS_VERSION

def download(url, destination):
	if os.path.exists(destination):
		print('Download detected, skipping download')
	else:
		local('wget -O %s %s' % (destination, url), capture=False)

def build(config_file=None):
	"""
	Build forum service

	- Download vanilla forum
	- Apply addones
	"""

	print('Preparing build')

	local('rm -rf build')
	local('mkdir build')

	print('Downloading vanilla forum %s' % VFORUM_VERSION)

	download(VFORUM_URL, VFORUM_TMP_FILE)

	local('unzip %s -d build/' % VFORUM_TMP_FILE)

	local('mv build/vanilla/* build/')
	local('mv build/vanilla/.htaccess build/')
	local('rm -rf build/vanilla/')

	print('Downloading plugins')

	download(PLUGIN_FILE_UPLOAD_URL, PLUGIN_FILE_UPLOAD_TMP_FILE)
	local('unzip %s -d build/plugins/' % PLUGIN_FILE_UPLOAD_TMP_FILE)

	download(PLUGIN_WHOISONLINE_URL, PLUGIN_WHOISONLINE_TMP_FILE)
	local('unzip %s -d build/plugins/' % PLUGIN_WHOISONLINE_TMP_FILE)

	download(PLUGIN_QUOTE_URL, PLUGIN_QUOTE_TMP_FILE)
	local('unzip %s -d build/plugins/' % PLUGIN_QUOTE_TMP_FILE)

	download(PLUGIN_ANALYTICS_URL, PLUGIN_ANALYTICS_TMP_FILE)
	local('unzip %s -d build/plugins/' % PLUGIN_ANALYTICS_TMP_FILE)

	print 'Applying selvbetjening-sso addon'

	local('ln -s -f ../../addons/selvbetjening-sso/plugins/SelvbetjeningSSO build/plugins/SelvbetjeningSSO')

	print 'Applying kita-misc addon'

	local('ln -s -f ../../addons/kita-misc/plugins/KitaMisc build/plugins/KitaMisc')

	print 'Applying kita-theme addon'

	local('ln -s -f ../../addons/kita-theme/themes/kita build/themes/kita')

	print 'Applying kita-locale addon'

	local('ln -s -f ../../addons/kita-locale/locales/danish build/locales/danish')

	print 'Applying tinymce-plugin addon'

	local('ln -s -f ../../addons/tinymce-plugin/plugins/TinyMCE build/plugins/TinyMCE')

	print('Patching...')

	local('patch build/plugins/AllViewed/class.allviewed.plugin.php patches/mark-all-viewed/class.allviewed.plugin.patch')
	local('patch build/plugins/Analytics/default.php patches/google-analytics/insert-analytics-id.patch')

	local('patch build/plugins/FileUpload/views/link_files.php  patches/open-attachments-in-new-window/link_files.patch')
	local('patch build/plugins/FileUpload/views/link_files.php patches/remove-delete-upload/remove-delete-link.patch')
	local('patch build/plugins/FileUpload/js/fileupload.js  patches/fileupload-cleanup/integrate-with-tinymce-plugin.patch')

	local('patch build/plugins/cleditor/default.php  patches/cleditor-cleanup/reduce-options.patch')
	local('patch build/plugins/cleditor/default.php  patches/cleditor-cleanup/remove-duplicate-http.patch')

	local('patch build/library/core/functions.general.php patches/fix-minify/fix-minify.patch')

	local('patch build/applications/vanilla/models/class.commentmodel.php patches/bugfixes/fix-discussion-watched-logic.patch')

	local('patch build/plugins/Quotes/js/quotes.js patches/quotes-cleanup/integrate-with-tinymce-plugin.patch')

	local('patch build/plugins/TinyMCE/js/themes/advanced/skins/default/dialog.css patches/tinymce-cleanup/simplify-ui-dialog-css.patch')
	local('patch build/plugins/TinyMCE/js/themes/advanced/link.htm patches/tinymce-cleanup/simplify-ui-link.htm.patch')
	local('patch build/plugins/TinyMCE/js/themes/advanced/image.htm patches/tinymce-cleanup/simplify-ui-image.htm.patch')
	local('cp patches/tinymce-cleanup/simplify-ui-editor-template.js build/plugins/TinyMCE/js/themes/advanced/editor_template.js')
	local('patch build/plugins/TinyMCE/js/themes/advanced/editor_template_src.js patches/tinymce-cleanup/simplify-ui-editor-template-src.js.patch')

	print ('Setting permissions')

	local('chmod 770 build/uploads')
	local('chmod 770 build/cache')
	local('chmod 770 build/cache/Smarty/compile')

	if config_file is not None:
		print ('Copying config')

		local('cp %s build/conf/config.php' % config_file)
		local('chmod 777 build/conf/config.php')
