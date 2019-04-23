<?php

namespace Glas;

class Persister
{

    public function __construct()
    {
        $this->db = new \SQLite3(__DIR__.'/../sqlite.db');

        $this->createTableIfNotExists();
    }

    private function createTableIfNotExists()
    {
        $result = $this->db->query('SELECT name FROM sqlite_master WHERE type=\'table\' AND name=\'result\'');

        if ($result->fetchArray() === false) {
            $this->db->exec('
                CREATE TABLE result(
                    application VARCHAR(255) NOT NULL,
                    up SMALLINT(1) NOT NULL
                )
            ');
        }
    }

    /**
     * @return bool true if "up" has changed since last check
     */
    public function storeResultIfChanged(Check $check)
    {
        $stmt = $this->db->prepare(
            'SELECT up FROM result WHERE application=:application'
        );
        $stmt->bindValue('application', $check->getApplication(), SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray();

        if ($result === false) {
            // first time, we consider result has not changed
            $stmt = $this->db->prepare(
                'INSERT INTO result VALUES (:application, :isUp)'
            );
            $stmt->bindValue('application', $check->getApplication(), SQLITE3_TEXT);
            $stmt->bindValue('isUp', $check->isUp(), SQLITE3_INTEGER);
            $stmt->execute();

            return false;
        }

        $result = (bool) $result['up'];

        if ($result == $check->isUp()) {
            // no change, nothing to do
            return false;
        }

        $stmt = $this->db->prepare(
            'UPDATE result SET up = :isUp WHERE application=:application'
        );
        $stmt->bindValue('application', $check->getApplication(), SQLITE3_TEXT);
        $stmt->bindValue('isUp', $check->isUp(), SQLITE3_INTEGER);
        $stmt->execute();

        return true; // result has changed
    }
}
