<?php
namespace FileManagement;

/**
 * Interface FileInterface
 *
 * @package FileManagement
 * @author Jamie Ynonan <jamiea31@gmail.com>
 * @version 1.0.0
 */
interface FileInterface
{
    /**
     * @param string $mime
     * @return boolean
     */
    public function validateMime($mime);

    /**
     * @param string $path
     * @return void
     */
    public function setPath($path);

    /**
     * @param null $name
     * @return bool
     */
    public function save($name = null);

    /**
     * @param $newPath
     * @param string|null $newOnlyName
     * @return File new instance of File with the new file
     */
    public function copy($newPath, $newOnlyName = null);

    /**
     * @param $newPath
     * @return bool
     */
    public function move($newPath);
}