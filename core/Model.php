<?php

namespace Core;

/**
 * Modelo base con query builder ligero sobre PDO.
 * Todos los modelos del ERP extienden esta clase.
 *
 * @package ERP\Core
 */
abstract class Model
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Busca un registro por su ID.
     */
    public function find(int $id): array|false
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->fetch($sql, ['id' => $id]);
    }

    /**
     * Retorna todos los registros.
     */
    public function all(string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $sql = "SELECT * FROM {$this->table} ORDER BY {$orderBy} {$direction}";
        return $this->db->fetchAll($sql);
    }

    /**
     * Búsqueda con condiciones WHERE simples.
     */
    public function where(array $conditions, string $orderBy = 'id', string $direction = 'ASC'): array
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $clauses = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            $clauses[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        $where = implode(' AND ', $clauses);
        $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$orderBy} {$direction}";
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Busca el primer registro que coincida con las condiciones.
     */
    public function firstWhere(array $conditions): array|false
    {
        $clauses = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            $clauses[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        $where = implode(' AND ', $clauses);
        $sql = "SELECT * FROM {$this->table} WHERE {$where} LIMIT 1";
        return $this->db->fetch($sql, $params);
    }

    /**
     * Inserta un nuevo registro.
     */
    public function create(array $data): string
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        return $this->db->insert($sql, $data);
    }

    /**
     * Actualiza un registro por su ID.
     */
    public function update(int $id, array $data): int
    {
        $sets = [];
        $params = ['id' => $id];

        foreach ($data as $column => $value) {
            $sets[] = "{$column} = :{$column}";
            $params[$column] = $value;
        }

        $setClause = implode(', ', $sets);
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        return $this->db->execute($sql, $params);
    }

    /**
     * Elimina un registro por su ID.
     */
    public function delete(int $id): int
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }

    /**
     * Cuenta registros con condiciones opcionales.
     */
    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $result = $this->db->fetch($sql);
        } else {
            $clauses = [];
            $params = [];
            foreach ($conditions as $column => $value) {
                $clauses[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            $where = implode(' AND ', $clauses);
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$where}";
            $result = $this->db->fetch($sql, $params);
        }

        return (int) ($result['total'] ?? 0);
    }

    /**
     * Paginación simple.
     */
    public function paginate(int $page = 1, int $perPage = 20, array $conditions = []): array
    {
        $offset = ($page - 1) * $perPage;
        $total = $this->count($conditions);
        $totalPages = (int) ceil($total / $perPage);

        if (empty($conditions)) {
            $sql = "SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
            $params = ['limit' => $perPage, 'offset' => $offset];
        } else {
            $clauses = [];
            $params = ['limit' => $perPage, 'offset' => $offset];
            foreach ($conditions as $column => $value) {
                $clauses[] = "{$column} = :{$column}";
                $params[$column] = $value;
            }
            $where = implode(' AND ', $clauses);
            $sql = "SELECT * FROM {$this->table} WHERE {$where} ORDER BY {$this->primaryKey} DESC LIMIT :limit OFFSET :offset";
        }

        $data = $this->db->fetchAll($sql, $params);

        return [
            'data' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => $totalPages,
        ];
    }
}
