<?php
declare(strict_types=1);

/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SA (https://www.cipherguard.github.io)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.github.io)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.github.io Cipherguard(tm)
 * @since         3.2.0
 */

namespace App\Test\TestCase\Service\Avatars;

use App\Model\Entity\Avatar;
use App\Service\Avatars\AvatarsCacheService;
use App\Test\Lib\Model\AvatarsIntegrationTestTrait;
use App\Utility\UuidFactory;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Laminas\Diactoros\Stream;

/**
 * @covers \App\Service\Avatars\AvatarsCacheService
 */
class AvatarsCacheServiceTest extends TestCase
{
    use AvatarsIntegrationTestTrait;

    public AvatarsCacheService $avatarsCacheService;

    public ?Table $Avatars = null;

    public function setUp(): void
    {
        parent::setUp();

        $this->Avatars = TableRegistry::getTableLocator()->get('Avatars');
        $this->avatarsCacheService = new AvatarsCacheService($this->filesystemAdapter);
    }

    public function tearDown(): void
    {
        unset($this->Avatars);
        parent::tearDown();
    }

    public function dataForTestAvatarsCacheServiceStore(): array
    {
        return [
            [file_get_contents(FIXTURES . 'Avatar' . DS . 'ada.png')],
            [(new Stream(FIXTURES . 'Avatar' . DS . 'ada.png'))->getContents()],
            [(new Stream(FIXTURES . 'Avatar' . DS . 'ada.png'))],
        ];
    }

    public function dataForTestAvatarsCacheServiceStoreFail(): array
    {
        return [
            [null],
            ['1234'],
            [FIXTURES . 'Avatar' . DS . 'ada.png'],
        ];
    }

    /**
     * @dataProvider dataForTestAvatarsCacheServiceStore
     */
    public function testAvatarsCacheServiceStore12($data)
    {
        $id = UuidFactory::uuid();
        $avatar = new Avatar(compact('id', 'data'));
        $mediumFileName = $this->cachedFileLocation . $id . DS . 'medium.jpg';
        $smallFileName = $this->cachedFileLocation . $id . DS . 'small.jpg';

        $this->avatarsCacheService->storeInCache($avatar);

        $this->assertFileExists($mediumFileName);
        $this->assertFileExists($smallFileName);

        // Perform the action twice to ensure that no overwriting issues occur
        $this->avatarsCacheService->storeInCache($avatar);

        $this->assertFileExists($mediumFileName);
        $this->assertFileExists($smallFileName);

        // Ensure that both files are not executable
        $getRights = function (string $filepath) {
            return substr(decoct(fileperms($filepath)), -4);
        };

        // Cater for Ubuntu / Debian default umask variations
        $this->assertTrue($getRights($mediumFileName) === '0644' || $getRights($mediumFileName) === '0664');
        $this->assertTrue($getRights($smallFileName) === '0644' || $getRights($smallFileName) === '0664');

        $this->assertSame(
            file_get_contents(FIXTURES . 'Avatar' . DS . 'ada.png'),
            file_get_contents($this->cachedFileLocation . $id . DS . 'medium.jpg')
        );
    }

    /**
     * @dataProvider dataForTestAvatarsCacheServiceStoreFail
     */
    public function testAvatarsCacheServiceStoreFail($data)
    {
        $id = UuidFactory::uuid();
        $avatar = new Avatar(compact('id', 'data'));

        $this->avatarsCacheService->storeInCache($avatar);

        $this->assertFileDoesNotExist($this->cachedFileLocation . $id . DS . 'medium.jpg');
        $this->assertFileDoesNotExist($this->cachedFileLocation . $id . DS . 'small.jpg');
    }

    public function testAvatarsCacheServiceStore_Fail_After_File_Deleted()
    {
        $data = file_get_contents(FIXTURES . 'Avatar' . DS . 'ada.png');
        $id = UuidFactory::uuid();
        $avatar = new Avatar(compact('id', 'data'));

        $this->avatarsCacheService->storeInCache($avatar);

        $this->assertFileExists($this->cachedFileLocation . $id . DS . 'medium.jpg');
        $this->assertFileExists($this->cachedFileLocation . $id . DS . 'small.jpg');

        unlink($this->cachedFileLocation . $id . DS . 'medium.jpg');
        unlink($this->cachedFileLocation . $id . DS . 'small.jpg');

        $this->avatarsCacheService->storeInCache($avatar);

        $this->assertFileExists($this->cachedFileLocation . $id . DS . 'medium.jpg');
        $this->assertFileExists($this->cachedFileLocation . $id . DS . 'small.jpg');
    }
}
