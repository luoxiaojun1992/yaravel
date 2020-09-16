<?php

namespace App\Domains\Base\Repositories;

use App\Domains\User\Repositories\Customer\Repository;
use App\Domains\Common\Support\YiiCache\YiiTokenCache;
use Elasticsearch;

class BaseEsRepository
{
    //Elasticsearch Index
    protected $index;

    //Elasticsearch Type
    protected $type;

    //Elasticsearch Connection Name
    protected $connection;

    public function __construct()
    {
        \Registry::set(static::class, $this);
    }

    /**
     * @param mixed $body
     * @param int $mid
     * @return array
     */
    public function search($body, $mid = 0)
    {
        $esInfo = $this->getEsInfo($mid);

        $params = [
            'index' => $esInfo['index'],
            'type' => $this->type,
            'body' => $body
        ];

        /** @var Elasticsearch\Client $esClient */
        $esClient = $esInfo['esClient'];
        return $esClient->search($params);
    }

    /**
     * @param mixed $body
     * @param int $mid
     * @return array
     */
    public function count($body, $mid = 0)
    {
        $esInfo = $this->getEsInfo($mid);

        $params = [
            'index' => $esInfo['index'],
            'type' => $this->type,
            'body' => $body
        ];

        /** @var Elasticsearch\Client $esClient */
        $esClient = $esInfo['esClient'];
        return $esClient->count($params);
    }

    /**
     * @param $body
     * @param int $mid
     * @return array
     */
    public function getScrollId($body, $mid = 0)
    {
        $esInfo = $this->getEsInfo($mid);

        $params = [
            'index' => $esInfo['index'],
            'type' => $this->type,
            'body' => $body,
            'scroll' => '10m'
        ];

        /** @var Elasticsearch\Client $esClient */
        $esClient = $esInfo['esClient'];
        $esResults =  $esClient->search($params);
        if(isset($esResults["_scroll_id"])){
            return [
                'result' => $esResults['hits']['hits'],
                'scroll_id'=> $esResults["_scroll_id"],
                'count'=> $esResults['hits']['total']
            ];
        }

        return [];
    }

    /**
     * Get es index and client
     *
     * @param int $mid
     * @return array
     */
    protected function getEsInfo($mid = 0)
    {
        //Default es info
        if (is_null($this->connection)) {
            $connection = config('elasticsearch.defaultConnection') ?: 'default';
        } else {
            $connection = $this->connection;
        }
        $configTpl = config('elasticsearch.connections.' . $connection);
        //replace index with default index if repo index not be set
        if (is_null($index = $this->index)) {
            $index = $configTpl['index'] ?? null;
        }

        //Replace es info with customer es info if mid > 0
        $usingCustomerEsInfo = false;
        if ($mid > 0) {
            if (YiiTokenCache::hexists("elasticsearch_index", $mid)) { //Fetch customer es info from cache
                $esIndexJson = YiiTokenCache::hget("elasticsearch_index", $mid);
                $esIndexJson = json_decode($esIndexJson, true);
                $host = $esIndexJson['host'];
                $configTpl['hosts'][0]['host'] = $host;

                //replace index with customer index if repo index not be set
                if (is_null($this->index)) {
                    $index = $esIndexJson['index'];
                    $configTpl['index'] = $index;
                } else {
                    $configTpl['index'] = $this->index;
                }

                $connection = $host;
                if (\Config::has('elasticsearch.connections.' . $connection)) {
                    $usingCustomerEsInfo = false;
                } else {
                    $usingCustomerEsInfo = true;
                }
                config(['elasticsearch.connections.' . $connection => $configTpl]);
            } else { //Fetch customer es info from db
                /** @var Repository $customerRepo */
                $customerRepo = di(Repository::class);
                $customer = $customerRepo->fetchOne([
                    'id' => $mid,
                ], ['elastic_search_index_id']);
                if ($customer && $customer['elastic_search_index_id']) {
                    /** @var \App\Domains\Base\Repositories\ElasticSearchIndex\Repository $esIndexRepo */
                    $esIndexRepo = di(\App\Domains\Base\Repositories\ElasticSearchIndex\Repository::class);
                    $esIndexObj = $esIndexRepo->fetchOne([
                        'id' => $customer['elastic_search_index_id'],
                    ], ['es_index_host', 'es_index_name']);
                    if ($esIndexObj) {
                        $host = $esIndexObj['es_index_host'];
                        $configTpl['hosts'][0]['host'] = $host;

                        //replace index with customer index if repo index not be set
                        if (is_null($this->index)) {
                            $index = $esIndexObj['es_index_name'];
                            $configTpl['index'] = $index;
                        } else {
                            $configTpl['index'] = $this->index;
                        }

                        $connection = $host;
                        if (\Config::has('elasticsearch.connections.' . $connection)) {
                            $usingCustomerEsInfo = false;
                        } else {
                            $usingCustomerEsInfo = true;
                        }
                        config(['elasticsearch.connections.' . $connection => $configTpl]);
                        YiiTokenCache::hset(
                            'elasticsearch_index',
                            $mid,
                            json_encode(['host' => $host, 'index' => $esIndexObj['es_index_name']])
                        );
                    }
                }
            }
        }

        if ($usingCustomerEsInfo) {
            if (Elasticsearch::hasConnection($connection)) {
                $usingCustomerEsInfo = false;
            }
        }

        $esClient = Elasticsearch::connection($connection);

        if ($usingCustomerEsInfo) {
            if (Elasticsearch::countConnections() > Elasticsearch::maxConnections()) {
                Elasticsearch::removeConnection($connection);
                \Config::forget('elasticsearch.connections.' . $connection);
            }
        }

        return compact('index', 'esClient');
    }
}
