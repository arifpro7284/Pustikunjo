<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'Pustikunjo' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         '7>J-*|z^m]EP:N+..6&M-|zi:AzPo$/-*>Xb5%t5_rl~`^/a,3`iJETuj5Ty7hAX' );
define( 'SECURE_AUTH_KEY',  '%%6s@af`z:O/!LO8tu^@<$` O;I1FrcOXZr5$7KkO7AfA,ZR8]JxWNWSqlwg[Q:C' );
define( 'LOGGED_IN_KEY',    'A:|pn[ZEe]*z.J6Xhq#`vqax)0qc*GlztV/<Na#H!IjO*F:(Y!69}4T^/-+8$Iu2' );
define( 'NONCE_KEY',        'su>ozy*xU|x3?A>=_IDYw-V&By*]ltrI+h8ycc. EHJXc1FTZ7o:-g?~HNRXaCWS' );
define( 'AUTH_SALT',        'U[12)E45w}[|j<y5B2mJL8Cd<f7<l.BM]vQ~17#pdK=Bn:Ei_ZWwNp@z0qp5U()N' );
define( 'SECURE_AUTH_SALT', 'fC#fg|DX^vQ9atOjAgM},/v-JUEDul.3#7:Hh$t&%&cJzsR}>J{ME;ae8)InqpXK' );
define( 'LOGGED_IN_SALT',   'QB3FqLi!f?2G[O_~.xoShFe(~dRL TMO29edCjIlS4jka?.G;JN1>H<n*i-D!tp`' );
define( 'NONCE_SALT',       ',#fIL:3 NC s^]kq)n3MNLdJyVVA0jdXP)gcB+`uK7Hy5`coEQ0Ka(#^9Z,ZjvfY' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
