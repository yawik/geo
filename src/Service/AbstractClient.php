<?php
/**
 * YAWIK
 *
 * @filesource
 * @license MIT
 * @copyright  2013 - 2017 Cross Solution <http://cross-solution.de>
 */

/** */
namespace Geo\Service;

use Zend\Http\Client;
use Zend\Json\Json;

/**
 * ${CARET}
 *
 * @author Mathias Gelhausen <gelhausen@cross-solution.de>
 * @todo write test
 */
class AbstractClient
{
    /**
     *
     *
     * @var \Zend\Http\Client
     */
    protected $client;

    /**
     *
     *
     * @var \Zend\Cache\Storage\Adapter\AbstractAdapter
     */
    protected $cache;

    protected $country;


    public function __construct($uri, $country="DE", $cache = false)
    {
        $this->country = $country;
        $this->client = $this->setupClient($uri);
        $this->cache = $cache;

//        $namespace = explode('\\', get_class($this));
//        $namespace = array_pop($namespace);
//        //$this->cache->setOptions(['namespace' => $namespace]);
    }

    protected function setupClient($uri)
    {
        return new Client($uri);
    }

    protected function preQuery($term, array $options)
    {
        $params['q'] = $term;
        $this->client->setParameterGet($options['params'] ?? []);
    }

    public function query($term, array $options = [])
    {
        /* @TODO: [ZF3] overriding $term value because it always returns null */
        if (is_null($term)) {
            $term = $_REQUEST['q'];
        }

        $cacheId = md5($term . serialize($options));

        if ($this->cache && ($result = $this->cache->getItem($cacheId))) {
            return $result;
        }

        $this->preQuery($term, $options);

        $response = $this->client->send();
        if ($response->getStatusCode() !== 200) {
            throw new \RuntimeException('Query failed, because ' . $response->getReasonPhrase());
        }

        $result = $response->getBody();
        $result = $this->processResult($result, $options);

        if ($this->cache) {
            $this->cache->setItem($cacheId, $result);
        }

        return $result;
    }

    public function queryOne($term, array $options = [])
    {
        $result = $this->query($term, $options);

        return isset($result[0]) ? $result[0] : false;
    }

    protected function processResult($result, array $options = [])
    {
        return $result;
    }
}
