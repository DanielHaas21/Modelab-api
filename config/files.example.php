<?php

const FILES_CONFIG = [
    'dataPath' => __DIR__ . '/../data',
    'maxSizeBytes' => 100 * 1000000, // 100 MB
    'supportedTypes' => [
        'model' => [
            'application/x-tgif', // OBJ
            'model/mtl', // MTL
            'model/gltf-binary', // GLB
            'application/octet-stream', // FBX
            'model/gltf+json', // GLTF (JSON)
            'model/stl', // STL
        ],
        'audio' => [
            'audio/ogg', // OGG
            'audio/mpeg', // MP3
            'audio/wav', // WAV
            'audio/flac', // FLAC
        ],
        'image' => [
            'image/png', // PNG
            'image/jpeg', // JPG, JPEG
            'image/gif', // GIF
            'image/svg+xml', // SVG
            'image/webp', // WEBP
            'image/tiff', // TIFF
            'image/bmp', // BMP
        ],
        'other' => [
            'application/zip', // ZIP
            'application/x-rar-compressed', // RAR
            'application/x-7z-compressed', // 7Z
            'application/gzip', // GZ
            'application/x-tar', // TAR
        ],
    ],
];