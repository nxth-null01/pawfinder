<?php

class Report
{
    public static function all($q = '', $type = '', $status = '')
    {
        $sql = 'SELECT r.*, u.name user_name FROM reports r JOIN users u ON u.id = r.user_id WHERE r.is_approved = 1';
        $params = [];

        if ($q) {
            $sql .= ' AND (animal_name LIKE ? OR breed LIKE ? OR color LIKE ? OR location LIKE ?)';
            $like = "%$q%";
            $params = array_merge($params, [$like, $like, $like, $like]);
        }

        if ($type) {
            $sql .= ' AND report_type = ?';
            $params[] = $type;
        }

        if ($status) {
            $sql .= ' AND status = ?';
            $params[] = $status;
        }

        $sql .= ' ORDER BY r.created_at DESC';
        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function find($id)
    {
        $stmt = Database::pdo()->prepare('SELECT r.*, u.name user_name, u.email FROM reports r JOIN users u ON u.id = r.user_id WHERE r.id = ?');
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
        return Database::pdo()
            ->query('SELECT r.*, u.name user_name FROM reports r JOIN users u ON u.id = r.user_id ORDER BY r.created_at DESC')
            ->fetchAll();
    }

    public static function create($data)
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO reports(user_id, report_type, animal_name, species, breed, color, gender, last_seen_date, location, owner_contact, description, photo) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        return $stmt->execute([
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
    }

    public static function sightings($id)
    {
        $stmt = Database::pdo()->prepare('SELECT * FROM sightings WHERE report_id = ? ORDER BY created_at DESC');
        $stmt->execute([$id]);

        return $stmt->fetchAll();
    }

    public static function addSighting($data)
    {
        $stmt = Database::pdo()->prepare(
            'INSERT INTO sightings(report_id, name, contact, location, note, photo) VALUES(?, ?, ?, ?, ?, ?)'
        );

        return $stmt->execute([
            $data['report_id'],
            $data['name'],
            $data['contact'],
            $data['location'],
            $data['note'],
            $data['photo'] ?? ''
        ]);
    }

    public static function updateStatus($id, $status, $approved)
    {
        $stmt = Database::pdo()->prepare('UPDATE reports SET status = ?, is_approved = ? WHERE id = ?');

        return $stmt->execute([$status, $approved, $id]);
    }

    public static function stats()
    {
        return Database::pdo()
            ->query("SELECT COUNT(*) total, SUM(report_type = 'missing') missing, SUM(report_type = 'found') found, SUM(status = 'reunited') reunited FROM reports")
            ->fetch();
    }
}
