<?php

class Report
{
    private static function ensureFeatureTables()
    {
        static $done = false;
        if ($done) return;
        $pdo = Database::pdo();

        $pdo->exec("CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_id INT NOT NULL,
            user_id INT NULL,
            name VARCHAR(120) NOT NULL,
            comment TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(report_id),
            INDEX(user_id)
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            report_id INT NULL,
            type VARCHAR(60) NOT NULL DEFAULT 'update',
            title VARCHAR(160) NOT NULL,
            message TEXT NOT NULL,
            is_read TINYINT(1) DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(user_id),
            INDEX(report_id)
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_id INT NOT NULL,
            user_id INT NULL,
            icon VARCHAR(60) DEFAULT 'activity',
            title VARCHAR(160) NOT NULL,
            details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(report_id),
            INDEX(user_id)
        )");

        $done = true;
    }

    public static function all($q = '', $type = '', $status = '')
    {
        $sql = 'SELECT r.*, u.name user_name 
                FROM reports r 
                JOIN users u ON u.id = r.user_id 
                WHERE r.is_approved = 1';

        $params = [];

        if (!empty($q)) {
            $sql .= ' AND (
                r.animal_name LIKE ? 
                OR r.species LIKE ? 
                OR r.breed LIKE ? 
                OR r.color LIKE ? 
                OR r.location LIKE ? 
                OR r.report_type LIKE ?
                OR r.status LIKE ?
            )';

            $like = "%{$q}%";

            $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like]);
        }

        if (!empty($type)) {
            $sql .= ' AND r.report_type = ?';
            $params[] = $type;
        }

        if (!empty($status)) {
            $sql .= ' AND r.status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY r.created_at DESC';

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function find($id)
    {
        $stmt = Database::pdo()->prepare(
            'SELECT r.*, u.name user_name, u.email 
             FROM reports r 
             JOIN users u ON u.id = r.user_id 
             WHERE r.id = ?'
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function byUser($uid)
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC');
        $stmt->execute([$uid]);
        return $stmt->fetchAll();
    }

    public static function pending()
    {
        return Database::pdo()->query(
            'SELECT r.*, u.name user_name 
             FROM reports r 
             JOIN users u ON u.id = r.user_id 
             ORDER BY r.created_at DESC'
        )->fetchAll();
    }

    public static function create($data)
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO reports(
                user_id, report_type, animal_name, species, breed, color, gender, last_seen_date, location, owner_contact, description, photo
            ) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $data['user_id'],
            $data['report_type'],
            $data['animal_name'],
            $data['species'],
            $data['breed'],
            $data['color'],
            $data['gender'],
            $data['last_seen_date'],
            $data['location'],
            $data['owner_contact'] ?? '',
            $data['description'],
            $data['photo']
        ]);

        return Database::pdo()->lastInsertId();
    }

    public static function sightings($id)
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM sightings WHERE report_id = ? ORDER BY created_at DESC');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public static function closeRequests($id)
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM close_requests WHERE report_id = ? ORDER BY created_at DESC');
        $stmt->execute([$id]);
        return $stmt->fetchAll();
    }

    public static function pendingCloseRequests()
    {
        return Database::pdo()->query(
            "SELECT cr.*, r.animal_name, r.report_type, r.status current_status, u.name user_name
             FROM close_requests cr
             JOIN reports r ON r.id = cr.report_id
             JOIN users u ON u.id = r.user_id
             WHERE cr.request_status = 'pending'
             ORDER BY cr.created_at DESC"
        )->fetchAll();
    }

    public static function addSighting($data)
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO sightings(report_id, name, contact, location, note, photo) VALUES(?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['report_id'], $data['name'], $data['contact'], $data['location'], $data['note'], $data['photo'] ?? ''
        ]);
        return Database::pdo()->lastInsertId();
    }

    public static function addCloseRequest($data)
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO close_requests(report_id, name, contact, result_status, note, proof_photo) VALUES(?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $data['report_id'], $data['name'], $data['contact'], $data['result_status'], $data['note'], $data['proof_photo'] ?? ''
        ]);
        return Database::pdo()->lastInsertId();
    }

    public static function updateCloseRequest($id, $requestStatus)
    {
        $stmt = Database::pdo()->prepare('UPDATE close_requests SET request_status = ? WHERE id = ?');
        return $stmt->execute([$requestStatus, $id]);
    }

    public static function findCloseRequest($id)
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM close_requests WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public static function updateStatus($id, $status, $approved)
    {
        $stmt = Database::pdo()->prepare('UPDATE reports SET status = ?, is_approved = ? WHERE id = ?');
        return $stmt->execute([$status, $approved, $id]);
    }

    public static function delete($id)
    {
        $stmt = Database::pdo()->prepare('DELETE FROM reports WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public static function stats()
    {
        return Database::pdo()->query(
            "SELECT COUNT(*) total, SUM(report_type = 'missing') missing, SUM(report_type = 'found') found, SUM(status = 'reunited') reunited FROM reports"
        )->fetch();
    }

    public static function analytics()
    {
        self::ensureFeatureTables();
        $pdo = Database::pdo();
        return [
            'reports' => self::stats(),
            'pending' => $pdo->query("SELECT COUNT(*) total FROM reports WHERE is_approved = 0")->fetch(),
            'comments' => $pdo->query("SELECT COUNT(*) total FROM comments")->fetch(),
            'sightings' => $pdo->query("SELECT COUNT(*) total FROM sightings")->fetch(),
            'recent' => $pdo->query("SELECT DATE(created_at) day, COUNT(*) total FROM reports GROUP BY DATE(created_at) ORDER BY day DESC LIMIT 7")->fetchAll(),
            'byType' => $pdo->query("SELECT report_type, COUNT(*) total FROM reports GROUP BY report_type")->fetchAll(),
        ];
    }

    public static function comments($reportId)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare(
            'SELECT c.*, u.name user_name FROM comments c LEFT JOIN users u ON u.id = c.user_id WHERE c.report_id = ? ORDER BY c.created_at DESC'
        );
        $stmt->execute([$reportId]);
        return $stmt->fetchAll();
    }

    public static function addComment($data)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('INSERT INTO comments(report_id, user_id, name, comment) VALUES(?, ?, ?, ?)');
        $stmt->execute([$data['report_id'], $data['user_id'] ?? null, $data['name'], $data['comment']]);
        return Database::pdo()->lastInsertId();
    }

    public static function addActivity($reportId, $userId, $icon, $title, $details = '')
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('INSERT INTO activity_logs(report_id, user_id, icon, title, details) VALUES(?, ?, ?, ?, ?)');
        return $stmt->execute([$reportId, $userId, $icon, $title, $details]);
    }

    public static function timeline($reportId)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('SELECT * FROM activity_logs WHERE report_id = ? ORDER BY created_at DESC');
        $stmt->execute([$reportId]);
        return $stmt->fetchAll();
    }

    public static function notify($userId, $reportId, $type, $title, $message)
    {
        if (empty($userId)) return false;
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('INSERT INTO notifications(user_id, report_id, type, title, message) VALUES(?, ?, ?, ?, ?)');
        return $stmt->execute([$userId, $reportId, $type, $title, $message]);
    }

    public static function notifications($userId, $limit = 10)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . (int)$limit);
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function unreadNotifications($userId)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) total FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->execute([$userId]);
        return (int)($stmt->fetch()['total'] ?? 0);
    }

    public static function markNotificationsRead($userId)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?');
        return $stmt->execute([$userId]);
    }

    public static function successStories()
    {
        $stmt = Database::pdo()->query(
            "SELECT r.*, u.name user_name FROM reports r JOIN users u ON u.id = r.user_id WHERE r.status = 'reunited' AND r.is_approved = 1 ORDER BY r.created_at DESC"
        );
        return $stmt->fetchAll();
    }
}
