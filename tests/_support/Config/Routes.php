<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Config;

$routes->get('hello', 'Tests\Support\Controllers\Hello');
$routes->get('nohello', 'Tests\Support\Controllers\NoHello');
$routes->get('noaccess', 'Tests\Support\Controllers\NoAccess');
$routes->get('hellobauthbasic', 'Tests\Support\Controllers\HelloAuthBasic');
$routes->get('hellobauthbasicajax', 'Tests\Support\Controllers\HelloAuthBasicAjax');
$routes->get('hellobauthbearer', 'Tests\Support\Controllers\HelloAuthBearer');
$routes->get('hellobauthcustombearer', 'Tests\Support\Controllers\HelloAuthCustomBearer');
$routes->get('hellobauthsession', 'Tests\Support\Controllers\HelloAuthSession');
$routes->get('hellobauthdigest', 'Tests\Support\Controllers\HelloAuthDigest');
$routes->get('hellobauthlibrary', 'Tests\Support\Controllers\HelloAuthLibrary');
$routes->get('hellobauthlibraryerror', 'Tests\Support\Controllers\HelloAuthLibraryError');
