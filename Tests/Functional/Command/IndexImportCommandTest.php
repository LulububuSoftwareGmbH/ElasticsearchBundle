<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchBundle\Tests\Functional\Command;

use ONGR\ElasticsearchBundle\Command\IndexImportCommand;
use ONGR\ElasticsearchBundle\DSL\Query\MatchAllQuery;
use ONGR\ElasticsearchBundle\Test\ElasticsearchTestCase;
use org\bovigo\vfs\vfsStream;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class IndexImportCommandTest extends ElasticsearchTestCase
{
    /**
     * Test for index import command.
     */
    public function testIndexImport()
    {
        $app = new Application();
        $app->add($this->getImportCommand());

        $command = $app->find('es:index:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                '--raw' => true,
                'filename' => __DIR__ . '/../../app/fixture/Json/command_import_0.json',
            ]
        );

        $manager = $this->getManager('default', false);
        $repo = $manager->getRepository('AcmeTestBundle:Product');
        $search = $repo
            ->createSearch()
            ->addQuery(new MatchAllQuery());
        $results = $repo->execute($search);

        $ids = [];
        foreach ($results as $doc) {
            $ids[] = $doc->_id;
        }
        sort($ids);

        $this->assertEquals(['doc1', 'doc2'], $ids);
    }

    /**
     * Returns import index command with assigned container.
     *
     * @return IndexImportCommand
     */
    protected function getImportCommand()
    {
        $command = new IndexImportCommand();
        $command->setContainer($this->getContainer());

        return $command;
    }
}
