<?php


/**
  * undocumented class
  *
  * @package default
  * @author
  **/
class ExtToClassFilter extends Twig_SimpleFilter
{
    public function __construct()
    {
        parent::__construct('ext_to_class', [ $this, 'extToClass']);
    }

    private function extToClass($ext)
    {
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
    }
}
