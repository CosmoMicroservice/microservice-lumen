<?php


return [
    'prefix' => 'api/graph',

    'domain' => null,

    'routes' => '{graphql_schema?}',

    'controllers' => \Folklore\GraphQL\GraphQLController::class.'@query',

    'variables_input_name' => 'variables',

    'middleware' => [],

    'middleware_schema' => [
        'default' => [],
        'v1' => ['api.jwt'],
    ],

    'headers' => [],

    'json_encoding_options' => 0,

    'schema' => env('API_STABLE_VERSION', 'default'),

    'schemas' => [
	    'default' => [
		    'query' => [],
		    'mutation' => []
	    ],
	    //Version Graph API
	    'v1' => [
		    'query' => [
			    'users' => App\Api\GraphQL\v1\Query\UsersQuery::class,
			    'usersPagination' => App\Api\GraphQL\v1\Query\UsersPaginationQuery::class,
			    'usersWithPost' => App\Api\GraphQL\v1\Query\UsersWithPostQuery::class,

			    'postsPagination' => App\Api\GraphQL\v1\Query\PostsPaginationQuery::class,
			    'posts' => App\Api\GraphQL\v1\Query\PostsQuery::class,

		    ],
		    'mutation' => [
			    'updateUserName' => App\Api\GraphQL\v1\Mutation\UpdateUserNameMutation::class,
		    ]
	    ]
    ],

    'resolvers' => [
        'default' => [
        ],
    ],

    'defaultFieldResolver' => null,

    'error_formatter' => [\Folklore\GraphQL\GraphQL::class, 'formatError'],

    'security' => [
        'query_max_complexity' => null,
        'query_max_depth' => null,
        'disable_introspection' => false
    ]
];
