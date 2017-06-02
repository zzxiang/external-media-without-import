=== External Media without Import ===
Contributors: zzxiang
Tags: remote media, remote URL, remote image, remote file, external media
Tested up to: 4.7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0-standalone.html

Add external images to the media library without importing, i.e. uploading them to your WordPress site.

== Description ==

By default, adding an image to the WordPress media library requires you to import or upload the image to the WordPress site, which means there must be a copy of the image file stored in the site. This plugin enables you to add an image stored in an external site to the media library by just adding a URL linking to the remote image address. In this way you can host the images in a dedicated server other than the WordPress site, and still be able to show them by various gallery plugins which only take images from the media library.

The plugin provides buttons and inputs in the 'Media' -> 'Add New' page, the media upload panel and a dedicated Add External Media without Import submenu page. Therefore you can either add an external media before (or after) editing any post or page, or in the process of editing a post or page without interrupting the editing process.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/external-media-without-import` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Add external media. You can do this in either of the following ways:
	* In the 'Media' -> 'Add New' page, click the 'Add External Media without Import' button in the upload panel. The 'Add a media from URL' panel will appear, or your are directed to the 'Add External Media without Import' submenu page, depending whether your media library page is in grid mode or list mode. Fill the URL in the input field and click the 'Add' button, the remote image will be added.
	* Click the 'Media' -> 'Add External Media without Import' submenu, you will be directed to the submenu page. Fill the URL in the input field and click the 'Add' button, the remote image will be added.
	* During the process of editing a post or page, click 'Add Media' -> 'Upload Files', and in the upload panel click 'Add External Media without Import'. The 'Add a media from URL' panel will appear, or your are directed to the 'Add External Media without Import' submenu page, depending whether your media library page is in grid mode or list mode. Fill the URL in the input field and click the 'Add' button, the remote image will be added.
4. WordPress needs to know in advance the width and height of an image in order to correctly display it in the media library page and any post/page. In most cases, the plugin will get these properties automatically without worrying you. But in rare cases, the plugin may fail to get the width and height of the image you specify when you click 'Add' in the 'Add a media from URL' panel. In that case, some input fields will show up and let you fill in the properties manually.
