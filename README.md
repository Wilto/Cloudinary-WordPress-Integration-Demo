# Cloudinary Integration #
**Contributors:** joemcgill  
**Tags:** media, images  
**Requires at least:** 4.5  
**Tested up to:** 4.6.1  
**Stable tag:** 0.1.0  
**License:** GPLv2 or later  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.html  

This plugin is an experiment to demonstrate integrating <a href="http://cloudinary.com/">Cloudinary</a> with WordPress for generating responsive images. For more, read <a href="https://css-tricks.com/responsive-images-wordpress-cloudinary-part-1/">the related article at CSS-Tricks</a>.

## Changelog ##

 0.1.0 - Initial demo

---

This is a fork of https://github.com/joemcgill/Cloudinary-WordPress-Integration-Demo

### Getting Started
`npm install`, and run `grunt uglify` to generate the minified `IntersectionObserver` script.

### Wiring it up to Cloudinary

Add the following lines to your `wp-config.php`:

```php
/* Cloudinary Plugin Credentials */
define( 'CLD_CLOUD_NAME', '' );
define( 'CLD_API_KEY', '' );
define( 'CLD_API_SECRET', '' );
```

Fill those in with the information you find at the top of your Cloudinary dashboard:

![Screenshot of the Cloudinary dashboard landing page, focused on the Cloud Name, API Key, and API Secret fields](http://wil.to/cloudinary-dash.jpg)