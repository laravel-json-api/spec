<?php
/*
 * Copyright 2023 Cloud Creativity Limited
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

class ToOneTest extends TestCase
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
                $this->createToOne('author', 'users'),
            ]],
            ['users', [
                $this->createAttribute('name'),
            ]],
        ]);
        $spec->method('types')->willReturn(['posts', 'users']);
    }

    /**
     * @return array
     */
    public function toOneProvider(): array
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
            'data:not object' => [
                ['data' => false],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member data must be an object.",
                    'status' => '400',
                    'source' => ['pointer' => '/data'],
                ],
            ],
            'data.type:required' => [
                [
                    'data' => [
                        'id' => '1',
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member type is required.",
                    'status' => '400',
                    'source' => ['pointer' => '/data'],
                ],
            ],
            'data.type:not string' => [
                [
                    'data' => [
                        'type' => null,
                        'id' => '1',
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member type must be a string.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/type'],
                ],
            ],
            'data.type:empty' => [
                [
                    'data' => [
                        'type' => '',
                        'id' => '1',
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member type cannot be empty.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/type'],
                ],
            ],
            'data.type:not recognised' => [
                [
                    'data' => [
                        'type' => 'foobar',
                        'id' => '1',
                    ],
                ],
                [
                    'title' => 'Not Supported',
                    'detail' => "Resource type foobar is not recognised.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/type'],
                ],
            ],
            'data.type:not supported' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'id' => '1',
                    ],
                ],
                [
                    'title' => 'Unprocessable Entity',
                    'detail' => 'The author field must be a to-one relationship containing users resources.',
                    'status' => '422',
                    'source' => ['pointer' => '/data'],
                ],
            ],
            'data.id:required' => [
                [
                    'data' => [
                        'type' => 'users',
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member id is required.",
                    'status' => '400',
                    'source' => ['pointer' => '/data'],
                ],
            ],
            'data.id:not string' => [
                [
                    'data' => [
                        'type' => 'users',
                        'id' => null,
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member id must be a string.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/id'],
                ],
            ],
            'data.id:integer' => [
                [
                    'data' => [
                        'type' => 'users',
                        'id' => 1,
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member id must be a string.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/id'],
                ],
            ],
            'data.id:empty' => [
                [
                    'data' => [
                        'type' => 'users',
                        'id' => '',
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member id cannot be empty.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/id'],
                ],
            ],
            'data:does not exist' => [
                [
                    'data' => [
                        'type' => 'users',
                        'id' => '999',
                    ],
                ],
                [
                    'title' => 'Not Found',
                    'detail' => 'The related resource does not exist.',
                    'status' => '404',
                    'source' => ['pointer' => '/data'],
                ],
            ],
            'data:resource object with attributes' => [
                [
                    'data' => [
                        'type' => 'users',
                        'id' => '1',
                        'attributes' => [
                            'name' => 'John Doe',
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => 'The member data must be a resource identifier.',
                    'status' => '400',
                    'source' => ['pointer' => '/data'],
                ],
            ],
            'data:resource object with relationships' => [
                [
                    'data' => [
                        'type' => 'users',
                        'id' => '1',
                        'relationships' => [
                            'sites' => [
                                'data' => [],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => 'The member data must be a resource identifier.',
                    'status' => '400',
                    'source' => ['pointer' => '/data'],
                ],
            ],
            'data:to-many' => [
                [
                    'data' => [
                        [
                            'type' => 'users',
                            'id' => '1',
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => 'The field author must be a to-one relation.',
                    'status' => '400',
                    'source' => ['pointer' => '/data'],
                ],
            ],
        ];
    }

    /**
     * @param $json
     * @param array $expected
     * @dataProvider toOneProvider
     */
    public function testToOne($json, array $expected): void
    {
        ksort($expected);

        /** @var RelationBuilder $builder */
        $builder = $this->app->make(RelationBuilder::class);

        $document = $builder
            ->expects('posts', 'author')
            ->build(json_encode($json));

        $this->assertInvalid($document, [$expected]);
    }
}
