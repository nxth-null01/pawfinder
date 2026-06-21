<?php
class User {
  private static function ensureProfileColumn() {
    static $done = false;
    if ($done) return;
    $done = true;
    try {
      Database::pdo()->exec("ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) NULL");
    } catch (Throwable $e) {
      // Column already exists or the database driver does not allow duplicate ALTER. Safe to ignore.
    }
  }

  public static function findByEmail($email) {
    self::ensureProfileColumn();
    $s = Database::pdo()->prepare('SELECT * FROM users WHERE email=? LIMIT 1'); $s->execute([$email]); return $s->fetch();
  }
  public static function create($name,$email,$password) {
    self::ensureProfileColumn();
    $s = Database::pdo()->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,"user")');
    return $s->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
  }
  public static function findById($id) {
    self::ensureProfileColumn();
    $s = Database::pdo()->prepare('SELECT * FROM users WHERE id=? LIMIT 1');
    $s->execute([$id]);
    return $s->fetch();
  }

  public static function emailExistsForOtherUser($email, $id) {
    self::ensureProfileColumn();
    $s = Database::pdo()->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
    $s->execute([$email, $id]);
    return (bool) $s->fetch();
  }

  public static function updateProfile($id, $name, $email, $profilePhoto = null) {
    self::ensureProfileColumn();
    if ($profilePhoto !== null && $profilePhoto !== '') {
      $s = Database::pdo()->prepare('UPDATE users SET name=?, email=?, profile_photo=? WHERE id=?');
      return $s->execute([$name, $email, $profilePhoto, $id]);
    }

    $s = Database::pdo()->prepare('UPDATE users SET name=?, email=? WHERE id=?');
    return $s->execute([$name, $email, $id]);
  }

  public static function updatePassword($id, $password) {
    self::ensureProfileColumn();
    $s = Database::pdo()->prepare('UPDATE users SET password=? WHERE id=?');
    return $s->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
  }
}
