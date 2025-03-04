<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Grid\Exception\OrderingNotSupportedException;
use Shopsys\FrameworkBundle\Component\Grid\Exception\PaginationNotSupportedException;
use Shopsys\FrameworkBundle\Component\Grid\Exception\RowNotFoundInGridByIdException;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class ArrayDataSource implements DataSourceInterface
{
    /**
     * @param array $data
     * @param string|null $rowIdSourceColumnName
     */
    public function __construct(
        protected array $data,
        protected ?string $rowIdSourceColumnName = null,
    ) {
    }

    /**
     * @return string
     */
    public function getRowIdSourceColumnName(): string
    {
        return $this->rowIdSourceColumnName;
    }

    /**
     * @param int $rowId
     * @return array
     */
    public function getOneRow(int $rowId): array
    {
        if ($this->rowIdSourceColumnName === null) {
            return $this->data[$rowId];
        }

        foreach ($this->data as $item) {
            if ($item[$this->rowIdSourceColumnName] === $rowId) {
                return $item;
            }
        }

        throw new RowNotFoundInGridByIdException(sprintf('Row with id "%s" not found', $rowId));
    }

    /**
     * @param int|null $limit
     * @param int $page
     * @param string|null $orderSourceColumnName
     * @param string $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        string $orderDirection = self::ORDER_ASC,
    ): PaginationResult {
        if ($limit !== null) {
            $message = 'Pagination not supported in ArrayDataSource';

            throw new PaginationNotSupportedException($message);
        }

        if ($orderSourceColumnName !== null) {
            $message = 'Ordering not supported in ArrayDataSource';

            throw new OrderingNotSupportedException($message);
        }

        return new PaginationResult(1, count($this->data), count($this->data), $this->data);
    }

    /**
     * @return int
     */
    public function getTotalRowsCount(): int
    {
        return count($this->data);
    }
}
