<?php
class User {
  public static function findByEmail($email) {
    $s = Database::pdo()->prepare('SELECT * FROM users WHERE email=? LIMIT 1'); $s->execute([$email]); return $s->fetch();
  }
  public static function create($name,$email,$password) {
    $s = Database::pdo()->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,"user")');
    return $s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
  }
}
