<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Tests\Unit\Indices;

use Elasticsearch\Client as ElasticsearchClient;
use Elasticsearch\Namespaces\IndicesNamespace;
use Zvonchuk\Elastic\Indices\CreateRequest;
use Zvonchuk\Elastic\Indices\Indices;
use Zvonchuk\Elastic\Indices\UpdateAliasesRequest;
use Zvonchuk\Elastic\Tests\Unit\TestCase;

final class AliasesTest extends TestCase
{
    public function testCreateWithSettingsMappingsAndAlias(): void
    {
        $request = (new CreateRequest('kyc_v2'))
            ->settings(['number_of_shards' => 1])
            ->mappings(['dynamic' => 'strict', 'properties' => ['name' => ['type' => 'text']]])
            ->alias('kyc_write', ['is_write_index' => true])
            ->alias('kyc_all');

        self::assertRenders(
            '{"index":"kyc_v2","body":{"settings":{"number_of_shards":1},"mappings":{"dynamic":"strict","properties":{"name":{"type":"text"}}},'
            . '"aliases":{"kyc_write":{"is_write_index":true},"kyc_all":{}}}}',
            $request->toArray(),
        );
    }

    public function testUpdateAliasesBody(): void
    {
        $request = (new UpdateAliasesRequest())->add('kyc_v2', 'kyc')->remove('kyc_v1', 'kyc');

        self::assertRenders(
            '{"body":{"actions":[{"add":{"index":"kyc_v2","alias":"kyc"}},{"remove":{"index":"kyc_v1","alias":"kyc"}}]}}',
            $request->toArray(),
        );
    }

    public function testSwapAliasIsOneAtomicCall(): void
    {
        $namespace = $this->createMock(IndicesNamespace::class);
        $namespace->method('existsAlias')->willReturn(true);
        $namespace->method('getAlias')->willReturn(['kyc_v1' => ['aliases' => ['kyc' => []]]]);
        $namespace->expects(self::once())->method('updateAliases')->with(['body' => ['actions' => [
            ['add' => ['index' => 'kyc_v2', 'alias' => 'kyc']],
            ['remove' => ['index' => 'kyc_v1', 'alias' => 'kyc']],
        ]]])->willReturn(['acknowledged' => true]);
        $elastic = $this->createMock(ElasticsearchClient::class);
        $elastic->method('indices')->willReturn($namespace);

        self::assertSame(['kyc_v1'], (new Indices($elastic))->swapAlias('kyc', 'kyc_v2'));
    }
}
