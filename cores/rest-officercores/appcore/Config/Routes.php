<?php namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Starter');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get ('{locale}/begin', 'Starter::index');
$routes->get ('{locale}/server/setup/saofficer', 'Starter::setup');
$routes->get ('{locale}/dashboard/do/login', 'Officer::do/login');
$routes->get ('{locale}/dashboard/do/logout', 'Officer::do/logout');
$routes->get ('{locale}/dashboard/data/(:any)', 'Officer::dashboard/$1');
$routes->post('{locale}/dashboard/do/loginprocess', 'Officer::do/process-login');
$routes->post ('{locale}/server/setup/initiate-administrator', 'Starter::setup');
$routes->post ('{locale}/server/setup/initiate-profiles', 'Starter::setup');
$routes->post ('{locale}/server/setup/process-setup', 'Starter::setup');
$routes->post ('{locale}/server/setup/saofficer', 'Starter::setup');
$routes->post ('{locale}/dashboard/data/(:any)', 'Officer::dataprocessing/$1');
$routes->put ('{locale}/api/get', 'Data::fetcher');
$routes->put ('{locale}/dashboard/data/(:any)', 'Officer::dataprocessing/$1');

/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
