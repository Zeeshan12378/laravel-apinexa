<?php

return [
    'name' => 'Create Job',
    'method' => 'POST',
    'endpoint' => '/jobs',
    'version' => 'v1',
    'auth' => true,
    'roles' => ['admin'],
    'scopes' => ['jobs:create'],
    'payload' => [
        'title' => 'required|string',
        'salary' => 'nullable|numeric',
    ],
];
