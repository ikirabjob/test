<?php

// Database configuration
const DB_HOST = 'postgres';
const DB_PORT = 5432;
const DB_NAME = 'event_management_system';
const DB_USER = 'your_username';
const DB_PASSWORD = 'your_password';

// Environment configuration
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');

// Base URL configuration
const BASE_URL = '/';

// Authentication configuration
const AUTH_SECRET_KEY = 'your_secret_key';