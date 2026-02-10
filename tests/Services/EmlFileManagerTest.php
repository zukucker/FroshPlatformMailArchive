<?php

declare(strict_types=1);

namespace Frosh\MailArchive\Tests\Services;

use Frosh\MailArchive\Services\EmlFileManager;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use ZBateson\MailMimeParser\IMessage;

class EmlFileManagerTest extends TestCase
{
    private FilesystemOperator $filesystem;
    private EmlFileManager $service;

    protected function setUp(): void
    {
        $this->filesystem = $this->createMock(FilesystemOperator::class);
        $this->service = new EmlFileManager($this->filesystem);
    }

    public function testWriteFileWritesCompressedContentAndReturnsPath(): void
    {
        $id = 'abcdef123456';
        $content = 'raw eml content';

        $this->filesystem
            ->expects(self::once())
            ->method('write')
            ->with(
                self::stringContains('mails/ab/cd/ef/' . $id . '.eml'),
                self::isType('string')
            );

        $path = $this->service->writeFile($id, $content);

        self::assertStringContainsString($id . '.eml', $path);
        self::assertMatchesRegularExpression('/\.eml\.(gz|zst)$/', $path);
    }

    public function testGetEmlFileAsStringReturnsUncompressedContent(): void
    {
        $originalContent = 'test eml content';
        $compressed = gzcompress($originalContent);

        $this->filesystem
            ->expects(self::once())
            ->method('read')
            ->willReturn($compressed);

        $result = $this->service->getEmlFileAsString('mails/test.eml.gz');

        self::assertSame($originalContent, $result);
    }

    public function testGetEmlFileAsStringReturnsFalseOnFailure(): void
    {
        $this->filesystem
            ->expects(self::once())
            ->method('read')
            ->willThrowException(new \RuntimeException());

        $result = $this->service->getEmlFileAsString('invalid-path');

        self::assertFalse($result);
    }

    public function testGetEmlAsMessageReturnsParsedMessage(): void
    {
        $eml = <<<EML
From: test@example.com
To: you@example.com
Subject: Hello

Body text
EML;

        $this->filesystem
            ->expects(self::once())
            ->method('read')
            ->willReturn(gzcompress($eml));

        $message = $this->service->getEmlAsMessage('mail.eml.gz');

        self::assertInstanceOf(IMessage::class, $message);
        self::assertSame('Hello', $message->getHeaderValue('Subject'));
    }

    public function testDeleteEmlFileDeletesExistingFile(): void
    {
        $path = 'mails/test.eml.gz';

        $this->filesystem
            ->expects(self::once())
            ->method('fileExists')
            ->with($path)
            ->willReturn(true);

        $this->filesystem
            ->expects(self::once())
            ->method('delete')
            ->with($path);

        $this->service->deleteEmlFile($path);
    }

    public function testDeleteEmlFileDoesNothingIfFileDoesNotExist(): void
    {
        $this->filesystem
            ->expects(self::once())
            ->method('fileExists')
            ->willReturn(false);

        $this->filesystem
            ->expects(self::never())
            ->method('delete');

        $this->service->deleteEmlFile('missing.eml');
    }
}

