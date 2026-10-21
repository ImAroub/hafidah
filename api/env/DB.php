<?php
class DB {
    private static ?mysqli $instance = null;

    private static function connect(): mysqli {
        if (self::$instance === null) {
            self::$instance = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            self::$instance->set_charset('utf8mb4');

            if (self::$instance->connect_error) {
                throw new \RuntimeException("Database Connection failed: " . self::$instance->connect_error);
            }
            self::$instance->set_charset('utf8mb4');
        }
        return self::$instance;
    }

    public static function query(string $sql, string $types = '', array $params = []): mysqli_result|bool {
        $conn = self::connect();
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new \RuntimeException("Prepare failed: $sql — " . $conn->error);
        }

        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            throw new \RuntimeException("Execute failed: " . $stmt->error);
        }
        return $stmt->get_result();
    }

    public static function fetch(string $sql, string $types = '', array $params = []): ?array {
        $result = self::query($sql,$types, $params);
        if (!$result instanceof mysqli_result) {
            return null;
        }
        return $result->fetch_assoc()?: null;
    }

    public static function fetchAll(string $sql, string $types = '', array $params = []): array {
        $result = self::query($sql, $types, $params);
        if (!$result instanceof mysqli_result) {
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function insert(string $sql, string $types = '', array $params = []): int {
        self::query($sql, $types, $params);
        return self::connect()->insert_id;
    }

    public static function affected(): int {
        return self::connect()->affected_rows;
    }

   public static function paging(string $sql,string $types = '',array $params = [],int $limit = 20,int $page = 1): array {

    $page = max(1, $page);

    $perPage = max(1, min(100, $limit));

    $offset = ($page - 1) * $perPage;

    $countResult = self::fetch(
        "SELECT COUNT(*) AS total
         FROM ($sql) AS paging_result",
        $types,
        $params
    );

    $total = (int) ($countResult['total'] ?? 0);

    $data = self::fetchAll(
        $sql . " LIMIT $perPage OFFSET $offset",
        $types,
        $params
    );

    return [
        'data' => $data,

        'pagination' => [
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => (int) ceil($total / $perPage),
            'has_next' => ($page * $perPage) < $total,
            'has_prev' => $page > 1
        ]
    ];
}
}
