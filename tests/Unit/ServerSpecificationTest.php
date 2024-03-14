<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Spec\Tests\Unit;

use LaravelJsonApi\Contracts\Schema\Container as SchemaContainer;
use LaravelJsonApi\Contracts\Schema\ID;
use LaravelJsonApi\Contracts\Schema\Schema;
use LaravelJsonApi\Contracts\Server\Server;
use LaravelJsonApi\Contracts\Store\Store;
use LaravelJsonApi\Spec\ServerSpecification;
use LaravelJsonApi\Spec\Tests\Integration\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ServerSpecificationTest extends TestCase
{

    /**
     * @var MockObject|Server
     */
    private MockObject $server;

    /**
     * @var ServerSpecification
     */
    private ServerSpecification $spec;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->server = $this->createMock(Server::class);
        $this->spec = new ServerSpecification($this->server);
    }

    public function testClientIds(): void
    {
        $this->server->method('schemas')->willReturn($schemas = $this->createMock(SchemaContainer::class));
        $schemas->method('schemaFor')->with('posts')->willReturn($schema = $this->createMock(Schema::class));
        $schema->method('id')->willReturn($id = $this->createMock(ID::class));
        $id->expects($this->exactly(2))->method('acceptsClientIds')->willReturnOnConsecutiveCalls(true, false);

        $this->assertTrue($this->spec->clientIds('posts'));
        $this->assertFalse($this->spec->clientIds('posts'));
    }

    public function testExists(): void
    {
        $this->server->method('store')->willReturn($store = $this->createMock(Store::class));

        $store->expects($this->exactly(3))->method('exists')->willReturnCallback(
            static fn($type, $id) => 'posts' === $type && '123' === $id
        );

        $this->assertTrue($this->spec->exists('posts', '123'));
        $this->assertFalse($this->spec->exists('users', '123'));
        $this->assertFalse($this->spec->exists('posts', '456'));
    }

    public function testFields(): void
    {
        $this->server->method('schemas')->willReturn($schemas = $this->createMock(SchemaContainer::class));

        $schemas
            ->expects($this->once())
            ->method('schemaFor')
            ->with('posts')
            ->willReturn($schema = $this->createMock(Schema::class));

        $schema->method('attributes')->willReturn(['foo' => 'foo', 'bar' => 'bar']);
        $schema->method('relationships')->willReturn(['baz' => 'baz', 'bat' => 'bat']);

        $this->assertSame(
            ['foo' => 'foo', 'bar' => 'bar', 'baz' => 'baz', 'bat' => 'bat'],
            collect($this->spec->fields('posts'))->all()
        );
    }

    public function testTypes(): void
    {
        $this->server->method('schemas')->willReturn($schemas = $this->createMock(SchemaContainer::class));
        $schemas->method('types')->willReturn($expected = ['posts', 'users']);

        $this->assertSame($expected, $this->spec->types());
    }
}
