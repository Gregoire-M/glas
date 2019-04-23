<?php

namespace Glas;

use GuzzleHttp\Client;
use Symfony\Component\Yaml\Yaml;
use Tolerance\Operation\Callback;
use Tolerance\Operation\Runner\CallbackOperationRunner;
use Tolerance\Operation\Runner\RetryOperationRunner;
use Tolerance\Waiter\CountLimited;
use Tolerance\Waiter\SleepWaiter;

class Checker
{
    /**
     * @var array
     */
    private $config = [];

    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->config = Yaml::parseFile(__DIR__.'/../config.yml');
        $this->client = new Client();
    }

    public function execute(Check $check): Check
    {
        $operation = new Callback(function() use ($check) {
            return $this->client->request(
                'GET',
                $this->config
                    ['config']
                    ['applications']
                    [$check->getApplication()]
            );
        });

        // 3 retries, 1 sec each
        $waitStrategy = new CountLimited(
            new SleepWaiter(),
            3
        );

        $runner = new RetryOperationRunner(
            new CallbackOperationRunner(),
            $waitStrategy
        );

        try {
            $response = $runner->run($operation);
        } catch (\Exception $e) {
            echo $e->getMessage().PHP_EOL;

            return $check->setIsUp(false);
        }

        return $check->setIsUp($response->getStatusCode() == 200);
    }
}
