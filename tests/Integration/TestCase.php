<?php
/*
 * Copyright 2021 Cloud Creativity Limited
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);

namespace LaravelJsonApi\Spec\Tests\Integration;

use LaravelJsonApi\Contracts\Schema\Attribute;
use LaravelJsonApi\Contracts\Schema\Relation;
use LaravelJsonApi\Core\Document\Error;
use LaravelJsonApi\Core\Document\ErrorList;
use LaravelJsonApi\Spec\Document;
use LaravelJsonApi\Spec\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{

    /**
     * @inheritDoc
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    /**
     * @param Document $document
     * @param array $expected
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
     * @param $errorOrErrors
     */
    protected function assertError(array $expected, $errorOrErrors): void
    {
        if ($errorOrErrors instanceof ErrorList) {
            $this->assertErrors([$expected], $errorOrErrors);
        } else {
            $this->assertInstanceOf(Error::class, $errorOrErrors);
            $this->assertEquals($expected, $errorOrErrors->toArray());
        }
    }

    /**
     * @param string $name
     * @return Attribute
     */
    protected function createAttribute(string $name): Attribute
    {
        $attr = $this->createMock(Attribute::class);
        $attr->method('name')->willReturn($name);

        return $attr;
    }

    /**
     * @param string $name
     * @param string $inverse
     * @return Relation
     */
    protected function createToOne(string $name, string $inverse): Relation
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
     * @return Relation
     */
    protected function createToMany(string $name, string $inverse): Relation
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
