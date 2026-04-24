<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
secureSessionStart();
applySecurityHeaders();

unset($_SESSION['user']);
unset($_SESSION['_csrf_token']);
unset($_SESSION['cart']);
markFallbackLoggedOut();
clearDemoAuthCookie();
session_regenerate_id(true);

header('Location: auth.php');
exit;
