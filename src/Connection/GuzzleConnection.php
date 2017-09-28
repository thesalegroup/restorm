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

namespace TheSaleGroup\Restorm\Connection;

use TheSaleGroup\Restorm\Query\Query;
use GuzzleHttp\Client;

/**
 * Description of Connection
 *
 * @author Rob Treacy <email@roberttreacy.com>
 */
class GuzzleConnection implements ConnectionInterface
{
    /**
     * @var Client
     */
    private $guzzleClient;
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        $this->guzzleClient = new Client([
            'base_uri' => rtrim($config['base_uri'], '/') . '/'
        ]);
    }

    public function handleQuery(Query $query)
    {
        $path = ltrim($query->getPath(), '/');

        $options = array();

        if ($this->config['filter_mode'] === 'query') {
            $options['query'] = $query->getFilter();
        }

        $options['body'] = json_encode($query->getData());

        if (isset($this->config['pagination_parameters'])) {
            if ($query->getPage()) {
                $options['query'][$this->config['pagination_parameters']['page_param']] = $query->getPage();
            }
            if ($query->getPerPage()) {
                $options['query'][$this->config['pagination_parameters']['per_page_param']] = $query->getPerPage();
            }
        }

        $response = $this->guzzleClient->request($query->getMethod(), $path, $options);

        return json_decode($response->getBody()->getContents());
    }
}
