<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Security\Filesystem;

use FM\ElfinderBundle\Configuration\ElFinderConfigurationReader;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Filesystem\FilepathComparator;
use Shopsys\FrameworkBundle\Model\Security\Filesystem\Exception\InstanceNotInjectedException;
use Shopsys\FrameworkBundle\Model\Security\Filesystem\FilemanagerAccess;

class FilemanagerAccessTest extends TestCase
{
    public static function isPathAccessibleProvider()
    {
        return [
            [
                __DIR__,
                __DIR__,
                'read',
                null,
            ],
            [
                __DIR__,
                __DIR__ . '/foo',
                'read',
                null,
            ],
            [
                __DIR__,
                __DIR__ . 'foo',
                'read',
                false,
            ],
            [
                __DIR__,
                __DIR__ . '/.foo',
                'read',
                false,
            ],
            [
                __DIR__ . '/sandbox',
                __DIR__ . '/sandboxSecreet/dummyFile',
                'read',
                false,
            ],
            [
                __DIR__ . '/sandbox',
                __DIR__ . '/sandbox/subdirectory/dummyFile',
                'read',
                null,
            ],
            [
                __DIR__ . '/sandbox',
                __DIR__ . '/sandbox/dummyFile',
                'read',
                null,
            ],
        ];
    }

    /**
     * @param mixed $fileuploadDir
     * @param mixed $testPath
     * @param mixed $attr
     * @param mixed $isAccessible
     */
    #[DataProvider('isPathAccessibleProvider')]
    public function testIsPathAccessible($fileuploadDir, $testPath, $attr, $isAccessible)
    {
        $elFinderConfigurationReaderMock = $this->getMockBuilder(ElFinderConfigurationReader::class)
            ->onlyMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $filemanagerAccess = new FilemanagerAccess(
            $fileuploadDir,
            $elFinderConfigurationReaderMock,
            new FilepathComparator(),
        );

        $this->assertSame($filemanagerAccess->isPathAccessible($attr, $testPath, null, null), $isAccessible);
    }

    /**
     * @param mixed $fileuploadDir
     * @param mixed $testPath
     * @param mixed $attr
     * @param mixed $isAccessible
     */
    #[DataProvider('isPathAccessibleProvider')]
    public function testIsPathAccessibleStatic($fileuploadDir, $testPath, $attr, $isAccessible)
    {
        $elFinderConfigurationReaderMock = $this->getMockBuilder(ElFinderConfigurationReader::class)
            ->onlyMethods([])
            ->disableOriginalConstructor()
            ->getMock();
        $filemanagerAccess = new FilemanagerAccess(
            $fileuploadDir,
            $elFinderConfigurationReaderMock,
            new FilepathComparator(),
        );
        FilemanagerAccess::injectSelf($filemanagerAccess);

        $this->assertSame(FilemanagerAccess::isPathAccessibleStatic($attr, $testPath, null, null), $isAccessible);
    }

    public function testIsPathAccessibleStaticException()
    {
        FilemanagerAccess::detachSelf();
        $this->expectException(InstanceNotInjectedException::class);
        FilemanagerAccess::isPathAccessibleStatic('read', __DIR__, null, null);
    }
}
