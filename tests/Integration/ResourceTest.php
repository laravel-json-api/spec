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

use LaravelJsonApi\Core\Exceptions\JsonApiException;
use LaravelJsonApi\Spec\ResourceBuilder;
use LaravelJsonApi\Spec\Specification;
use LogicException;

class ResourceTest extends TestCase
{

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance(Specification::class, $spec = $this->createMock(Specification::class));

        $spec->method('clientIds')->willReturnCallback(fn($type) => 'podcasts' === $type);
        $spec->method('exists')->willReturnCallback(
            fn($type, $id) => !in_array($id, ['0', '999'], true)
        );
        $spec->method('fields')->willReturnMap([
            ['posts', [
                $this->createAttribute('title'),
                $this->createAttribute('content'),
                $this->createAttribute('slug'),
                $this->createToOne('author', 'users'),
                $this->createToMany('tags', 'tags'),
            ]],
            ['users', [
                $this->createAttribute('name'),
            ]],
            ['podcasts', [
                $this->createAttribute('title'),
            ]],
        ]);
        $spec->method('types')->willReturn(['posts', 'users', 'comments', 'podcasts', 'tags']);
    }

    /**
     * @return array
     */
    public static function emptyIdProvider(): array
    {
        return [
            [''],
            [' '],
            ['      '],
        ];
    }

    /**
     * @return array[]
     */
    public static function createProvider(): array
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
                ['data' => []],
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
                        'attributes' => ['title' => 'Hello World'],
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
                        'attributes' => ['title' => 'Hello World'],
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
                        'attributes' => ['title' => 'Hello World'],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member type cannot be empty.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/type'],
                ],
            ],
            'data.type:not supported' => [
                [
                    'data' => [
                        'type' => 'users',
                        'attributes' => ['name' => 'John Doe'],
                    ],
                ],
                [
                    'title' => 'Not Supported',
                    'detail' => "Resource type users is not supported by this endpoint.",
                    'status' => '409',
                    'source' => ['pointer' => '/data/type'],
                ],
            ],
            'data.id:client id not allowed' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'id' => '999', // does not exist.
                        'attributes' => ['title' => 'Hello World'],
                    ],
                ],
                [
                    'title' => 'Not Supported',
                    'detail' => 'Resource type posts does not support client-generated IDs.',
                    'status' => '403',
                    'source' => ['pointer' => '/data/id'],
                ],
            ],
            'data.attributes:not object' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member attributes must be an object.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/attributes'],
                ],
            ],
            'data.attributes:type not allowed' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'type' => 'foo',
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member attributes cannot have a type field.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/attributes'],
                ],
            ],
            'data.attributes:id not allowed' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'id' => '123',
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member attributes cannot have a id field.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/attributes'],
                ],
            ],
            'data.attributes.*:unrecognised' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'foo' => 'bar',
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => 'The field foo is not a supported attribute.',
                    'status' => '400',
                    'source' => ['pointer' => '/data/attributes'],
                ],
            ],
            'data.relationships:not object' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member relationships must be an object.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships'],
                ],
            ],
            'data.relationships:type not allowed' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'relationships' => [
                            'type' => [
                                'data' => null,
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member relationships cannot have a type field.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships'],
                ],
            ],
            'data.relationships:id not allowed' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'relationships' => [
                            'id' => [
                                'data' => null,
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member relationships cannot have a id field.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships'],
                ],
            ],
            'data.relationships.*:not object' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'author' => false,
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member author must be an object.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships/author'],
                ],
            ],
            'data.relationships.*:unrecognised' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                        ],
                        'relationships' => [
                            'foo' => [
                                'data' => null,
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => 'The field foo is not a supported relationship.',
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships'],
                ],
            ],
            'data.relationships.*.data:required' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'author' => [
                                'meta' => ['foo' => 'bar'],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member data is required.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships/author'],
                ],
            ],
            'data.relationships.*.data:not object' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'author' => [
                                'data' => false,
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member data must be an object.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships/author/data'],
                ],
            ],
            'data.relationships.*.data.type:required' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'author' => [
                                'data' => [
                                    'id' => '123',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member type is required.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships/author/data'],
                ],
            ],
            'data.relationships.*.data.id:required' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'author' => [
                                'data' => [
                                    'type' => 'users',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member id is required.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships/author/data'],
                ],
            ],
            'data.relationships.*.data:resource does not exist' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'author' => [
                                'data' => [
                                    'type' => 'users',
                                    'id' => '999',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Not Found',
                    'detail' => 'The related resource does not exist.',
                    'status' => '404',
                    'source' => ['pointer' => '/data/relationships/author'],
                ],
            ],
            'data.relationships.*.data:resource not supported' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'author' => [
                                'data' => [
                                    'type' => 'posts',
                                    'id' => '1',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Unprocessable Entity',
                    'detail' => 'The author field must be a to-one relationship containing users resources.',
                    'status' => '422',
                    'source' => ['pointer' => '/data/relationships/author'],
                ],
            ],
            'data.relationships.*: rejected to-many for to-one' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'author' => [
                                'data' => [
                                    [
                                        'type' => 'users',
                                        'id' => '123',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => 'The field author must be a to-one relation.',
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships/author'],
                ],
            ],
            'data.relationships.*.data.*:not array' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'tags' => [
                                'data' => false,
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member data must be an array.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships/tags/data'],
                ],
            ],
            'data.relationships.*.data.*:not object' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'tags' => [
                                'data' => [
                                    [],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member 0 must be an object.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships/tags/data/0'],
                ],
            ],
            'data.relationships.*.data.*.type:required' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'author' => [
                                'data' => null,
                            ],
                            'tags' => [
                                'data' => [
                                    [
                                        'id' => '1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member type is required.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships/tags/data/0'],
                ],
            ],
            'data.relationships.*.data.*.id:required' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'author' => [
                                'data' => null,
                            ],
                            'tags' => [
                                'data' => [
                                    [
                                        'type' => 'tags',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member id is required.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships/tags/data/0'],
                ],
            ],
            'data.relationships.*.data.*:resource does not exist' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'tags' => [
                                'data' => [
                                    [
                                        'type' => 'tags',
                                        'id' => '999',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Not Found',
                    'detail' => 'The related resource does not exist.',
                    'status' => '404',
                    'source' => ['pointer' => '/data/relationships/tags/data/0'],
                ],
            ],
            'data.relationships.*.data.*:resource not supported' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'tags' => [
                                'data' => [
                                    [
                                        'type' => 'posts',
                                        'id' => '1',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Unprocessable Entity',
                    'detail' => 'The tags field must be a to-many relationship containing tags resources.',
                    'status' => '422',
                    'source' => ['pointer' => '/data/relationships/tags/data/0'],
                ],
            ],
            'data.relationships.*: rejected to-one for to-many' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => [
                            'title' => 'Hello World',
                            'content' => '...',
                            'slug' => 'hello-world',
                        ],
                        'relationships' => [
                            'tags' => [
                                'data' => [
                                    'type' => 'tags',
                                    'id' => '123',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => 'The field tags must be a to-many relation.',
                    'status' => '400',
                    'source' => ['pointer' => '/data/relationships/tags'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function updateProvider(): array
    {
        return [
            'data.id:required' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'attributes' => ['title' => 'Hello World'],
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
                        'type' => 'posts',
                        'id' => null,
                        'attributes' => ['title' => 'Hello World'],
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
                        'type' => 'posts',
                        'id' => 1,
                        'attributes' => ['title' => 'Hello World'],
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
                        'type' => 'posts',
                        'id' => '',
                        'attributes' => ['title' => 'Hello World'],
                    ],
                ],
                [
                    'title' => 'Non-Compliant JSON:API Document',
                    'detail' => "The member id cannot be empty.",
                    'status' => '400',
                    'source' => ['pointer' => '/data/id'],
                ],
            ],
            'data.id:not supported' => [
                [
                    'data' => [
                        'type' => 'posts',
                        'id' => '10',
                        'attributes' => ['title' => 'Hello World'],
                    ],
                ],
                [
                    'title' => 'Not Supported',
                    'detail' => "Resource id 10 is not supported by this endpoint.",
                    'status' => '409',
                    'source' => ['pointer' => '/data/id'],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public static function emptyStringProvider(): array
    {
        return [
            [''],
            ['           '],
        ];
    }

    /**
     * @param string $value
     * @dataProvider emptyStringProvider
     */
    public function testEmptyString(string $value): void
    {
        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        try {
            $builder->expects('posts', '1')->build($value);
            $this->fail('No exception thrown.');
        } catch (JsonApiException $ex) {
            $this->assertSame(400, $ex->getStatusCode());
            $this->assertError([
                'detail' => 'Expecting JSON to decode.',
                'status' => '400',
                'title' => 'Invalid JSON',
            ], $ex->toErrors());
        }
    }

    public function testInvalidJson(): void
    {
        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        try {
            $builder->expects('posts', '1')->build(
                '{"data": {}"'
            );

            $this->fail('No exception thrown.');
        } catch (JsonApiException $ex) {
            $previous = $ex->getPrevious();
            $this->assertInstanceOf(\JsonException::class, $previous);
            $this->assertSame(400, $ex->getStatusCode());
            $this->assertError([
                'code' => (string) $previous->getCode(),
                'detail' => $previous->getMessage(),
                'status' => '400',
                'title' => 'Invalid JSON',
            ], $ex->toErrors());
        }
    }

    /**
     * @return array
     */
    public static function nonObjectProvider(): array
    {
        return [
            ['true'],
            ['false'],
            ['""'],
            ['"foo"'],
            ['"1"'],
            ['1'],
            ['0.1'],
            ['[]'],
        ];
    }

    /**
     * @param string $json
     * @throws \JsonException
     * @dataProvider nonObjectProvider
     */
    public function testNonObject(string $json): void
    {
        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        try {
            $builder->expects('posts', '1')->build($json);
            $this->fail('No exception thrown.');
        } catch (JsonApiException $ex) {
            $this->assertSame(400, $ex->getStatusCode());
            $this->assertError([
                'detail' => 'Expecting JSON to decode to an object.',
                'status' => '400',
                'title' => 'Invalid JSON',
            ], $ex->toErrors());
        }
    }

    public function testCustomPipe(): void
    {
        $ex = new LogicException('Boom!');

        $this->expectExceptionObject($ex);

        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        $builder->expects('posts', null)->using(function () use ($ex) {
            throw $ex;
        })->build(json_encode([
            'data' => [
                'type' => 'posts',
                'attributes' => [
                    'title' => 'Hello World',
                ],
            ],
        ]));
    }

    /**
     * @param $json
     * @param array $expected
     * @dataProvider createProvider
     */
    public function testCreate($json, array $expected): void
    {
        ksort($expected);

        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        $document = $builder
            ->expects('posts', null)
            ->build(json_encode($json));

        $this->assertInvalid($document, [$expected]);
    }

    public function testCreateWithClientIdAllowed(): void
    {
        $data = [
            'type' => 'podcasts',
            'id' => '999', // 999 is set up to not-exist.
            'attributes' => [
                'title' => 'My first podcast',
            ],
        ];

        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        $document = $builder
            ->expects('podcasts', null)
            ->build(json_encode(compact('data')));

        $this->assertTrue($document->valid());
    }

    public function testCreateWithClientIdIsZero(): void
    {
        $data = [
            'type' => 'podcasts',
            'id' => '0',
            'attributes' => [
                'title' => 'My first podcast',
            ],
        ];

        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        $document = $builder
            ->expects('podcasts', null)
            ->build(json_encode(compact('data')));

        $this->assertTrue($document->valid());
    }

    public function testCreateWithClientIdAlreadyExists(): void
    {
        $data = [
            'type' => 'podcasts',
            'id' => '123',
            'attributes' => [
                'title' => 'My first podcast',
            ],
        ];

        $expected = [
            'detail' => 'Resource 123 already exists.',
            'source' => ['pointer' => '/data/id'],
            'status' => '409',
            'title' => 'Conflict',
        ];

        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        $document = $builder
            ->expects('podcasts', null)
            ->build(json_encode(compact('data')));

        $this->assertInvalid($document, [$expected]);
    }

    /**
     * @param string $id
     * @return void
     * @dataProvider emptyIdProvider
     */
    public function testCreateWithClientIdIsEmpty(string $id): void
    {
        $data = [
            'type' => 'podcasts',
            'id' => $id,
            'attributes' => [
                'title' => 'My first podcast',
            ],
        ];

        $expected = [
            'detail' => 'The member id cannot be empty.',
            'source' => ['pointer' => '/data/id'],
            'status' => '400',
            'title' => 'Non-Compliant JSON:API Document',
        ];

        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        $document = $builder
            ->expects('podcasts', null)
            ->build(json_encode(compact('data')));

        $this->assertInvalid($document, [$expected]);
    }

    /**
     * @param $json
     * @param array $expected
     * @dataProvider updateProvider
     */
    public function testUpdate($json, array $expected): void
    {
        ksort($expected);

        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        $document = $builder
            ->expects('posts', '1')
            ->build(json_encode($json));

        $this->assertInvalid($document, [$expected]);
    }

    public function testUpdateWithZeroId(): void
    {
        $data = [
            'type' => 'posts',
            'id' => '0',
            'attributes' => [
                'title' => 'Hello World',
            ],
        ];

        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        $document = $builder
            ->expects('posts', '0')
            ->build(json_encode(['data' => $data]));

        $this->assertTrue($document->valid());
    }

    public function testDuplicateFields(): void
    {
        $json = [
            'data' => [
                'type' => 'posts',
                'id' => '1',
                'attributes' => [
                    'author' => null,
                ],
                'relationships' => [
                    'author' => [
                        'data' => null,
                    ],
                ],
            ],
        ];

        $expected = [
            [
                'detail' => 'The author field cannot exist as an attribute and a relationship.',
                'source' => ['pointer' => '/data'],
                'status' => '400',
                'title' => 'Non-Compliant JSON:API Document',
            ],
            [
                'detail' => 'The field author is not a supported attribute.',
                'source' => ['pointer' => '/data/attributes'],
                'status' => '400',
                'title' => 'Non-Compliant JSON:API Document',
            ],
        ];

        /** @var ResourceBuilder $builder */
        $builder = $this->app->make(ResourceBuilder::class);

        $document = $builder
            ->expects('posts', '1')
            ->build(json_encode($json));

        $this->assertInvalid($document, $expected);
    }

}
