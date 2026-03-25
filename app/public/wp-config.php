<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
// Local (Flywheel) MySQL socket (ensures CLI access in sandboxed environments)
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'od,#;J8?6_bsI*1(Z-G5V*y%6Iw:3$$Nq95EOonSFSYCba1d|:jhp#pK&mB^!kx-' );
define( 'SECURE_AUTH_KEY',   '+S5SaR`YegV)5Viv=*$=R,g/9NZ?#&;)yd0[|a)pg*H7CIANNyy)$vL,AA)2!=)J' );
define( 'LOGGED_IN_KEY',     ']@0!0^T.SD?&?4)[3$&+l,imJ~tZ.?5Ef.m|(QTBF)@<fQR7k<eV[/ib75WvfENB' );
define( 'NONCE_KEY',         '7EAA$50dBdk@VfJaf!NLvw9{C!g$v}0/;)mnBRENT:T60YQWqs`.{s`xK0QDU2{%' );
define( 'AUTH_SALT',         'sgZ9J}/$v_e96Ls7{)/#$H<g]rYyvI<vX(Z5L@02EP}F6tL53_,ho<&JTbi`.=d)' );
define( 'SECURE_AUTH_SALT',  '3X:<Qtg{>13FlM66i!GTth%cR%gm5L`5m/gv~pl=rjCnjr}])vz8(2-Q+&Pf=@V2' );
define( 'LOGGED_IN_SALT',    '1.43AK>a#63@1,IRt1bQYUQ,UrWvW7<=$]sZ_T$MW[hiZ^3+6}MVyHS;~jSnx<E#' );
define( 'NONCE_SALT',        'B5i2[+|7 m1b`co|9iaWx:F[R>?_7#CCSaH9k`|SPg%+2)vjS,n|O(02/^6`dt|i' );
define( 'WP_CACHE_KEY_SALT', 'k4}=nj!o{x6-[.z17Ge%/ #6R/% IMdV:dU_#Y/zo7TLQBiFlbd^L?3]xiSSvwJF' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
// Enable WP_DEBUG mode
define( 'WP_DEBUG', true );

// Enable Debug logging to the /wp-content/debug.log file
define( 'WP_DEBUG_LOG', true );

// Disable display of errors and warnings
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
