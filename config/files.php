<?php

// TODO: Find a way to use https://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types instead

const FILES_CONFIG = [
    'supported_extensions' => [
         'model' => [
            'obj', 'mtl',
            'glb',
            'fbx',
            'stl',
        ],
        'audio' => [
            'ogg',
            'mpeg',
            'wav',
            'flac',
        ],
        'image' => [
            'png',
            'jpeg', 'jpg',
            'gif',
            'svg',
            'webp',
            'bmp',
        ],
        'other' => [
            'zip',
            'mb',
            'blend'
        ],
    ],
];
