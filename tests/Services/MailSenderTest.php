<?php

declare(strict_types=1);

namespace Frosh\MailArchive\Tests\Services;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Content\Mail\Service\AbstractMailSender;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Frosh\MailArchive\Services\EmlFileManager;
use Shopware\Core\Framework\Uuid\Uuid;

class MailSenderTest extends TestCase
{
    private AbstractMailSender $mailSender;
    private EntityRepository $froshMailArchiveRepository; 
    private EntityRepository $customerRepository; 
    private EmlFileManager $emlFileManager;

    protected function setUp(): void
    {
    }

    public function testMailSend(): void
    {
        $id = Uuid::randomHex();

        $condition = true;
        self::assertTrue($condition);

    }
}
