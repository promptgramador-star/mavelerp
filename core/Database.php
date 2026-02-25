<?php

namespace Core;

use PDO;
use PDOException;

/**
 * Conexión PDO Singleton a MySQL.
 * Usa charset utf8mb4 y modo de error EXCEPTION.
 *
 * @package ERP\Core
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $config = require BASE_PATH . '/config/database.php';

        $dsn = sprintf(
            '%s:host=%s;port=%d;dbname=%s;charset=%s',
            $config['driver'],
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        } catch (PDOException $e) {
            if (config('app', 'debug')) {
                die('Error de conexión: ' . $e->getMessage());
            }
            die('Error de conexión a la base de datos.');
        }
    }

    /**
     * Obtiene la instancia singleton.
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna la conexión PDO directa.
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    /**
     * Ejecuta una consulta preparada y retorna el statement.
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Ejecuta SELECT y retorna todas las filas.
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Ejecuta SELECT y retorna una sola fila.
     */
    public function fetch(string $sql, array $params = []): array|false
    {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Ejecuta INSERT y retorna el last insert ID.
     */
    public function insert(string $sql, array $params = []): string
    {
        $this->query($sql, $params);
        return $this->pdo->lastInsertId();
    }

    /**
     * Ejecuta UPDATE/DELETE y retorna el número de filas afectadas.
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Inicia una transacción.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Confirma la transacción.
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Revierte la transacción.
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    // Prevenir clonación y deserialización
    private function __clone()
    {
    }
    public function __wakeup()
    {
        throw new \Exception('No se puede deserializar un singleton.');
    }
}
