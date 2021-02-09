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

use LaravelJsonApi\Spec\RelationBuilder;
use LaravelJsonApi\Spec\Specification;

class ToManyTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(Specification::class, $spec = $this->createMock(Specification::class));

        $spec->method('exists')->willReturnCallback(fn($type, $id) => '999' !== $id);
        $spec->method('fields')->willReturnMap([
            ['posts', [
                $this->createToMany('tags'),
            ]],
            ['tags', [
                $this->createAttribute('name'),
            ]],
        ]);
        $spec->method('types')->willReturn(['posts', 'tags']);
    }

    /**
     * @return array
     */
    public function toManyProvider(): array
    {
        return [
            'data:required' => [
                new \stdClass(),
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member data is required.",
                    'status' => '400',
                    'source' => ['pointer' => '/'],
                ],
            ],
            'data:to-one' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'id' => '123',
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The field tags must be a to-many relation.",
                    'status' => '400',
                    'source' => ['pointer' => '/data'],
                ],
            ],
            'data:not array' => [
                [
                    'data' => false,
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member data must be an array.",
                    'status' => '400',
                    'source' => ['pointer' => '/data'],
                ],
            ],
            'data.type:required' => [
                [
                    'data' => [
                        ['id' => '1'],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member type is required.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/0'],
                ],
            ],
            'data.type:not string' => [
                [
                    'data' => [
                        ['type' => null, 'id' => '1'],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member type must be a string.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/0/type'],
                ],
            ],
            'data.type:empty' => [
                [
                    'data' => [
                        ['type' => '', 'id' => '1'],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member type cannot be empty.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/0/type'],
                ],
            ],
            'data.type:not recognised' => [
                [
                    'data' => [
                        ['type' => 'foobar', 'id' => '1'],
                    ],
                ],
                [
                    'title' => 'Not Supported',
                    'detail' => "Resource type foobar is not recognised.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/0/type'],
                ],
            ],
            'data.id:required' => [
                [
                    'data' => [
                        ['type' => 'tags'],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member id is required.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/0'],
                ],
            ],
            'data.id:not string' => [
                [
                    'data' => [
                        ['type' => 'tags', 'id' => null],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member id must be a string.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/0/id'],
                ],
            ],
            'data.id:integer' => [
                [
                    'data' => [
                        ['type' => 'tags', 'id' => 1],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member id must be a string.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/0/id'],
                ],
            ],
            'data.id:empty' => [
                [
                    'data' => [
                        ['type' => 'tags', 'id' => ''],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member id cannot be empty.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/0/id'],
                ],
            ],
            'data:does not exist' => [
                [
                    'data' => [
                        ['type' => 'tags', 'id' => '999'],
                    ],
                ],
                [
                    'title' => 'Not Found',
                    'detail' => 'The related resource does not exist.',
                    'status' => '404',
                    'source' => ['pointer' => '/data/0'],
                ],
            ],
            'data:resource object with attributes' => [
                [
                    'data' => [
                        [
                            'type' => 'tags',
                            'id' => '100',
                            'attributes' => [
                                'name' => 'News',
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => 'The member 0 must be a resource identifier.',
                    'status' => '400',
                    'source' => ['pointer' => '/data/0'],
                ],
            ],
        ];
    }

    /**
     * @param $json
     * @param array $expected
     * @dataProvider toManyProvider
     */
    public function testToMany($json, array $expected): void
    {
        ksort($expected);

        /** @var RelationBuilder $builder */
        $builder = $this->app->make(RelationBuilder::class);

        $document = $builder
            ->expects('posts', 'tags')
            ->build(json_encode($json));

        $this->assertInvalid($document, [$expected]);
    }
}
