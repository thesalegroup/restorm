<?php
/*
 * The MIT License
 *
 * Copyright 2017 Rob Treacy <robert.treacy@thesalegroup.co.uk>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Robwasripped\Restorm\Query;

use Robwasripped\Restorm\Connection\ConnectionInterface;
use Robwasripped\Restorm\Mapping\EntityBuilder;

/**
 * Description of Query
 *
 * @author Rob Treacy <robert.treacy@thesalegroup.co.uk>
 */
class Query
{
    // Method constants
    const METHOD_GET = 'GET';
    const METHOD_POST = 'POST';
    const METHOD_PUT = 'PUT';
    const METHOD_PATCH = 'PATCH';
    const METHOD_DELETE = 'DELETE';
    // Sort constants
    const SORT_ASCENDING = 'ASC';
    const SORT_DESCENDING = 'DESC';

    /**
     * @var string
     */
    private $method;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var array
     */
    private $filter;

    /**
     * @var array
     */
    private $sort;

    /**
     * @var int
     */
    private $page;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var string
     */
    private $path;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * @var EntityBuilder
     */
    private $entityBuilder;

    /**
     * @var string
     */
    private $entityClass;

    public function __construct(ConnectionInterface $connection, EntityBuilder $entityBuilder, string $entityClass, string $path, string $method, $data, array $filter = [], int $page = 1, int $perPage = 0, array $sort = [])
    {
        $this->connection = $connection;
        $this->entityBuilder = $entityBuilder;
        $this->entityClass = $entityClass;
        $this->path = $path;
        $this->method = $method;
        $this->data = $data;
        $this->filter = $filter;
        $this->page = $page;
        $this->perPage = $perPage;
        $this->sort = $sort;
    }

    public function getResult()
    {
        $result = $this->connection->handleQuery($this);

        if (is_array($result)) {
            $entities = array();
            foreach ($result as $singleResult) {
                $entities[] = $this->entityBuilder->buildEntity($this->entityClass, $singleResult);
            }

            return $entities;
        } else {
            return $this->entityBuilder->buildEntity($this->entityClass, $result);
        }
    }

    function getMethod()
    {
        return $this->method;
    }

    function getPath()
    {
        return $this->path;
    }

    function getFilter()
    {
        return $this->filter;
    }
}
