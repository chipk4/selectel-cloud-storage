<?php

return [
    /*
     * This is agreement number from system
     */
    'authUser' => env('SELECTEL_LOGIN'),

    /*
     * Password for cloud storage service.
     * Note: it's different with account password
     */
    'authKey' => env('SELECTEL_PASSWORD'),

    /*
     * Default API url
     */
    'apiUrl' => 'https://auth.selcdn.ru/',

    /*
     * Default value for request timeout
     */
    'timeout' => 10
];