<?php


namespace Pressmind\Image;


use Exception;
use Pressmind\Log\Writer;
use Pressmind\Registry;
use Pressmind\Storage\Bucket;
use Pressmind\Storage\File;

class Downloader
{
    /**
     * @var string
     */
    private $_file_type;

    /**
     * Downloader constructor.
     * @param string $file_type
     */
    public function __construct($file_type = 'image')
    {
        $this->_file_type = $file_type;
    }

    /**
     * @param string $url
     * @param string $targetName
     * @param boolean $forceOverwrite
     * @return File
     * @throws Exception
     */
    public function download($url, $targetName, $forceOverwrite = false)
    {
        $config = Registry::getInstance()->get('config');
        $bucket = new Bucket($config['image_handling']['storage']['bucket']);
        $file = new File($bucket);
        $file->name = $targetName;
        if(!$file->exists() || $forceOverwrite === true) {
            Writer::write('File ' . $file->name . ' does not exists. Downloading ...', WRITER::OUTPUT_FILE, 'image_processor', WRITER::TYPE_INFO);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            $raw = curl_exec($ch);
            curl_close($ch);
            if ($raw === false) {
                throw new Exception('CURL Timeout or other error: ' . curl_error($ch));
            } else if(empty($raw)) {
                throw new Exception('Empty response for: ' . $url);
            } else {
                $file->content = $raw;
            }
        } else {
            Writer::write('File ' . $file->name . ' allready exists. Skipping ...', WRITER::OUTPUT_FILE, 'image_processor', WRITER::TYPE_INFO);
            $file->read();
        }
        return $file;
    }
}
