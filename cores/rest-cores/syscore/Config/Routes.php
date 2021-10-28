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
$routes->setDefaultController('Tools');
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
$routes->add ('data/test', 'Tools::test');

$routes->get ('servertools/home', 'Tools::home');
$routes->get ('servertools/serverkey-generator', 'Tools::generator');
$routes->put ('servertools/serverkey-generator', 'Tools::generator');
$routes->put ('api/getlocale', 'Tools::localedata');
$routes->put ('api/getlocaledata', 'OfficerRequest::localedata');
$routes->put ('api/administration', 'OfficerRequest::serverSecurity');
$routes->put ('api/validation', 'OfficerRequest::officerValidation');
$routes->put ('api/administration-setup', 'OfficerRequest::officerValidation');
$routes->put ('api/dataaccess', 'OfficerRequest::dataRequest');
$routes->put ('api/forger', 'DataForgery::start_forgery');

$routes->put ('api/clientkey-verification', 'ClientRequest::keyAuthentication');
$routes->put ('api/client-authentication', 'ClientRequest::clientAuthentication');
$routes->put ('api/client-setup', 'ClientRequest::setupFirstTime');
$routes->put ('api/client-check', 'ClientRequest::dataCheck');

$routes->add ('client/api/request-data', 'ClientRequest::dataRequest');
$routes->post ('client/api/data-processing', 'ClientRequest::dataRequest');
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
