<?php
/**
 * Configuration file for Jyotish API
 * 
 * Uses environment variables with fallbacks for configuration
 */

// Use PHP 7.4 null coalescing assignment operator for environment variables
define('SWETEST_PATH', $_ENV['SWETEST_PATH'] ?? '/var/www/api/swetest/src');
