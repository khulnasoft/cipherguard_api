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
 * @since         2.0.0
 */

namespace App\Controller\Comments;

use App\Controller\AppController;
use App\Service\Comments\CommentsDeleteService;

/**
 * @property \App\Model\Table\CommentsTable $Comments
 */
class CommentsDeleteController extends AppController
{
    /**
     * Delete a comment.
     *
     * @param string $id The identifier of comment to delete.
     * @throws \Cake\Http\Exception\BadRequestException
     * @throws \Cake\Http\Exception\NotFoundException
     * @return void
     */
    public function delete(string $id)
    {
        $this->assertJson();

        (new CommentsDeleteService())->delete($id, $this->User->id());
        $this->success(__('The comment was deleted.'));
    }
}
