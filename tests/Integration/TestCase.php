<?php
/*
 * Copyright 2024 Cloud Creativity Limited
 *
 * Use of this source code is governed by an MIT-style
 * license that can be found in the LICENSE file or at
 * https://opensource.org/licenses/MIT.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Spec\Tests\Integration;

use Illuminate\Foundation\Testing\Concerns\InteractsWithDeprecationHandling;
use LaravelJsonApi\Contracts\Schema\Attribute;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Contracts\Support\Result;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Document\ErrorList;
use LaravelJsonApi\Spec\Document;
use LaravelJsonApi\Spec\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class TestCase extends BaseTestCase
{
    use InteractsWithDeprecationHandling;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutDeprecationHandling();
    }

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app): array
    {
        return [ServiceProvider::class];
    }

    /**
     * @param Document $document
     * @param array $expected
     * @deprecated
     */
    protected function assertInvalid(Document $document, array $expected): void
    {
        $this->assertFalse($document->valid());
        $this->assertTrue($document->invalid());
        $this->assertErrors($expected, $document->errors());
    }

    /**
     * Assert an error list.
     *
     * @param array $expected
     * @param ErrorList $errors
     */
    protected function assertErrors(array $expected, ErrorList $errors): void
    {
        $this->assertSame($expected, $errors->toArray());
    }

    /**
     * Assert a single error.
     *
     * @param array $expected
     * @param ErrorList|Error $errorOrErrors
     */
    protected function assertError(array $expected, ErrorList|Error $errorOrErrors): void
    {
        if ($errorOrErrors instanceof ErrorList) {
            $this->assertErrors([$expected], $errorOrErrors);
            return;
        }

        $this->assertEquals($expected, $errorOrErrors->toArray());
    }

    /**
     * @param Result $result
     * @return void
     */
    public function assertSuccessful(Result $result): void
    {
        $this->assertTrue($result->didSucceed());
        $this->assertFalse($result->didFail());
        $this->assertEmpty($result->errors());
    }

    /**
     * Assert a failed result with a single error.
     *
     * @param Result $result
     * @param array $expected
     * @return void
     */
    protected function assertFailedWithError(Result $result, array $expected): void
    {
        $this->assertTrue($result->didFail());
        $this->assertFalse($result->didSucceed());
        $this->assertError($expected, $result->errors());
    }

    /**
     * Assert a failed result with multiple errors.
     *
     * @param Result $result
     * @param array $expected
     * @return void
     */
    protected function assertFailedWithErrors(Result $result, array $expected): void
    {
        $this->assertTrue($result->didFail());
        $this->assertFalse($result->didSucceed());
        $this->assertErrors($expected, $result->errors());
    }

    /**
     * @param string $name
     * @return Attribute&MockObject
     */
    protected function createAttribute(string $name): Attribute&MockObject
    {
        $attr = $this->createMock(Attribute::class);
        $attr->method('name')->willReturn($name);

        return $attr;
    }

    /**
     * @param string $name
     * @param string $inverse
     * @return Relation&MockObject
     */
    protected function createToOne(string $name, string $inverse): Relation&MockObject
    {
        $relation = $this->createMock(Relation::class);
        $relation->method('name')->willReturn($name);
        $relation->method('inverse')->willReturn($inverse);
        $relation->method('allInverse')->willReturn([$inverse]);
        $relation->method('toOne')->willReturn(true);
        $relation->method('toMany')->willReturn(false);

        return $relation;
    }

    /**
     * @param string $name
     * @param string $inverse
     * @return Relation&MockObject
     */
    protected function createToMany(string $name, string $inverse): Relation&MockObject
    {
        $relation = $this->createMock(Relation::class);
        $relation->method('name')->willReturn($name);
        $relation->method('inverse')->willReturn($inverse);
        $relation->method('allInverse')->willReturn([$inverse]);
        $relation->method('toOne')->willReturn(false);
        $relation->method('toMany')->willReturn(true);

        return $relation;
    }
}
