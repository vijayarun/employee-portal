<?php

require_once __DIR__ . '/SingletonTrait.php';

/**
 * This class used to handle the database functionality.
 *
 * Class DBHelper
 *
 * @author A Vijay<mailvijay.vj@gmail.com>
 */
class DBHelper
{
    use SingletonTrait;

    public const TABLE_IMPORT = 'import';
    public const TABLE_EMPLOYEE = 'employee';
    public const TABLE_EMPLOYEE_PERSONAL = 'employee_personal';

    /**
     * @var PDO|null
     */
    private ?PDO $db;

    /**
     * DBHelper constructor.
     */
    public function __construct()
    {
        try {
            $this->db = new PDO('mysql:host=localhost;dbname=employee-portal', 'root', 'vijay');
        } catch (PDOException $e) {
            die('Error!: ' . $e->getMessage());
        }
    }

    /**
     * @param $tableName
     * @param array $attributes
     * @return string
     */
    public function insert($tableName, array $attributes): string
    {
        if ($attributes === []) {
            #TODO: Check whether the $attribute array is associative array
            throw new \RuntimeException('$attributes cannot be blank');
        }
        $columns = array_keys($attributes);
        $values = array_values($attributes);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $tableName,
            implode(', ', $columns),
            implode(', ', array_fill(0, count($values), '?'))
        );

        $stmt = $this->db->prepare($sql);
        $stmt->execute($values);

        return $this->db->lastInsertId();
    }

    /**
     * @param $sql
     * @param array $params
     * @return bool|PDOStatement
     */
    public function prepare($sql, array $params = [])
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt;
    }
}