<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ONGR\ElasticsearchBundle\Tests\Functional\Result;

use Doctrine\Common\Collections\ArrayCollection;
use ONGR\App\Document\CollectionNested;
use ONGR\App\Document\DummyDocument;
use ONGR\ElasticsearchBundle\Result\ObjectIterator;
use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchBundle\Test\AbstractElasticsearchTestCase;

class MethodPropertyTest extends AbstractElasticsearchTestCase
{
    protected function getDataDocuments(): array
    {
        $document1     = new DummyDocument();
        $document1->id = 1;

        $document1_1     = new DummyDocument();
        $document1_1->id = 3;
        $document1_1->title = 'First embedded object';

        $document1_2     = new DummyDocument();
        $document1_2->id = 4;
        $document1_2->title = 'Second embedded object';

        $document1->setObjectCollection(new ArrayCollection([$document1_1, $document1_2]));

        $document2     = new DummyDocument();
        $document2->id = 2;

        return [
            DummyDocument::class => [
                $document1,
                $document2,
            ],
        ];
    }

    /**
     * Iteration test.
     */
    public function testIteration()
    {
        /** @var IndexService $index */
        $index = $this->getIndex(DummyDocument::class);

        $search = $index->createSearch();

        /** @var DummyDocument $document */
        $response = $index->findRaw($search);

        foreach ($response as $object) {
            if ($object['_id'] == 1) {
                $this->assertEquals('First embedded object', $object['_source']['first_object']['title']);
                $this->assertEquals(true, $object['_source']['has_id']);
                $this->assertEquals(true, $object['_source']['is_great']);
            }
            else if ($object['_id'] == 2) {
                $this->assertFalse(isset($object['_source']['first_object']));
                $this->assertEquals(true, $object['_source']['has_id']);
                $this->assertEquals(true, $object['_source']['is_great']);
            }
        }
    }
}
