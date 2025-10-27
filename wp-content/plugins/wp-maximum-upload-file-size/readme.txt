=== EasyMedia - Increase Media Upload File Size | Increase Execution Time ===
Contributors: codepopular, shamimtpi, rajubdpro
Tags: increase upload limit,increase file size limit,large file upload,easymedia,max upload file size
Donate link: https://ko-fi.com/codepopular
Requires at least: 3.0
Requires PHP: 7.0
Tested up to: 6.8
Stable tag: 3.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

EasyMedia - Increase maximum upload file size limit to any value. Increase upload limit - upload large files effortlessly.


== Description ==

### **Increases the upload file size limit to any value with one click**
EasyMedia automatically detects your WordPress and server upload limits, displaying them in a simple dashboard. Instantly increase your upload size and easily upload large files like backups, videos, or ZIP archives directly to your WordPress media library — even if your hosting restricts upload limits.

Access the plugin's settings from the main WP admin menu. Media -> EasyMedia

You can increase the maximum upload file size and WordPress maximum execution time. Some time extended maximum execution time is needed when uploading large attachments. With this plugin you can simply avoid this problem and manage your media uploads efficiently.

### **Where to find Option to Increase Upload Limit.**
After installing and activating the plugin, go to your dashboard and navigate to Media > EasyMedia. From there, you can easily adjust your upload limits and execution time settings.

### **Increase Maximum Execution Time.**
Sometimes WordPress users can't upload new files due to extended execution time limits. With this plugin, you can increase the execution time to avoid these issues. Simply set the execution time in the input field according to your needs.

### **Plugin Renamed: MaxUploader is now EasyMedia** ###
We’ve rebranded MaxUploader to EasyMedia for a better, more user-friendly experience.
Don’t worry — all your existing settings will remain intact. The plugin continues to provide the same core functionality, including increasing upload file size limits and execution time, with an improved interface and additional features.

If you previously used MaxUploader, you can safely update to EasyMedia — it’s the same plugin, just with a new name and enhanced design.


### **🚀 Upgrade to EasyMedia Pro for Advanced Features**

Take your media management to the next level with [EasyMedia Pro](http://codepopular.com/product/easymedia)!

**Pro Features Include:**

* **📊 Upload Logs & Tracking** - Monitor all file uploads with detailed logging including user, file name, size, type, and timestamp. With EasyMedia Pro upload logs module you can see how many source has for a single attachment. Some time WordPress site user got confused to detect how many blog post or page has used the picture. We provide the source list so you can easy manage and update it.

* **👥 User-Based Upload Limits** - Set individual upload limits for specific users that override global settings. With this module you can specially set the disk limit per individual user. This way we can handle over upload issue from Editor and Author

* **🎭 Role-Based Restrictions** - Configure different upload limits based on WordPress user roles (Administrator, Editor, Author, etc.) This module can help to define the upload limit per role which WordPress by default is not comes

* **📁 Media Manager** - Display file sizes directly in media library columns and attachment details. This feature is acting as a File manage plugin where you can manager your WordPress site all files and folder.

* **📈 Upload Statistics Dashboard** - View comprehensive statistics including top uploads, recent uploads, and overall usage. You can see graphical interface of your media library and top uploader list.

* **🔍 Advanced Reporting** - Export upload logs and generate detailed reports for analysis

* **⚡ Auto-Install Dependency** - Automatically installs and configures the main plugin if needed

* **🛡️ Better Security** - Track who uploads what and when for improved security and accountability

* **🎯 Priority Support** - Get faster support and regular updates. We focus one to one priority support.

[**Get EasyMedia Pro Now →**](http://codepopular.com/product/easymedia) [**Get Read Documentation →**](https://codepopular.com/docs/easymedia)


= Recommended Elementor Plugin =

> * [Unlimited Theme Addons](https://wordpress.org/plugins/unlimited-theme-addons/)


== Installation ==

The usual, automatic way:

1. Open WordPress admin, go to Plugins, click Add New
2. Enter "EasyMedia" or "MaxUploader" or "Maximum upload file size" in search and hit Enter
3. The Plugin will show up in the list, click "Install Now"
4. Activate & open the plugin's settings page located in the main admin menu

Or if needed, install manually:

1. Download the plugin.
2. Unzip it and upload to _/wp-content/plugins/_
3. Open WordPress admin - Plugins and click "Activate" next to plug in
4. Activate & open the plugin's settings from Media > EasyMedia.


== Screenshots ==
1. Admin Panel for maximum upload file size.
2. System status dashboard showing current limits.


== Changelog ==

= 3.0.1 =
-------------
* PHP 7.0 to 8.3 support
* Pro Extension offer to extend features

= 2.0.2 =
-------------
* Allow to change the memory limit.
* Latest feed display in the cache to make it faster.
* Rebranded to EasyMedia for better user experience.

= 2.0.1 =
-------------
* Overwrite PHP ini file.
* Show detailed info in system status.
* Improve UI
* Offer a premium version.

= 2.0.0 =
-------------
* Major UI improvements.
* Enhanced system status display.
* Added premium version with advanced features.

= 1.1.7 =
-------------
* Tracking usage data allow/disallow issue fixed.

= 1.1.6 =
-------------
* Security issue fixed.

= 1.1.5 =
-------------
* Warning fixed in WP version 6.7

= 1.1.4 =
-------------
* Text domain fixed.
* Code structure updated.

= 1.1.2 =
-------------
* PHP 8 compatibility checked.
* Allow to upload 10GB file size.

= 1.1.1 =
-------------
* PHP 8 compatibility checked.
* WordPress latest version 6 compatibility checked.

= 1.1.0 =
-------------
* WordPress latest version 6 compatibility checked.
* Allow to Upload File Size 3GB, 4GB, 5GB.

= 1.0.9 =
-------------
* WordPress latest version 6 compatibility checked.

= 1.0.8 =
-------------
* Footer text issue fixed in admin page.
* Header Notification removed from plugin setting page.

= 1.0.7 =
-------------
* WordPress latest version 5.9 compatibility added.
* New value added in dropdown to upload maximum 2GB.

= 1.0.6 =
-------------
* WordPress latest version 5.8 compatibility added.

= 1.0.5 =
-------------
* Maximum Execution Time Increase Option Added.

= 1.0.4 =
-------------
* System Status Added.
* WordPress latest version 5.7 compatibility checked.

= 1.0.3 =
-------------
* WordPress latest version 5.6 compatibility checked.

= 1.0.2 =
-------------
* WordPress latest version 5.5 compatibility checked.

= 1.0.1 =
-------------
* Test up to WordPress 5.4 latest version

= 1.0.0 =
-------------
* Initial release


== Frequently Asked Questions ==

= Does this plugin work with all servers and hosting providers? =

Yes, it works with all servers. However, please note that server-adjusted limits can't be changed from a WordPress plugin. If the server set limit is 16MB you can't increase it to 128MB via WordPress. But that case we chunk the large uploaded file in small peace as a refection upload time can be slower. But its possible to upload big file than your server set upload limit. Install the plugin and it'll tell you what the limits are and what to do.

= Increase upload file size but still not working? =

If minimum upload limit is set by hosting provider then it will not work. Ask your hosting provider to increase upload size.

= Increase maximum execution time but not working? =

Usually we upload large by chunking to small size but if your WordPress upload directory is protected, then we can't create chunk directory. Try to create a support ticket we will try to investigate the issue to fix it

= What's the difference between free and pro versions? =

The free version allows you to increase upload limits and execution time. The pro version adds advanced features like upload logging, user-based limits, role restrictions, statistics dashboard, and enhanced media library display. [Learn more about EasyMedia Pro](http://codepopular.com/product/easymedia)

= Can I upgrade from free to pro? =

Yes! You can upgrade anytime. The pro version works alongside the free version and adds additional features without affecting your current settings.

= Do I get support with the free version? =

Yes, we provide community support for the free version. Pro users get priority support with faster response times.
