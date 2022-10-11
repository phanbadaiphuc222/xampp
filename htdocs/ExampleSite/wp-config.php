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
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ExampleSite' );

/** Database username */
define( 'DB_USER', 'ExampleSite' );

/** Database password */
define( 'DB_PASSWORD', 'daiphucprohehe' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3307' );

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
define( 'AUTH_KEY',         '?[#X1gM 9@jnW^!O9$ aROLB0[QQ8Y~4V}yy4K;+*ICc6dp)dM+z<,*u=d=mfl(,' );
define( 'SECURE_AUTH_KEY',  ';TMzdKKMZ%B,[ktEC>}h[i&r-B&k>86):2-QkZ]~+{OuoDz1#V{tD;Li(q]!ac?^' );
define( 'LOGGED_IN_KEY',    '(Te viVX.}C!|-HA+JS^UV;[NMlGrp92>A1Ht*Jq7v9*j{1~<Fp9 *G>I[&]#t&+' );
define( 'NONCE_KEY',        '8eM[?[x%^&:-QHAgkr2jukouNJl ?^chXdivYCBBx#wj<sHz6m 4Fcvg$A/3(3*Q' );
define( 'AUTH_SALT',        'E>}===(J%28xX=7;Pd9n(l]Br9Nb/f#?rjv49L;jUNw-)%YEq?pt1DT(o!.0AKC^' );
define( 'SECURE_AUTH_SALT', 'tM3z{6`SbL K.c~0F(/kEWC+$TeRpuABh`P+_~{2P_$ovU$N_V10gu}^.H}VA/8%' );
define( 'LOGGED_IN_SALT',   'SUd0T#-Qt2ta#tPbt;2@lD/8;@vDkHRDA[6miMv9@e3~y<|(dteyS{)c5jO+,$,{' );
define( 'NONCE_SALT',       '96uk1+D10J@v1j9Tm(^xt.e~cJ!m3}f<Q}{{i4$m5)s0=<H)p2sWQnCisq{q+j^k' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
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

