<?php

declare(strict_types=1);

namespace Framework\Database;

use Envms\FluentPDO\Queries\Select;
use Pagerfanta\Adapter\AdapterInterface;

/**
 * @template T
 * @implements \Pagerfanta\Adapter\AdapterInterface<T>
 */
final class PaginatedQuery implements AdapterInterface
{
    public function __construct(
        readonly private Select $query,
        readonly private Select $countQuery,
        readonly private ?string $entity
    ) {
    }

    /**
     * get the number of items
     *
     * @return int<0, max>
     */
    public function getNbResults(): int
    {
        $nbResults = (int)$this->countQuery->fetchColumn();
        assert($nbResults >= 0);
        return $nbResults;
    }

    /**
     * Retrieve items corresponding to the number of items per page
     *
     * @return iterable<array-key, T>
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $statement = $this->query->limit($length)->offset($offset)->execute();
        if ($this->entity) {
            $statement->setFetchMode(\PDO::FETCH_CLASS, $this->entity);
        }
        return $statement->fetchAll();
    }
}
