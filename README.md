# BookWorm
BookWorm is a private, low-key web app that helps you keep track of the books you've read, what you thought about them, and what you'd like to read.

## Description
This package is a custom WordPress theme. BookWorm uses the Google Books API to search books and pull book metadata. Users are standard WordPress users at the Subscriber level, and we use a custom DB table to manage books and capture user-specific book metadata tied to unique user IDs. There are custom friend and book recommendation features. Some user account management is handled with the WP User Manager plugin, customized in the `/wpum` folder in the theme. `analytics.php` uses the Gemini API to generate a summary of users' book notes. Most of the custom functions can be found in `wp-ajax.php`. 

## Requirements
* PHP 7.4+
* WordPress 6.7+
* Advacned Custom Fields plugin (latest)
* WP User Manager plugin (latest)

## Acknowledgements
Thought leadership and design by [Madhurima Chakraborty](https://madhurimachakraborty.net) (this whole thing is Madhurima's baby!). Datepicker functionality and UI from [Air Datepicker](https://air-datepicker.com/).
