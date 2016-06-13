<?php
namespace FileManagement;

/**
 * Class File
 *
 * this class can import files from a url or move, copy and rename an existing file on the server
 *
 * @package FileManagement
 * @author Jamie Ynonan <jamiea31@gmail.com>
 * @version 1.0.0
 */
class File implements FileInterface
{
    /**
     * @var \SplFileInfo
     */
    protected $file;

    /**
     * @var string
     */
    protected $mime;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $fullPath;

    /**
     * @var bool
     */
    private $isValid;

    /**
     * File constructor.
     * @param string $file
     */
    public function __construct($file)
    {
        $this->init($file);
        $this->setMime();
    }

    /**
     * Set $this->file with new instance of \SplFileInfo
     * @param string $file
     */
    private function init($file)
    {
        $this->file = new \SplFileInfo($file);
    }

    /**
     * @return \SplFileInfo
     */
    public function getOriginalFileInfo()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getMime()
    {
        return $this->mime;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFullPath()
    {
        return $this->fullPath;
    }

    /**
     * validates if the file mime type is correct
     *
     * @param string $mime
     * @return bool
     */
    public function validateMime($mime)
    {
        $this->isValid = ($mime === $this->mime);
        return $this->isValid;
    }

    /**
     * set the path to save the file
     *
     * @param $path
     * @throws \InvalidArgumentException if path can not be empty
     * @throws \LogicException if can not create the dir
     * @return void
     */
    public function setPath($path)
    {
        if (!is_string($path) || empty($path)) {
            throw new \InvalidArgumentException('path invalid');
        }

        if (realpath($path) === false && $this->createDir($path) === false) {
            $this->path = null;
            throw new \LogicException('can not create the folder');
        }
        $this->path = $path;
    }

    /**
     * @param null|string $name
     * @throws  \UnexpectedValueException if mime isn't valid
     * @throws  \UnexpectedValueException if path isn't set path
     * @throws  \RuntimeException if the file cannot be opened (e.g. insufficient access rights).
     * @return bool
     */
    public function save($name = null)
    {
        if ($this->isValid === false) {
            throw new \UnexpectedValueException('mime file is not valid');
        }

        if (empty($this->path)) {
            throw new \UnexpectedValueException('path can not be empty');
        }

        if ($this->file->isFile() === true) {
            return $this->copy($this->path, $name);
        }

        $this->setName($name);
        return ($this->file->isFile() === true)
            ? file_put_contents(
                $this->fullPath,
                $this->file->openFile()->fread($this->file->getSize())
            ) : file_put_contents(
                $this->fullPath,
                file_get_contents($this->file->getPathname())
            );
    }

    /**
     * copy in another folder
     *
     * Only allowed for internal files (on the server)
     *
     * @param string $newPath
     * @param string|null $newOnlyName
     * @throws \UnexpectedValueException if the file is link
     * @return File new instance of File with the new file
     * @return bool false if can not be copy
     */
    public function copy($newPath, $newOnlyName = null)
    {
        if ($this->file->isFile() === false) {
            throw new \UnexpectedValueException('the file can not be link');
        }
        $this->setPath($newPath);
        $this->setName($newOnlyName);
        $copy = copy($this->file->getPathname(), $this->fullPath);
        // set path of original file
        $this->setPath($this->file->getPath());
        if ($copy === true) {
            $copyFile = $this->fullPath;
            // set name (and fullPath) of original file
            $this->setName();
            return new static($copyFile);
        }
        return false;
    }

    /**
     * Only allowed for internal files (on the server)
     *
     * @param string $newPath
     * @throws \UnexpectedValueException if the file is link
     * @return bool
     */
    public function move($newPath)
    {
        if ($this->file->isFile() === false) {
            throw new \UnexpectedValueException('the file can not be link');
        }
        $this->setPath($newPath);
        $this->setName();

        if (rename($this->file->getPathname(), $this->fullPath) === true) {
            $this->init($this->fullPath);
            return true;
        }
        return false;
    }

    /**
     * Only allowed for internal files (on the server)
     *
     * @param string $newOnlyName
     * @throws \UnexpectedValueException if the file is link
     * @return bool
     */
    public function rename($newOnlyName)
    {
        if ($this->file->isFile() === false) {
            throw new \UnexpectedValueException('the file can not be link');
        }
        $this->setPath($this->file->getPath());
        $this->setName($newOnlyName);
        return rename($this->file->getRealPath(), $this->fullPath);
    }

    /**
     * @return void
     */
    private function setMime()
    {
        $this->mime = ($this->file->isFile() === true)
            ? (new \finfo(FILEINFO_MIME_TYPE))->buffer(
                $this->file->openFile()->fread($this->file->getSize())
            ) : (new \finfo(FILEINFO_MIME_TYPE))->buffer(
                file_get_contents($this->file->getPathname())
            );
    }

    /**
     * @param string $path
     * @return bool
     */
    private function createDir($path)
    {
        return (is_dir($path)) ? true : mkdir($path);
    }

    /**
     * @param null|string $onlyName
     * @return void
     */
    private function setName($onlyName = null)
    {
        $this->name = (!empty($onlyName) && is_string($onlyName))
            ? $onlyName
            : $this->file->getBasename('.' . $this->file->getExtension());
        $this->name .= '.' .$this->file->getExtension();
        $this->setFullPath();
    }

    /**
     * @return void
     */
    private function setFullPath()
    {
        $this->fullPath = $this->path . DIRECTORY_SEPARATOR . $this->name;
    }
}