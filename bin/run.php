<?php

use Glas\MailNotifier;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Yaml\Yaml;
use Glas\Check;
use Glas\Checker;
use Glas\Persister;

require __DIR__.'/../vendor/autoload.php';

// run once at a time
$factory = new Factory(new SemaphoreStore());
$lockKey = 'Glas';
$lock = $factory->createLock($lockKey);

if (!$lock->acquire()) {
    echo sprintf('Lock could not be acquired for key "%s". Stopping.', $lockKey).PHP_EOL;
    return 0;
}

$config = Yaml::parseFile(__DIR__.'/../config.yml');

$persister = new Persister();
$checker = new Checker();
$notifier = new MailNotifier();

foreach ($config['config']['applications'] as $application => $value) {
    echo '-----'.$application.PHP_EOL;

    $check = new Check($application);
    $checker->execute($check);
    $hasChanged = $persister->storeResultIfChanged($check);

    echo $check->isUp() ? $application.' OK'.PHP_EOL : $application.' KO'.PHP_EOL;

    if ($hasChanged) {
        echo 'Sending notification...'.PHP_EOL;
        $notifier->notify($check);
    }
}

$lock->release();
