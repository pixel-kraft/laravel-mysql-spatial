<?php

namespace Grimzy\LaravelMysqlSpatial\Eloquent;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\DB;
use PDO;

class SpatialExpression extends Expression
{
    protected bool $supportsCustomAxisOrder;
    public function __construct($value, ?Model $model)
    {
        $connection = $model ? $model->getConnection() : DB::connection();
        $this->supportsCustomAxisOrder = $this->checkCustomAxisSupport($connection);

        parent::__construct($value);
    }

    private function checkCustomAxisSupport(ConnectionInterface $connection): bool
    {
        /** @var MySqlConnection $connection */
        if ($connection->isMaria()) {
            return false;
        }
        if (version_compare($connection->getPdo()->getAttribute(PDO::ATTR_SERVER_VERSION), '5.8.0', '<')) {
            return false;
        }

        return true;
    }

    public function getValue()
    {
        return $this->supportsCustomAxisOrder ? "ST_GeomFromText(?, ?, 'axis-order=long-lat')" : "ST_GeomFromText(?, ?)";
    }

    public function getSpatialValue()
    {
        return $this->value->toWkt();
    }

    public function getSrid()
    {
        return $this->value->getSrid();
    }
}
