<?php
declare(strict_types=1);
function ensureSessionStarted(): void { if(session_status()!==PHP_SESSION_ACTIVE){session_start();}}
function requireAuth(): void { ensureSessionStarted(); if(!isset($_SESSION['user'])){header('Location: /login.php'); exit;}}
function requireRole(array $roles): void { requireAuth(); if(!in_array($_SESSION['user']['role']??null,$roles,true)){http_response_code(403); exit('Forbidden');}}
