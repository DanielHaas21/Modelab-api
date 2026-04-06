<?php

const BASE_CATEGORIES = ['3D Models', '2D Textures', 'Audio'];
const BASE_TAGS = ['Maya', 'FBX', 'OBJ', 'Prop', 'Shrine', 'Textured', 'Music', 'SFX', 'Stylized'];

const CAT_3D_MODELS = 0;
const CAT_2D_TEXTURES = 1;
const CAT_AUDIO = 2;

const TAG_MAYA = 0;
const TAG_FBX = 1;
const TAG_OBJ = 2;
const TAG_PROP = 3;
const TAG_SHRINE = 4;
const TAG_TEXTURED = 5;
const TAG_MUSIC = 6;
const TAG_SFX = 7;
const TAG_STYLIZED = 8;

const LOREM_TEXT = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Fusce tellus. Etiam dui sem,'
    . 'fermentum vitae, sagittis id, malesuada in, quam. Bomboclat sagittis ultrices augue.'
    . 'Nullam justo enim, consectetuer nec, ullamcorper ac, vestibulum in, elit. Suspendisse'
    . 'sagittis ultrices augue. Vivamus porttitor turpis ac leo.';

const DEV_ASSETS_PATH_ROOT = __DIR__;

const DEV_ASSETS = [
    [
        'name' => 'Chram',
        'description' => LOREM_TEXT,
        'category' => CAT_3D_MODELS,
        'tags' => [TAG_MAYA, TAG_FBX, TAG_SHRINE],
        'files' => [
            ['path' => './Chram/Chram.mb', 'isHidden' => true, 'order' => 0, 'isPreview' => false],
            ['path' => './Chram/Chram_export.mb', 'isHidden' => true, 'order' => 0, 'isPreview' => false],
            ['path' => './Chram/chram.fbx', 'isHidden' => false, 'order' => 1, 'isPreview' => false],
            ['path' => './Chram/Chram_All.png', 'isHidden' => false, 'order' => 2, 'isPreview' => false],
            ['path' => './Chram/Chram_Persp.png', 'isHidden' => false, 'order' => 3, 'isPreview' => true],
        ]
    ],
    [
        'name' => 'Baudys',
        'description' => LOREM_TEXT,
        'category' => CAT_3D_MODELS,
        'tags' => [TAG_MAYA, TAG_SHRINE],
        'files' => [
            ['path' => './Baudys/Baudys.mb', 'isHidden' => true, 'order' => 0, 'isPreview' => false],
            ['path' => './Baudys/all.png', 'isHidden' => false, 'order' => 1, 'isPreview' => false],
            ['path' => './Baudys/persp.png', 'isHidden' => false, 'order' => 2, 'isPreview' => true],
        ]
    ],
    [
        'name' => 'Bugaj',
        'description' => LOREM_TEXT,
        'category' => CAT_3D_MODELS,
        'tags' => [TAG_MAYA, TAG_FBX, TAG_SHRINE, TAG_TEXTURED],
        'files' => [
            ['path' => './Bugaj/Bugaj.mb', 'isHidden' => true, 'order' => 0, 'isPreview' => false],
            ['path' => './Bugaj/Bugaj.fbx', 'isHidden' => false, 'order' => 1, 'isPreview' => false],
            ['path' => './Bugaj/all.png', 'isHidden' => false, 'order' => 2, 'isPreview' => false],
            ['path' => './Bugaj/persp.png', 'isHidden' => false, 'order' => 3, 'isPreview' => true],
        ]
    ],
    [
        'name' => 'CP_NabytekHraoTruny',
        'description' => LOREM_TEXT,
        'category' => CAT_3D_MODELS,
        'tags' => [TAG_MAYA, TAG_OBJ, TAG_PROP],
        'files' => [
            ['path' => './CP_NabytekHraoTruny/CP_NabytekHraoTruny.mb', 'isHidden' => true, 'order' => 0, 'isPreview' => false],
            ['path' => './CP_NabytekHraoTruny/CP_NabytekHraoTruny.mtl', 'isHidden' => false, 'order' => 0, 'isPreview' => false],
            ['path' => './CP_NabytekHraoTruny/CP_NabytekHraoTruny.obj', 'isHidden' => false, 'order' => 1, 'isPreview' => false],
        ]
    ],
    [
        'name' => 'Sedmihradsky',
        'description' => LOREM_TEXT,
        'category' => CAT_3D_MODELS,
        'tags' => [TAG_MAYA, TAG_OBJ, TAG_SHRINE],
        'files' => [
            ['path' => './Sedmihradsky/Sedmihradsky.mb', 'isHidden' => true, 'order' => 0, 'isPreview' => false],
            ['path' => './Sedmihradsky/Sedmihradsky.obj', 'isHidden' => false, 'order' => 1, 'isPreview' => false],
            ['path' => './Sedmihradsky/Sedmihradsky.mtl', 'isHidden' => false, 'order' => 0, 'isPreview' => false],
            ['path' => './Sedmihradsky/all.png', 'isHidden' => false, 'order' => 2, 'isPreview' => false],
            ['path' => './Sedmihradsky/persp.png', 'isHidden' => false, 'order' => 3, 'isPreview' => true],
        ]
    ],
    [
        'name' => 'Stastka',
        'description' => LOREM_TEXT,
        'category' => CAT_3D_MODELS,
        'tags' => [TAG_MAYA, TAG_FBX, TAG_SHRINE],
        'files' => [
            ['path' => './Stastka/Stastka.mb', 'isHidden' => true, 'order' => 0, 'isPreview' => false],
            ['path' => './Stastka/Stastka.fbx', 'isHidden' => false, 'order' => 1, 'isPreview' => false],
            ['path' => './Stastka/all.png', 'isHidden' => false, 'order' => 2, 'isPreview' => false],
            ['path' => './Stastka/persp.png', 'isHidden' => false, 'order' => 3, 'isPreview' => true],
        ]
    ],
    [
        'name' => 'Tumova_TajMahal',
        'description' => LOREM_TEXT,
        'category' => CAT_3D_MODELS,
        'tags' => [TAG_MAYA, TAG_FBX, TAG_SHRINE],
        'files' => [
            ['path' => './Tumova_TajMahal/Tumova_TajMahal.mb', 'isHidden' => true, 'order' => 0, 'isPreview' => false],
            ['path' => './Tumova_TajMahal/Tumova_TajMahal.fbx', 'isHidden' => false, 'order' => 1, 'isPreview' => false],
            ['path' => './Tumova_TajMahal/all.png', 'isHidden' => false, 'order' => 2, 'isPreview' => false],
            ['path' => './Tumova_TajMahal/persp.png', 'isHidden' => false, 'order' => 3, 'isPreview' => true],
        ]
    ],
    [
        'name' => 'Audio Test',
        'description' => LOREM_TEXT,
        'category' => CAT_AUDIO,
        'tags' => [TAG_MUSIC],
        'files' => [
            ['path' => './Audio/juhani-junkala.wav', 'isHidden' => false, 'order' => 1, 'isPreview' => false],
            ['path' => './Audio/moon.jpg', 'isHidden' => false, 'order' => 2, 'isPreview' => true],
        ]
    ],
    [
        'name' => 'Texture Test',
        'description' => LOREM_TEXT,
        'category' => CAT_2D_TEXTURES,
        'tags' => [TAG_STYLIZED],
        'files' => [
            ['path' => './Texture/colormap.png', 'isHidden' => false, 'order' => 1, 'isPreview' => false],
        ]
    ],
];
