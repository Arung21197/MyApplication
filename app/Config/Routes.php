<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('Home', 'Home::index');
$routes->get('TestDB', 'TestDB::index');
$routes->get('orders', 'OrderController::index');
$routes->get('/checkout', 'CheckoutController::index');
$routes->get('orders/create', 'OrderController::createOrder');
$routes->post('orders/storeOrder', 'OrderController::storeOrder');
$routes->post('/checkout/proceedToPay', 'CheckoutController::proceedToPay');
$routes->post('/checkout/paymentCallback', 'CheckoutController::paymentCallback');
$routes->get('/checkout/paymentResult', 'CheckoutController::paymentResult');
$routes->post('checkout/getPayTabsIframeUrl', 'CheckoutController::getPayTabsIframeUrl');

$routes->setDefaultController('OrdersController');
$routes->setDefaultMethod('index');
