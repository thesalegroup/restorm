<?php
/*
 * The MIT License
 *
 * Copyright 2017 Rob Treacy <email@roberttreacy.com>.
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

use Robwasripped\Restorm\EntityManager;
use Robwasripped\Restorm\Query\Query;
use Robwasripped\Restorm\Mapping\EntityMapping;

/**
 * Description of QueryBuilder
 *
 * @author Rob Treacy <email@roberttreacy.com>
 */
class QueryBuilder
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $entityClass;

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
    private $filter = [];

    /**
     * @var array
     */
    private $sort = [];

    /**
     * @var int
     */
    private $page = 0;

    /**
     * @var int
     */
    private $perPage = 0;

    /**
     * @var string
     */
    private $path;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function get(string $entityClass): self
    {
        return $this->action(Query::METHOD_GET, $entityClass);
    }

    public function post(string $entityClass): self
    {
        return $this->action(Query::METHOD_POST, $entityClass);
    }

    public function put(string $entityClass): self
    {
        return $this->action(Query::METHOD_PUT, $entityClass);
    }

    public function patch(string $entityClass): self
    {
        return $this->action(Query::METHOD_PATCH, $entityClass);
    }

    public function delete(string $entityClass): self
    {
        return $this->action(Query::METHOD_DELETE, $entityClass);
    }

    public function action(string $method, string $entityClass): self
    {
        $this->method = $method;
        $this->entityClass = $entityClass;

        return $this;
    }

    public function where(array $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    public function sortBy(array $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function page(int $page, int $perPage = 0): self
    {
        $this->page = $page;
        $this->perPage = $perPage;

        return $this;
    }

    private function getEndpoint()
    {
        $entityMapping = $this->getEntityMapping($this->entityClass);

        $isSingle = array_key_exists($entityMapping->getIdentifierName(), $this->filter);

        switch ($this->method) {
            case Query::METHOD_GET && $isSingle:
                $pathLabel = EntityMapping::PATH_GET;
                break;
            case Query::METHOD_GET && !$isSingle:
                $pathLabel = EntityMapping::PATH_LIST;
                break;
        }
        $path = $entityMapping->getpath($pathLabel);

        if ($isSingle) {
            $path = sprintf('%s/%s', rtrim($path, '/'), ltrim($this->filter[$entityMapping->getIdentifierName()], '/'));
            unset($this->filter[$entityMapping->getIdentifierName()]);
        }

        return $path;
    }

    public function getQuery(): Query
    {
        $endpoint = $this->getEndpoint();
        $connections = $this->entityManager->getConnectionRegister()->getConnections($this->getEntityMapping($this->entityClass)->getConnection());
        $entityBuilder = $this->entityManager->getEntityBuilder();
        $eventDispatcher = $this->entityManager->getEventDispatcher();

        return new Query($connections, $eventDispatcher, $entityBuilder, $this->entityClass, $endpoint, $this->method, $this->data, $this->filter, $this->page, $this->perPage, $this->sort);
    }

    private function getEntityMapping(string $entityClass): EntityMapping
    {
        return $this->entityManager->getEntityMappingRegister()->getEntityMapping($entityClass);
    }
}
