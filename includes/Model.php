<?php

abstract class Model {
    protected static $table;
    protected static $fillable = [];
    protected $attributes = [];
    
    public function __construct(array $attributes = []) {
        $this->fill($attributes);
    }
    
    public function fill(array $attributes) {
        foreach ($attributes as $key => $value) {
            if (in_array($key, static::$fillable)) {
                $this->attributes[$key] = $value;
            }
        }
    }
    
    public function __get($name) {
        return $this->attributes[$name] ?? null;
    }
    
    public function __set($name, $value) {
        if (in_array($name, static::$fillable)) {
            $this->attributes[$name] = $value;
        }
    }
    
    public static function find($id) {
        $db = Database::getInstance();
        $result = $db->fetch("SELECT * FROM " . static::$table . " WHERE id = ?", [$id]);
        return $result ? new static($result) : null;
    }
    
    public static function findBy($column, $value) {
        $db = Database::getInstance();
        $result = $db->fetch(
            "SELECT * FROM " . static::$table . " WHERE $column = ?",
            [$value]
        );
        return $result ? new static($result) : null;
    }
    
    public static function all() {
        $db = Database::getInstance();
        $results = $db->fetchAll("SELECT * FROM " . static::$table);
        return array_map(function($result) {
            return new static($result);
        }, $results);
    }
    
    public function save() {
        $db = Database::getInstance();
        
        if (isset($this->attributes['id'])) {
            // Update
            $id = $this->attributes['id'];
            unset($this->attributes['id']);
            return $db->update(
                static::$table,
                $this->attributes,
                'id = ?',
                [$id]
            );
        } else {
            // Insert
            $id = $db->insert(static::$table, $this->attributes);
            $this->attributes['id'] = $id;
            return $id;
        }
    }
    
    public function delete() {
        if (!isset($this->attributes['id'])) {
            return false;
        }
        
        $db = Database::getInstance();
        return $db->query(
            "DELETE FROM " . static::$table . " WHERE id = ?",
            [$this->attributes['id']]
        );
    }
    
    public static function paginate($page = 1, $perPage = 10) {
        $db = Database::getInstance();
        $offset = ($page - 1) * $perPage;
        
        $total = $db->fetch("SELECT COUNT(*) as count FROM " . static::$table)['count'];
        $results = $db->fetchAll(
            "SELECT * FROM " . static::$table . " LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        return [
            'data' => array_map(function($result) {
                return new static($result);
            }, $results),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }
    
    public static function where($conditions, $params = []) {
        $db = Database::getInstance();
        $results = $db->fetchAll(
            "SELECT * FROM " . static::$table . " WHERE " . $conditions,
            $params
        );
        return array_map(function($result) {
            return new static($result);
        }, $results);
    }
    
    public function toArray() {
        return $this->attributes;
    }
}
