<?php

declare(strict_types=1);

namespace Api\Presentation\Api\GraphQL\Types;

use Api\Application\Example\Models\ExampleListDto;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Facades\GraphQL;
use Rebing\GraphQL\Support\Type as GraphQLType;

final class ExampleListType extends GraphQLType
{
    protected $attributes = [
        'name' => 'ExampleList',
        'description' => 'A type',
        'model' => ExampleListDto::class
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
                'description' => 'The id of the user',
                'alias' => 'id',
            ],
            'examples' => [
                'type' => Type::nonNull(Type::listOf(Type::nonNull(GraphQL::type('example')))),
                'description' => 'The id of the user',
            ],
        ];
    }
}
