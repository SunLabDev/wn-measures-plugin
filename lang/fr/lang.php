<?php return [
    'plugin' => [
        'description' => 'Permet de créer de d\'incrémenter toute sorte de mesures sur n\'importe quel modèle.'
    ],
    'listened_events' => [
        'active' => 'Actif',
        'event_type' => 'Type d\'événement',
        'event_name' => 'Nom de l\'événement',
        'model_to_watch' => 'Modèle observé',
        'on_logged_in_user' => 'Incrémenter la mesure de l\'utilisateur connecté',
        'on_logged_in_user_desc' => 'Supporte uniquement Winter.User',
        'measure_name' => 'Nom de la mesure',
        'model_to_update' => 'Modèle à mettre à jour',
        'model_to_update_desc' => 'Le modèle à mettre à jour',
        'route_parameter' => 'Paramètre dans l\'url',
        'route_parameter_desc' => 'Le paramètre pour retrouver le modèle',
        'model_attribute' => 'Attribut du modèle',
        'model_attribute_desc' => 'L\'attribut du modèle qui doit correspondre au paramètre de l\'url',
    ]
];
