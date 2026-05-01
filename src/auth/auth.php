<?php
declare(strict_types=1);
require_once __DIR__.'/session.php'; require_once __DIR__.'/../../config/database.php';
function loginUser(string $email,string $password): bool { ensureSessionStarted(); $s=db()->prepare('SELECT id,name,email,role,password_hash FROM users WHERE email=:email AND is_active=1 LIMIT 1'); $s->execute(['email'=>$email]); $u=$s->fetch(); if(!$u||!password_verify($password,$u['password_hash'])){return false;} $_SESSION['user']=['id'=>(int)$u['id'],'name'=>$u['name'],'email'=>$u['email'],'role'=>$u['role']]; return true; }
function logoutUser(): void { ensureSessionStarted(); $_SESSION=[]; session_destroy(); }
