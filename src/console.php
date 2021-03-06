<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Doctrine\DBAL\Schema\Comparator;

$console = new Application('Zelten Console', '1.0');

$console
    ->register('doctrine:schema:update')
    ->setDescription('Update Doctrine Schema to match current definition.')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $conn       = $app['db'];
        $fromSchema = $conn->getSchemaManager()->createSchema();
        $toSchema   = $app['tent.user_storage']->createSchema();

        $userTable = $toSchema->createTable('users');
        $userTable->addColumn('entity', 'string');
        $userTable->addColumn('twitter_oauth_token', 'tentecstring', array('notnull' => false));
        $userTable->addColumn('twitter_oauth_secret', 'tentecstring', array('notnull' => false));
        $userTable->addColumn('last_login', 'datetime');
        $userTable->addColumn('login_count', 'integer', array('default' => 0));
        $userTable->addColumn('bookmarks', 'integer', array('default' => 0));
        $userTable->setPrimaryKey(array('entity'));

        $comp = new Comparator();
        $diff = $comp->compare($fromSchema, $toSchema);

        foreach ($diff->toSQL($conn->getDatabasePlatform()) as $sql) {
            $output->writeln($sql);
            $conn->exec($sql);
        }
    })
;

$console
    ->register('tent:application')
    ->addArgument('server', InputArgument::REQUIRED, null, null)
    ->setDescription('Show details of the application on a given tent server')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $serverUrl = $input->getArgument('server');
        $client = $app['tent.client'];
        $client->updateApplication($serverUrl);
    });

return $console;
