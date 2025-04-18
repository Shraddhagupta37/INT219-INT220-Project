<?php
session_start();
if (!isset($_SESSION['test_start_time'])) {
    $_SESSION['test_start_time'] = time();
}
// Optionally restrict to logged in users and valid test_id
http_response_code(204); // No content
