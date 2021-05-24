<?php return [
    'plugin' => [
        'description' => 'Allows to create and increment any measures over any model you want.'
    ],
    'listened_events' => [
        'active' => 'Active',
        'event_type' => 'Event type',
        'event_name' => 'Event name',
        'model_to_watch' => 'Model to watch',
        'on_logged_in_user' => 'Increment measure on logged in user',
        'on_logged_in_user_desc' => 'Only supports Winter.User',
        'measure_name' => 'Measure name',
        'model_to_update' => 'Model to update',
        'model_to_update_desc' => 'The model which the measure will be incremented',
        'route_parameter' => 'Route parameter',
        'route_parameter_desc' => 'The route parameter to retrieve the model',
        'model_attribute' => 'Model attribute',
        'model_attribute_desc' => 'The model attribute to match against route parameter',
    ]
];
