<?php
// Here are the configrations that can be changed by the webmaster
// They are constants about layout, format, repository location, or web-technologie than can be enabled or disabled

define('MEMCACHE_HOST', 'localhost'); // Memcache server hostname (ex: 'localhost' or 'tcp://10.10.0.5'). Use FALSE to disable memcache.
define('MEMCACHE_PORT', 11211);       // Memcache server port (integer). Default port is 11211.
define('MEMCACHE_FAILOVER', false);   // Use session as failover for memcache values. (use this only if session and memcache are on separate servers)
define('MEMCACHE_LIVETIME', 600);     // Livetime (in seconds) of a memcache. Recommended: 600

// -----------------
// MEMCACHE
// -----------------
// Memcache allow storing frequently used values in server-cache (instead of runnning database sql requests)
// If memcache library is not available on your server use FALSE as host.
//
// See the about_ext.txt if you plan to use external server for memcache or sse 
