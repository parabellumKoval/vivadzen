<?php

namespace ParabellumKoval\Dumper\Services;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\DatabaseManager;

class TableInspector
{
    public function __construct(
        protected DatabaseManager $database,
        protected Repository $config
    ) {
    }

    public function connectionName(): string
    {
        return $this->config->get('dumper.connection', $this->database->getDefaultConnection());
    }

    /**
     * @return array<int, string>
     */
    public function tables(): array
    {
        $connection = $this->database->connection($this->connectionName());
        if ($connection->getDriverName() !== 'mysql') {
            return [];
        }

        $rows = $connection->select('SHOW FULL TABLES');

        $tables = [];
        foreach ($rows as $row) {
            $values = array_values((array) $row);
            $name = $values[0] ?? null;
            $type = strtoupper($values[1] ?? 'BASE TABLE');

            if (!$name) {
                continue;
            }

            if ($type === 'VIEW') {
                continue;
            }

            $tables[] = $name;
        }

        sort($tables);

        return $tables;
    }
}
