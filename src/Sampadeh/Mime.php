<?php
namespace Sampadeh;

class Mime
{

    public static $type = [
        'csv' => 'text/csv',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'json' => 'application/json',
        'odp' => 'application/vnd.oasis.opendocument.presentation',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt' => 'application/vnd.oasis.opendocument.text',
        'pdf' => 'application/pdf',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'txt' => 'text/plain',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xml' => 'application/xml',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'bmp' => 'image/bmp',
        'gif' => 'image/gif',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'ico' => 'image/vnd.microsoft.icon',
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'flv' => 'video/x-flv',
        'mp3' => 'audio/mpeg',
        'ogg' => 'audio/ogg',
        'mp4' => 'video/mp4',
        'ogv' => 'video/ogg',
        'swf' => 'application/x-shockwave-flash',
        'webm' => 'video/webm',
        'gz' => 'application/x-gzip',
        'gzip' => 'application/x-gzip',
        'rar' => 'application/rar',
        'tar' => 'application/x-tar',
        'zip' => 'application/zip'
    ];

    public static function allow($mime, array $type = [])
    {
        $type = array_merge(self::$type, $type);

        return in_array($mime, $type) ? true : false;
    }

    public static function get($file)
    {
        $extension = File::extension($file);

        if (array_key_exists($extension, self::$type)) {
            return self::$type[$extension];
        } else {
            if (function_exists('finfo_open') && function_exists('finfo_file') && function_exists('finfo_close')) {
                $info = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($info, $file);
                finfo_close($info);

                return $mime;
            } elseif (function_exists('mime_content_type')) {
                $mime = mime_content_type($file);

                return $mime;
            } else {
                return 'application/octet-stream';
            }
        }
    }
}
