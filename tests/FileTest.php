<?php

class FileTest extends PHPUnit_Framework_TestCase
{
    private $fileLocal;
    private $fileUrl;

    public function setUp() {
        $this->fileLocal = new \FileManagement\File(__DIR__ . '/files/logo.php.jpg');
        $this->fileUrl = new \FileManagement\File('https://www.google.com/work/images/logo/google-for-work-social-icon.png');
    }

    public function tearDown() {
        $this->fileLocal = null;
        $this->fileUrl = null;
    }

    public static function tearDownAfterClass()
    {
        array_map('unlink', glob(__DIR__ .'/files/move/*'));
        array_map('unlink', glob(__DIR__ .'/files/new-files/*'));
        rmdir(__DIR__ .'/files/move');
        rmdir(__DIR__ .'/files/new-files');
    }

    public function testGetMimeLocal()
    {
        $this->assertEquals('image/svg+xml', $this->fileLocal->getMime());
        return $this->fileLocal->getMime();
    }

    public function testGetMimeUrl()
    {
        $this->assertEquals('image/png', $this->fileUrl->getMime());
        return $this->fileUrl->getMime();
    }

    /**
     * @depends testGetMimeLocal
     */
    public function testValidateMimeOkLocal($mime)
    {
        $this->assertTrue($this->fileLocal->validateMime($mime));
        return $this->fileLocal->getMime();
    }

    public function testValidateMimeFailLocal()
    {
        $this->assertFalse($this->fileLocal->validateMime('invalid/mime'));
    }

    /**
     * @depends testGetMimeUrl
     */
    public function testValidateMimeOkUrl($mime)
    {
        $this->assertTrue($this->fileUrl->validateMime($mime));
        return $this->fileUrl->getMime();
    }

    public function testValidateMimeFailUrl()
    {
        $this->assertFalse($this->fileUrl->validateMime('invalid/mime'));
    }

    /**
     * @dataProvider setPathExceptionEmptyPathProvider
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage path invalid
     */
    public function testSetPathExceptionEmptyPathLocal($path)
    {
        $this->fileLocal->setPath($path);
    }

    /**
     * @dataProvider setPathExceptionEmptyPathProvider
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage path invalid
     */
    public function testSetPathExceptionEmptyPathUrl($path)
    {
        $this->fileUrl->setPath($path);
    }

    public function setPathExceptionEmptyPathProvider()
    {
        return [[false], [true], [''], [0]];
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testSetPathErrorNotCreateDirLocal()
    {
        $this->fileLocal->setPath(__DIR__ . '/not-permission/new-path');
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Warning
     */
    public function testSetPathErrorNotCreateDirUrl()
    {
        $this->fileUrl->setPath(__DIR__ . '/not-permission/new-path');
    }

    public function testSetPathValidLocal()
    {
        $this->assertNull($this->fileLocal->setPath(__DIR__ . '/files/new-files'));
        return $this->fileLocal->getPath();
    }

    public function testSetPathValidUrl()
    {
        $this->assertNull($this->fileUrl->setPath(__DIR__ . '/files/new-files'));
        return $this->fileUrl->getPath();
    }

    /**
     * @depends testSetPathValidLocal
     */
    public function testSaveWithNameLocal($path)
    {
        $this->fileLocal->setPath($path);
        $this->fileLocal->save('new-file-local');
        $this->assertFileExists($this->fileLocal->getFullPath());
    }

    /**
     * @depends testSetPathValidLocal
     */
    public function testSaveWithOutNameLocal($path)
    {
        $this->fileLocal->setPath($path);
        $this->fileLocal->save();
        $this->assertFileExists($this->fileLocal->getFullPath());
    }

    /**
     * @depends testSetPathValidUrl
     */
    public function testSaveWithNameUrl($path)
    {
        $this->fileUrl->setPath($path);
        $this->fileUrl->save('new-file-url');
        $this->assertFileExists($this->fileUrl->getFullPath());
    }

    /**
     * @depends testSetPathValidUrl
     */
    public function testSaveWithOutNameUrl($path)
    {
        $this->fileUrl->setPath($path);
        $this->fileUrl->save();
        $this->assertFileExists($this->fileUrl->getFullPath());
    }

    /**
     * @depends testSetPathValidLocal
     */
    public function testCopyLocal($path)
    {
        $this->fileLocal->setPath($path);
        $newFile = $this->fileLocal->copy($this->fileLocal->getPath(), 'file-local-copy');
        $this->assertInstanceOf('\FileManagement\File', $newFile);
        $this->assertFileExists($newFile->getOriginalFileInfo()->getPathname());

        return $newFile;
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage the file can not be link
     */
    public function testCopyUrl()
    {
        $this->fileUrl->copy(__DIR__ . '/files/new-files', 'file-url-copy');
    }

    /**
     * @depends testCopyLocal
     */
    public function testMoveOnOtherDirFileCopy($newFile)
    {
        $newFile->move(__DIR__ . '/files/move');
        $this->assertFileExists($newFile->getFullPath());
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage the file can not be link
     */
    public function testMoveUrl()
    {
        $this->fileUrl->move(__DIR__ . '/files/new-files', 'file-url-copy');
    }

    /**
     * @depends testCopyLocal
     */
    public function testRenameFileCopy($newFile)
    {
        $newFile->rename(false);
        $this->assertFileExists($newFile->getFullPath());
    }
}
