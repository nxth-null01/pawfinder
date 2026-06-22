<?php

class Report
{
    private static function columnExists($table, $column)
    {
        $stmt = Database::pdo()->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
        $stmt->execute([$column]);
        return (bool)$stmt->fetch();
    }

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
            parent_id INT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(report_id), INDEX(user_id), INDEX(parent_id)
        )");
        if (!self::columnExists('comments', 'parent_id')) {
            $pdo->exec("ALTER TABLE comments ADD COLUMN parent_id INT NULL AFTER comment, ADD INDEX(parent_id)");
        }

        if (!self::columnExists('comments', 'is_deleted')) {
            $pdo->exec("ALTER TABLE comments ADD COLUMN is_deleted TINYINT(1) DEFAULT 0 AFTER parent_id");
        }
        if (!self::columnExists('comments', 'edited_at')) {
            $pdo->exec("ALTER TABLE comments ADD COLUMN edited_at DATETIME NULL AFTER created_at");
        }
        if (!self::columnExists('comments', 'is_reported')) {
            $pdo->exec("ALTER TABLE comments ADD COLUMN is_reported TINYINT(1) DEFAULT 0 AFTER edited_at");
        }
        if (!self::columnExists('comments', 'report_reason')) {
            $pdo->exec("ALTER TABLE comments ADD COLUMN report_reason TEXT NULL AFTER is_reported");
        }
        if (!self::columnExists('comments', 'is_pinned')) {
            $pdo->exec("ALTER TABLE comments ADD COLUMN is_pinned TINYINT(1) DEFAULT 0 AFTER report_reason");
        }
        if (!self::columnExists('comments', 'type')) {
            $pdo->exec("ALTER TABLE comments ADD COLUMN type VARCHAR(40) DEFAULT 'comment' AFTER is_pinned");
        }
        if (!self::columnExists('comments', 'sighting_location')) {
            $pdo->exec("ALTER TABLE comments ADD COLUMN sighting_location VARCHAR(255) NULL AFTER type");
        }

        $pdo->exec("CREATE TABLE IF NOT EXISTS comment_helpfuls (
            id INT AUTO_INCREMENT PRIMARY KEY,
            comment_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_helpful(comment_id, user_id),
            INDEX(comment_id), INDEX(user_id)
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS report_follows (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_id INT NOT NULL,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_follow(report_id, user_id),
            INDEX(report_id), INDEX(user_id)
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
            INDEX(user_id), INDEX(report_id)
        )");

        $pdo->exec("CREATE TABLE IF NOT EXISTS activity_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            report_id INT NOT NULL,
            user_id INT NULL,
            icon VARCHAR(60) DEFAULT 'activity',
            title VARCHAR(160) NOT NULL,
            details TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX(report_id), INDEX(user_id)
        )");

        if (!self::columnExists('users', 'profile_photo')) {
            $pdo->exec("ALTER TABLE users ADD COLUMN profile_photo VARCHAR(255) NULL");
        }

        if (!self::columnExists('reports', 'reward_amount')) {
            $pdo->exec("ALTER TABLE reports ADD COLUMN reward_amount DECIMAL(10,2) NULL AFTER owner_contact");
        }
        if (!self::columnExists('sightings', 'is_verified')) {
            $pdo->exec("ALTER TABLE sightings ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER photo");
        }

        $done = true;
    }

    public static function all($q = '', $type = '', $status = '')
    {
        self::ensureFeatureTables();
        $sql = 'SELECT r.*, u.name user_name FROM reports r JOIN users u ON u.id = r.user_id WHERE r.is_approved = 1';
        $params = [];
        if (!empty($q)) {
            $sql .= ' AND (r.animal_name LIKE ? OR r.species LIKE ? OR r.breed LIKE ? OR r.color LIKE ? OR r.location LIKE ? OR r.report_type LIKE ? OR r.status LIKE ?)';
            $like = "%{$q}%"; $params = array_merge($params, [$like,$like,$like,$like,$like,$like,$like]);
        }
        if (!empty($type)) { $sql .= ' AND r.report_type = ?'; $params[] = $type; }
        if (!empty($status)) { $sql .= ' AND r.status = ?'; $params[] = $status; }
        $sql .= ' ORDER BY r.created_at DESC';
        $stmt = Database::pdo()->prepare($sql); $stmt->execute($params); return $stmt->fetchAll();
    }

    public static function find($id)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('SELECT r.*, u.name user_name, u.email FROM reports r JOIN users u ON u.id = r.user_id WHERE r.id = ?');
        $stmt->execute([$id]); return $stmt->fetch();
    }

    public static function byUser($uid) { self::ensureFeatureTables(); $stmt = Database::pdo()->prepare('SELECT * FROM reports WHERE user_id = ? ORDER BY created_at DESC'); $stmt->execute([$uid]); return $stmt->fetchAll(); }
    public static function pending() { self::ensureFeatureTables(); return Database::pdo()->query('SELECT r.*, u.name user_name FROM reports r JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC')->fetchAll(); }

    public static function create($data)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('INSERT INTO reports(user_id, report_type, animal_name, species, breed, color, gender, last_seen_date, location, owner_contact, reward_amount, description, photo) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $reward = isset($data['reward_amount']) && $data['reward_amount'] !== '' ? $data['reward_amount'] : null;
        $stmt->execute([$data['user_id'],$data['report_type'],$data['animal_name'],$data['species'],$data['breed'],$data['color'],$data['gender'],$data['last_seen_date'],$data['location'],$data['owner_contact'] ?? '',$reward,$data['description'],$data['photo']]);
        return Database::pdo()->lastInsertId();
    }

    public static function sightings($id) { self::ensureFeatureTables(); $stmt = Database::pdo()->prepare('SELECT * FROM sightings WHERE report_id = ? ORDER BY created_at DESC'); $stmt->execute([$id]); return $stmt->fetchAll(); }
    public static function closeRequests($id) { $stmt = Database::pdo()->prepare('SELECT * FROM close_requests WHERE report_id = ? ORDER BY created_at DESC'); $stmt->execute([$id]); return $stmt->fetchAll(); }
    public static function pendingCloseRequests() { return Database::pdo()->query("SELECT cr.*, r.animal_name, r.report_type, r.status current_status, u.name user_name FROM close_requests cr JOIN reports r ON r.id = cr.report_id JOIN users u ON u.id = r.user_id WHERE cr.request_status = 'pending' ORDER BY cr.created_at DESC")->fetchAll(); }

    public static function addSighting($data)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('INSERT INTO sightings(report_id, name, contact, location, note, photo) VALUES(?, ?, ?, ?, ?, ?)');
        $stmt->execute([$data['report_id'], $data['name'], $data['contact'], $data['location'], $data['note'], $data['photo'] ?? '']);
        return Database::pdo()->lastInsertId();
    }

    public static function verifySighting($id, $verified = 1)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('UPDATE sightings SET is_verified = ? WHERE id = ?');
        return $stmt->execute([(int)$verified, $id]);
    }

    public static function addCloseRequest($data) { $stmt = Database::pdo()->prepare('INSERT INTO close_requests(report_id, name, contact, result_status, note, proof_photo) VALUES(?, ?, ?, ?, ?, ?)'); $stmt->execute([$data['report_id'],$data['name'],$data['contact'],$data['result_status'],$data['note'],$data['proof_photo'] ?? '']); return Database::pdo()->lastInsertId(); }
    public static function updateCloseRequest($id, $requestStatus) { $stmt = Database::pdo()->prepare('UPDATE close_requests SET request_status = ? WHERE id = ?'); return $stmt->execute([$requestStatus, $id]); }
    public static function findCloseRequest($id) { $stmt = Database::pdo()->prepare('SELECT * FROM close_requests WHERE id = ?'); $stmt->execute([$id]); return $stmt->fetch(); }
    public static function updateStatus($id, $status, $approved) { $stmt = Database::pdo()->prepare('UPDATE reports SET status = ?, is_approved = ? WHERE id = ?'); return $stmt->execute([$status, $approved, $id]); }
    public static function delete($id) { $stmt = Database::pdo()->prepare('DELETE FROM reports WHERE id = ?'); return $stmt->execute([$id]); }
    public static function stats() { self::ensureFeatureTables(); return Database::pdo()->query("SELECT COUNT(*) total, SUM(report_type = 'missing') missing, SUM(report_type = 'found') found, SUM(status = 'reunited') reunited FROM reports")->fetch(); }

    public static function analytics()
    {
        self::ensureFeatureTables();
        $pdo = Database::pdo();
        $reports = self::stats();
        $total = (int)($reports['total'] ?? 0);
        $reunited = (int)($reports['reunited'] ?? 0);
        $reports['active'] = (int)($pdo->query("SELECT COUNT(*) total FROM reports WHERE status = 'active'")->fetch()['total'] ?? 0);
        $reports['closed'] = (int)($pdo->query("SELECT COUNT(*) total FROM reports WHERE status = 'closed'")->fetch()['total'] ?? 0);
        $reports['success_rate'] = $total > 0 ? round(($reunited / $total) * 100, 1) : 0;

        return [
            'reports' => $reports,
            'pending' => $pdo->query("SELECT COUNT(*) total FROM reports WHERE is_approved = 0")->fetch(),
            'comments' => $pdo->query("SELECT COUNT(*) total FROM comments WHERE is_deleted = 0")->fetch(),
            'sightings' => $pdo->query("SELECT COUNT(*) total, SUM(is_verified = 1) verified FROM sightings")->fetch(),
            'followers' => $pdo->query("SELECT COUNT(*) total FROM report_follows")->fetch(),
            'thisMonth' => $pdo->query("SELECT (SELECT COUNT(*) FROM reports WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')) reports, (SELECT COUNT(*) FROM sightings WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')) sightings, (SELECT COUNT(*) FROM comments WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND is_deleted = 0) comments")->fetch(),
            'recent' => $pdo->query("SELECT DATE(created_at) day, COUNT(*) total FROM reports GROUP BY DATE(created_at) ORDER BY day DESC LIMIT 7")->fetchAll(),
            'monthly' => $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m') month, COUNT(*) total FROM reports GROUP BY DATE_FORMAT(created_at, '%Y-%m') ORDER BY month DESC LIMIT 6")->fetchAll(),
            'byType' => $pdo->query("SELECT report_type, COUNT(*) total FROM reports GROUP BY report_type")->fetchAll(),
            'areas' => $pdo->query("SELECT COALESCE(NULLIF(TRIM(location), ''), 'Unknown') location, COUNT(*) total FROM reports GROUP BY COALESCE(NULLIF(TRIM(location), ''), 'Unknown') ORDER BY total DESC LIMIT 8")->fetchAll(),
            'contributors' => $pdo->query("SELECT u.id, u.name, u.profile_photo, COUNT(DISTINCT r.id) reports, COUNT(DISTINCT c.id) comments, COUNT(DISTINCT s.id) verified_sightings, (COUNT(DISTINCT r.id) * 10 + COUNT(DISTINCT c.id) * 2 + COUNT(DISTINCT s.id) * 20 + SUM(CASE WHEN r.status = 'reunited' THEN 30 ELSE 0 END)) points FROM users u LEFT JOIN reports r ON r.user_id = u.id LEFT JOIN comments c ON c.user_id = u.id AND c.is_deleted = 0 LEFT JOIN sightings s ON s.name = u.name AND s.is_verified = 1 GROUP BY u.id, u.name, u.profile_photo ORDER BY points DESC LIMIT 5")->fetchAll(),
            'activeCases' => $pdo->query("SELECT r.id, r.animal_name, r.location, r.status, COUNT(DISTINCT c.id) comments, COUNT(DISTINCT s.id) sightings, COUNT(DISTINCT f.id) followers FROM reports r LEFT JOIN comments c ON c.report_id = r.id AND c.is_deleted = 0 LEFT JOIN sightings s ON s.report_id = r.id LEFT JOIN report_follows f ON f.report_id = r.id GROUP BY r.id ORDER BY (COUNT(DISTINCT c.id) + COUNT(DISTINCT s.id) + COUNT(DISTINCT f.id)) DESC LIMIT 5")->fetchAll(),
            'reportedComments' => $pdo->query("SELECT c.id, c.comment, c.report_reason, c.created_at, r.id report_id, r.animal_name, u.name user_name FROM comments c LEFT JOIN reports r ON r.id = c.report_id LEFT JOIN users u ON u.id = c.user_id WHERE c.is_reported = 1 ORDER BY c.created_at DESC LIMIT 8")->fetchAll(),
            'pendingSightings' => $pdo->query("SELECT s.*, r.animal_name FROM sightings s JOIN reports r ON r.id = s.report_id WHERE s.is_verified = 0 ORDER BY s.created_at DESC LIMIT 8")->fetchAll(),
        ];
    }

    public static function comments($reportId, $sort = 'newest')
    {
        self::ensureFeatureTables();
        $order = "c.is_pinned DESC, c.created_at DESC";
        if ($sort === 'oldest') {
            $order = "c.is_pinned DESC, c.created_at ASC";
        } elseif ($sort === 'helpful') {
            $order = "c.is_pinned DESC, helpful_count DESC, c.created_at DESC";
        }
        $stmt = Database::pdo()->prepare("SELECT c.*, COALESCE(u.name, c.name) user_name, u.profile_photo, u.role user_role, (SELECT COUNT(*) FROM comment_helpfuls ch WHERE ch.comment_id = c.id) helpful_count, (SELECT COUNT(*) FROM reports r2 WHERE r2.user_id = c.user_id) user_report_count, (SELECT COUNT(*) FROM comment_helpfuls ch2 JOIN comments c2 ON c2.id = ch2.comment_id WHERE c2.user_id = c.user_id) user_helpful_count, (SELECT COUNT(*) FROM sightings s2 WHERE s2.name = COALESCE(u.name, c.name) AND s2.is_verified = 1) user_verified_sightings FROM comments c LEFT JOIN users u ON u.id = c.user_id WHERE c.report_id = ? ORDER BY $order");
        $stmt->execute([$reportId]);
        return $stmt->fetchAll();
    }

    public static function addComment($data)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('INSERT INTO comments(report_id, user_id, name, comment, parent_id, type, sighting_location) VALUES(?, ?, ?, ?, ?, ?, ?)');
        $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
        $type = !empty($data['type']) ? $data['type'] : 'comment';
        $sightingLocation = $data['sighting_location'] ?? null;
        $stmt->execute([$data['report_id'], $data['user_id'] ?? null, $data['name'], $data['comment'], $parentId, $type, $sightingLocation]);
        return Database::pdo()->lastInsertId();
    }


    public static function findComment($commentId)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('SELECT * FROM comments WHERE id = ?');
        $stmt->execute([$commentId]);
        return $stmt->fetch();
    }

    public static function updateComment($commentId, $userId, $comment, $isAdmin = false)
    {
        self::ensureFeatureTables();
        $sql = $isAdmin ? 'UPDATE comments SET comment = ?, edited_at = NOW() WHERE id = ? AND is_deleted = 0' : 'UPDATE comments SET comment = ?, edited_at = NOW() WHERE id = ? AND user_id = ? AND is_deleted = 0';
        $params = $isAdmin ? [$comment, $commentId] : [$comment, $commentId, $userId];
        $stmt = Database::pdo()->prepare($sql);
        return $stmt->execute($params);
    }

    public static function deleteComment($commentId, $userId, $isAdmin = false)
    {
        self::ensureFeatureTables();
        $comment = self::findComment($commentId);
        if (!$comment) return false;
        if (!$isAdmin && (int)$comment['user_id'] !== (int)$userId) return false;
        $stmt = Database::pdo()->prepare("UPDATE comments SET is_deleted = 1, comment = '[deleted]', edited_at = NOW() WHERE id = ?");
        return $stmt->execute([$commentId]);
    }

    public static function reportComment($commentId, $userId, $reason = '')
    {
        self::ensureFeatureTables();
        $comment = self::findComment($commentId);
        if (!$comment || (int)$comment['user_id'] === (int)$userId) return false;
        $stmt = Database::pdo()->prepare('UPDATE comments SET is_reported = 1, report_reason = ? WHERE id = ?');
        return $stmt->execute([$reason, $commentId]);
    }

    public static function pinComment($commentId, $reportId, $pinned = 1)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('UPDATE comments SET is_pinned = ? WHERE id = ? AND report_id = ? AND is_deleted = 0');
        return $stmt->execute([(int)$pinned, $commentId, $reportId]);
    }

    public static function toggleHelpful($commentId, $userId)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('SELECT id FROM comment_helpfuls WHERE comment_id = ? AND user_id = ?');
        $stmt->execute([$commentId, $userId]); $row = $stmt->fetch();
        if ($row) { $del = Database::pdo()->prepare('DELETE FROM comment_helpfuls WHERE id = ?'); $del->execute([$row['id']]); return false; }
        $ins = Database::pdo()->prepare('INSERT IGNORE INTO comment_helpfuls(comment_id, user_id) VALUES(?, ?)'); $ins->execute([$commentId, $userId]); return true;
    }

    public static function commentReportId($commentId)
    {
        self::ensureFeatureTables(); $stmt = Database::pdo()->prepare('SELECT report_id FROM comments WHERE id = ?'); $stmt->execute([$commentId]); $row = $stmt->fetch(); return $row['report_id'] ?? null;
    }



    public static function userReputation($userId)
    {
        self::ensureFeatureTables();
        $pdo = Database::pdo();
        $reports = $pdo->prepare('SELECT COUNT(*) total, SUM(status = "reunited") reunited FROM reports WHERE user_id = ?');
        $reports->execute([$userId]);
        $r = $reports->fetch() ?: ['total' => 0, 'reunited' => 0];

        $comments = $pdo->prepare('SELECT COUNT(*) total FROM comments WHERE user_id = ? AND is_deleted = 0');
        $comments->execute([$userId]);
        $c = $comments->fetch() ?: ['total' => 0];

        $helpful = $pdo->prepare('SELECT COUNT(*) total FROM comment_helpfuls ch JOIN comments c ON c.id = ch.comment_id WHERE c.user_id = ?');
        $helpful->execute([$userId]);
        $h = $helpful->fetch() ?: ['total' => 0];

        $verified = $pdo->prepare('SELECT COUNT(*) total FROM sightings s JOIN users u ON u.name = s.name WHERE u.id = ? AND s.is_verified = 1');
        $verified->execute([$userId]);
        $v = $verified->fetch() ?: ['total' => 0];

        $points = ((int)($r['total'] ?? 0) * 10) + ((int)($c['total'] ?? 0) * 2) + ((int)($h['total'] ?? 0) * 5) + ((int)($v['total'] ?? 0) * 20) + ((int)($r['reunited'] ?? 0) * 30);
        $badge = 'Community Member';
        $icon = 'paw-print';
        if ($points >= 100) { $badge = 'Trusted Reporter'; $icon = 'badge-check'; }
        elseif ((int)($h['total'] ?? 0) >= 5) { $badge = 'Helpful Neighbor'; $icon = 'heart-handshake'; }
        elseif ((int)($r['total'] ?? 0) >= 1) { $badge = 'Pet Advocate'; $icon = 'shield-check'; }

        return [
            'points' => $points,
            'badge' => $badge,
            'icon' => $icon,
            'reports' => (int)($r['total'] ?? 0),
            'reunited' => (int)($r['reunited'] ?? 0),
            'comments' => (int)($c['total'] ?? 0),
            'helpful' => (int)($h['total'] ?? 0),
            'verified_sightings' => (int)($v['total'] ?? 0),
        ];
    }

    public static function profileCompletion($user)
    {
        self::ensureFeatureTables();
        $userId = (int)($user['id'] ?? 0);
        $checks = [
            ['label' => 'Full name', 'done' => !empty($user['name'])],
            ['label' => 'Email address', 'done' => !empty($user['email'])],
            ['label' => 'Profile picture', 'done' => !empty($user['profile_photo'])],
            ['label' => 'Created a report', 'done' => false],
            ['label' => 'Followed a case', 'done' => false],
        ];
        if ($userId) {
            $stmt = Database::pdo()->prepare('SELECT COUNT(*) total FROM reports WHERE user_id = ?');
            $stmt->execute([$userId]);
            $checks[3]['done'] = ((int)($stmt->fetch()['total'] ?? 0)) > 0;
            $stmt = Database::pdo()->prepare('SELECT COUNT(*) total FROM report_follows WHERE user_id = ?');
            $stmt->execute([$userId]);
            $checks[4]['done'] = ((int)($stmt->fetch()['total'] ?? 0)) > 0;
        }
        $done = count(array_filter($checks, fn($i) => !empty($i['done'])));
        return ['percent' => (int)round(($done / count($checks)) * 100), 'done' => $done, 'total' => count($checks), 'items' => $checks];
    }

    public static function addActivity($reportId, $userId, $icon, $title, $details = '') { self::ensureFeatureTables(); $stmt = Database::pdo()->prepare('INSERT INTO activity_logs(report_id, user_id, icon, title, details) VALUES(?, ?, ?, ?, ?)'); return $stmt->execute([$reportId, $userId, $icon, $title, $details]); }
    public static function timeline($reportId) { self::ensureFeatureTables(); $stmt = Database::pdo()->prepare("SELECT * FROM activity_logs WHERE report_id = ? AND title NOT IN ('Comment Added','Report Approved','Report Created') ORDER BY created_at DESC"); $stmt->execute([$reportId]); return $stmt->fetchAll(); }

    public static function notify($userId, $reportId, $type, $title, $message) { if (empty($userId)) return false; self::ensureFeatureTables(); $stmt = Database::pdo()->prepare('INSERT INTO notifications(user_id, report_id, type, title, message) VALUES(?, ?, ?, ?, ?)'); return $stmt->execute([$userId,$reportId,$type,$title,$message]); }
    public static function notifyFollowers($reportId, $exceptUserId, $type, $title, $message)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('SELECT user_id FROM report_follows WHERE report_id = ? AND user_id <> ?'); $stmt->execute([$reportId, (int)$exceptUserId]);
        foreach ($stmt->fetchAll() as $f) self::notify($f['user_id'], $reportId, $type, $title, $message);
    }
    public static function notifications($userId, $limit = 10) { self::ensureFeatureTables(); $stmt = Database::pdo()->prepare('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT '.(int)$limit); $stmt->execute([$userId]); return $stmt->fetchAll(); }
    public static function unreadNotifications($userId) { self::ensureFeatureTables(); $stmt = Database::pdo()->prepare('SELECT COUNT(*) total FROM notifications WHERE user_id = ? AND is_read = 0'); $stmt->execute([$userId]); return (int)($stmt->fetch()['total'] ?? 0); }
    public static function markNotificationsRead($userId) { self::ensureFeatureTables(); $stmt = Database::pdo()->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ?'); return $stmt->execute([$userId]); }

    public static function isFollowing($reportId, $userId) { self::ensureFeatureTables(); if (!$userId) return false; $stmt = Database::pdo()->prepare('SELECT id FROM report_follows WHERE report_id = ? AND user_id = ?'); $stmt->execute([$reportId,$userId]); return (bool)$stmt->fetch(); }
    public static function toggleFollow($reportId, $userId)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('SELECT id FROM report_follows WHERE report_id = ? AND user_id = ?'); $stmt->execute([$reportId,$userId]); $row = $stmt->fetch();
        if ($row) { Database::pdo()->prepare('DELETE FROM report_follows WHERE id = ?')->execute([$row['id']]); return false; }
        Database::pdo()->prepare('INSERT IGNORE INTO report_follows(report_id,user_id) VALUES(?,?)')->execute([$reportId,$userId]); return true;
    }

    public static function trustScore($userId)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare('SELECT COUNT(*) total, SUM(is_verified = 1) verified FROM sightings WHERE contact IN (SELECT email FROM users WHERE id = ?)');
        $stmt->execute([$userId]); $row = $stmt->fetch();
        $total = (int)($row['total'] ?? 0); $verified = (int)($row['verified'] ?? 0);
        return ['total' => $total, 'verified' => $verified, 'score' => $total > 0 ? round(($verified / $total) * 100) : 0];
    }

    public static function similarReports($report, $limit = 3)
    {
        self::ensureFeatureTables();
        $stmt = Database::pdo()->prepare("SELECT * FROM reports WHERE id <> ? AND is_approved = 1 AND status = 'active' AND (species = ? OR color LIKE ? OR breed LIKE ?) ORDER BY created_at DESC LIMIT ".(int)$limit);
        $stmt->execute([$report['id'], $report['species'], '%'.$report['color'].'%', '%'.$report['breed'].'%']); return $stmt->fetchAll();
    }

    public static function successStories() { $stmt = Database::pdo()->query("SELECT r.*, u.name user_name FROM reports r JOIN users u ON u.id = r.user_id WHERE r.status = 'reunited' AND r.is_approved = 1 ORDER BY r.created_at DESC"); return $stmt->fetchAll(); }
}
