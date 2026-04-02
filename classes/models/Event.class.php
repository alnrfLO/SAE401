<?php
class Event {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create(int $userId, array $data) {
        $sql = 'INSERT INTO events (user_id, title, description, location, event_date, type, status) VALUES (:user_id, :title, :description, :location, :event_date, :type, :status)';
        $stmt = $this->pdo->prepare($sql);
        $ok = $stmt->execute([
            ':user_id'    => $userId,
            ':title'      => trim($data['title'] ?? ''),
            ':description'=> $data['description'] ?? null,
            ':location'   => $data['location'] ?? null,
            ':event_date' => $data['event_date'] ?? null,
            ':type'       => in_array($data['type'] ?? '', ['private','shared','public']) ? $data['type'] : 'private',
            ':status'     => in_array($data['status'] ?? '', ['pending','accepted','refused']) ? $data['status'] : 'pending',
        ]);

        if (!$ok) return false;
        return (int)$this->pdo->lastInsertId();
    }

    public function update(int $eventId, int $userId, array $data) {
        $event = $this->findById($eventId);
        if (!$event || $event['user_id'] != $userId) {
            return false;
        }

        $allowed = ['title','description','location','event_date','type','status'];
        $fields = [];
        $params = [':id' => $eventId];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                if ($field === 'type') {
                    $params[":$field"] = in_array($data[$field], ['private','shared','public']) ? $data[$field] : 'private';
                } elseif ($field === 'status') {
                    $params[":$field"] = in_array($data[$field], ['pending','accepted','refused']) ? $data[$field] : 'pending';
                } else {
                    $params[":$field"] = $data[$field];
                }
            }
        }

        if (empty($fields)) {
            return false;
        }

        $sql = 'UPDATE events SET ' . implode(', ', $fields) . ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $eventId, int $userId) {
        $event = $this->findById($eventId);
        if (!$event || $event['user_id'] != $userId) {
            return false;
        }

        $stmt = $this->pdo->prepare('DELETE FROM events WHERE id = :id');
        return $stmt->execute([':id' => $eventId]);
    }

    public function findById(int $id) {
        $stmt = $this->pdo->prepare('SELECT * FROM events WHERE id = :id');
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByMonth(int $userId, int $year, int $month) {
        $from = sprintf('%04d-%02d-01 00:00:00', $year, $month);
        $toDate = new DateTime($from);
        $toDate->modify('first day of next month');
        $to = $toDate->format('Y-m-d H:i:s');

        $stmt = $this->pdo->prepare('SELECT * FROM events WHERE user_id = :user_id AND event_date >= :from AND event_date < :to ORDER BY event_date ASC');
        $stmt->execute([':user_id' => $userId, ':from' => $from, ':to' => $to]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByDay(int $userId, string $date) {
        $start = $date . ' 00:00:00';
        $end = $date . ' 23:59:59';
        $stmt = $this->pdo->prepare('SELECT * FROM events WHERE user_id = :user_id AND event_date BETWEEN :start AND :end ORDER BY event_date ASC');
        $stmt->execute([':user_id' => $userId, ':start' => $start, ':end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
