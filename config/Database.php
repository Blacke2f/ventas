<?php
/**
 * Database — Conexión PDO Singleton
 * Compatible con Laragon y Docker
 */

class Database {
    private PDO $pdo;
    private static ?Database $instance = null;

    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST
             . ';port='    . DB_PORT
             . ';dbname='  . DB_NAME
             . ';charset=' . DB_CHARSET;

        // Reintentar hasta 5 veces (útil en Docker cuando MySQL tarda en iniciar)
        $intentos  = 0;
        $maxIntentos = 5;
        $ultimoError = '';

        while ($intentos < $maxIntentos) {
            try {
                $this->pdo = new PDO($dsn, DB_USER, DB_PASSWORD, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_TIMEOUT            => 5,
                ]);
                return; // Conexión exitosa
            } catch (PDOException $e) {
                $ultimoError = $e->getMessage();
                $intentos++;
                if ($intentos < $maxIntentos) {
                    sleep(1); // Esperar 1 segundo antes de reintentar
                }
            }
        }

        // Si llegamos aquí, falló después de todos los intentos
        // Lanzar excepción en lugar de die() para que el error sea manejable
        throw new RuntimeException(
            'No se pudo conectar a la base de datos (' . DB_HOST . ':' . DB_PORT . '/' . DB_NAME . '): ' . $ultimoError
        );
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }

    public function execute(string $query, array $params = []): PDOStatement {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    public function findOne(string $query, array $params = []): mixed {
        return $this->execute($query, $params)->fetch();
    }

    public function findAll(string $query, array $params = []): array {
        return $this->execute($query, $params)->fetchAll();
    }

    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool {
        return $this->pdo->commit();
    }

    public function rollBack(): bool {
        return $this->pdo->rollBack();
    }
}
?>
