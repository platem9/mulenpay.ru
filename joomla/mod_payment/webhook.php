<?php
// Define the Joomla framework path
define('_JEXEC', 1);
define('JPATH_BASE', realpath(dirname(__FILE__) . '/../../')); // Adjust the path to your Joomla root

// Load the Joomla framework
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

// Get the application
$app = JFactory::getApplication('site');

// Get the input data
$input = $app->input;
$data = json_decode($input->json->getRaw(), true);

// Get the payment status
$payment_status = isset($data['payment_status']) ? $data['payment_status'] : null;
$uuid = isset($data['uuid']) ? $data['uuid'] : null;

if ($payment_status === 'success') {
    // TODO: Implement the logic to update the order status in your e-commerce extension.
    // Use the $uuid to identify the order.
}

// Send a response to the payment system
http_response_code(200);
