import os
import sys
import hashlib

from fabric.api import *
from fabric import utils

VFORUM_VERSION = '2.0.17'
VFORUM_URL = 'http://www.vanillaforums.org/uploads/addons/Q3HEV2BFTZFZ.zip'
VFORUM_TMP_FILE = '/tmp/vforum-%s.zip' % VFORUM_VERSION

PLUGIN_FILE_UPLOAD_VERSION = '1.4.0'
PLUGIN_FILE_UPLOAD_URL = 'http://vanillaforums.org/uploads/addons/ZO5QDNEU1ADP.zip'
PLUGIN_FILE_UPLOAD_TMP_FILE = '/tmp/vforum-plugin-file-upload-%s.zip' % PLUGIN_FILE_UPLOAD_VERSION

PLUGIN_WHOISONLINE_VERSION = '0.8'
PLUGIN_WHOISONLINE_URL = 'http://vanillaforums.org/uploads/addons/PCVRY64JRWNS.zip'
PLUGIN_WHOISONLINE_TMP_FILE = '/tmp/vforum-plugin-whoisonline-%s.zip' % PLUGIN_WHOISONLINE_VERSION

PLUGIN_QUOTE_VERSION = '1.2'
PLUGIN_QUOTE_URL = 'http://vanillaforums.org/uploads/addons/A46J6T6GG5C8.zip'
PLUGIN_QUOTE_TMP_FILE = '/tmp/vforum-plugin-quote-%s.zip' % PLUGIN_QUOTE_VERSION

def download(url, destination):
	if os.path.exists(destination):
		print('Download detected, skipping download')
	else:
		local('wget -O %s %s' % (destination, url), capture=False)

def _apply(item_type, item):
	print('Applying %s' % item)
	local('rsync -rt --exclude=.svn %s/%s/* build/' % (item_type, item))

def build(config_file=None):
	"""
	Build forum service

	- Download vanilla forum
	- Apply patches
	- Apply addones
	- Apply configuration templates
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

	print 'Applying addons'

	_apply('addons', 'selvbetjening-sso')

	print ('Setting permissions')

	local('chmod 770 build/uploads')
	local('chmod 770 build/cache')
	local('chmod 770 build/cache/Smarty/compile')

	if config_file is not None:
		print ('Copy config')

		local('cp %s build/conf/config.php' % config_file)
		local('chmod 777 build/conf/config.php')

	# link to profiles

	# deployment

	# locale
	# theme?


# lav deployment
# lav migration plan
# profiles
# better editor (optional)
# locale
# theme