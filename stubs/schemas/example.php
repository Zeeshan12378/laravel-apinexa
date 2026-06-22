<?php

return [
    'name' => 'Create Job',
    'method' => 'POST',
    'endpoint' => '/jobs',
    'version' => 'v1',
    'description' => 'Create a new job posting.',
    'auth' => true,
    'roles' => ['admin', 'employer'],
    'scopes' => ['jobs:create'],
    'payload' => [
        'title' => 'required|string',
        'salary' => 'nullable|numeric',
    ],
];
