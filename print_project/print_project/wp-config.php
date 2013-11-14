<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'printproject');

/** MySQL database username */
define('DB_USER', 'printproject');

/** MySQL database password */
define('DB_PASSWORD', 'Printproject@1');

/** MySQL hostname */
define('DB_HOST', 'printproject.db.11647461.hostedresource.com');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'iXK,aSD&`6.]bZVaqm0iBiNmo[@;m=?527n}4pJP:jF;->*Phme*#O`6)*fU+1)9');
define('SECURE_AUTH_KEY',  '&F;F{gLV<xg/jJHa_(1y`wEN[F&S[%k*$tl^-sI)[-BKLHhdI#75NHd+nVjTn~#z');
define('LOGGED_IN_KEY',    '>mj;95l|yo8Me3](f{JJ^kc26uk-B8H%xg|!Zap-Ib-6HR@2Fd0bizN!p>yvj$Ra');
define('NONCE_KEY',        'sv+E?hp5RGzRvF}Ak(a$M8)8.3H/;Z~=@^&v`:6J!A 5}:=XI|14X/t+#(2^>eht');
define('AUTH_SALT',        'q qqA0UT]yoSJ!cLE?j,v-{#2iePD-SYua#5E+~5oMfVcapEWl>1Y?|3hh0~vIG!');
define('SECURE_AUTH_SALT', 'SsVQFXh~7ejTLgu0`fo~#:DmxmJc|2A-^y.V~Una|_wFK#pGt|jMd26S ~0#AV>W');
define('LOGGED_IN_SALT',   ')TW5M ]H]WJA|N&n0h:3-=,cvik+KPD*MxDL+-m%bZ@-&q8% |%xcBb/x9h|~T`+');
define('NONCE_SALT',       '5EY/Cih}akp)V$=mX|?j=bA,5}]v+0j#@S~HSePV5)km25K;C5o5B)QqxDr2NPI&');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
