<?php

use Silex\Application;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

require_once __DIR__.'/../vendor/autoload.php';

// PHP Web server support
$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

$app_info = [
    'name' => 'Archiwum Tymon Radzika',
    'lead' => 'Zamieszczam tutaj informacje z mojej działności w różnych sprawach',
    'about' => 'Jestem Tymon Radzik. Interesuje się życiem i władzą publiczną. Lubie koty i uważam, że koty '
        + 'to kluczowe istoty dla rozwoju świata ładu i porządku. Kultura egispka wygrała z rzymską tylko '
        + 'dzięki kotom.'
];

$app_config = [
    'public_dir' => __DIR__.'/public'
];

// Initalize app
$app = new Silex\Application();
$app['debug'] = true;

// Load modules
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->register(new Neutron\Silex\Provider\FilesystemServiceProvider());
$app->register(new SilexFinder\FinderServiceProvider());

// Add globla variable and usefull functions
$app->share($app->extend('twig', function ($twig, $app) use ($app_info) {
    $twig->addGlobal('app_info', $app_info);
    $twig->addFilter(new Twig_SimpleFilter('ext_to_class', function ($ext) {
        switch ($ext) {
            case 'zip':
            case 'rar':
            case '7z':
                return 'fa fa-file-archive-o';
            case 'mp3':
            case 'ogg':
                return 'fa fa-file-audio-o';
            case 'cpp':
            case 'php':
            case 'py':
            case 'java':
            case 'css':
                return 'fa fa-file-code-o';
            case 'xls':
            case 'csv':
                return 'fa fa-file-excel-o';
            case 'jpg':
            case 'png':
            case 'gif':
            case 'tiff':
            case 'svg':
                return 'fa fa-file-image-o';
            case 'pdf':
                return 'fa fa-file-pdf-o';
            case 'txt':
                return 'fa fa-file-text-o';
            case 'mp4':
            case 'avi':
                return 'fa fa-file-video-o';
            case 'doc':
            case 'docx':
            case 'odt':
                return 'fa fa-file-word-o';
        }
        return 'fa fa-file-o';
    }));
    return $twig;
}));


// =========
// Routng 
// ========== 

// Directory listing
$app->get('/{directory}', function (Application $app, $directory) use ($app_config) {
    $base_path = $app_config['public_dir'];
    $path = $base_path . '/' .$directory;

    if (!$app['filesystem']->isAbsolutePath($path)) {
        throw new NotFoundHttpException();
    }
    
    $directories = $app['finder']->depth('== 0')->directories()->in($path);
    $directories = iterator_to_array($directories);
    $directories = array_map(function ($directory) use ($base_path, $app) {
        $dir_path = $app['filesystem']->makePathRelative($directory->getPath(), $base_path);
        $dir_name = $directory->getFilename();

        return [
            'name' => $dir_name,
            'path' => $dir_path,
            'pathname' => $dir_path . '/' . $dir_name,
        ];
    }, $directories);

    $files = $app['finder']->depth('== 0')->notName('*.json')->files()->in($path);
    $files = iterator_to_array($files);

    $files = array_map(function ($file) use ($app, $base_path) {
        $dir_path = $app['filesystem']->makePathRelative($file->getPath(), $base_path);

        $json_files = $app['finder']->in($file->getPath())->files()->name($file->getFilename() . '.json');
        $json_files = iterator_to_array($json_files);
        $result = [
            'title' => $file->getFilename(),
            'dir_path' => $dir_path,
            'path' => $dir_path . '/'.$file->getFilename(),
            'description' => '',
            'ext' => $file->getExtension(),
            'creation_time' => $file->getCTime(),
        ];

        if (count($json_files) > 0) {
            $json = (array) json_decode(reset($json_files)->getContents());
            $old_result = $result;
            $result = $json + $result;
        }
        return $result;
    }, $files);

    return $app['twig']->render('listing.twig', array(
        'directory' => $directory,
        'path' => $path,
        'directories' => $directories,
        'files' => $files,
    ));
})->assert('directory', '[a-zA-Z0-9\/]+');

// Index
$app->get('/', function (Application $app) {
    return $app['twig']->render('index.twig', array(
        'name' => "Index",
    ));
});

// Start app
$app->run();
