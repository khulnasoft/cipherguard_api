/**
 * Cipherguard ~ Open source password manager for teams
 * Copyright (c) Cipherguard SARL (https://www.cipherguard.github.io)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cipherguard SA (https://www.cipherguard.github.io)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.cipherguard.github.io Cipherguard(tm)
 * @since         3.3.2
 */

(function () {
  const selfTag = document.querySelector('#stylesheet-manager');
  if (!selfTag) {
    return;
  }

  const baseUrl = document.querySelector("base").getAttribute("href").replace(/\/$/, '');
  const cssFile = selfTag.dataset.file;
  const version = selfTag.getAttribute("cache-version");

  const theme = selfTag.dataset.theme
    ? selfTag.dataset.theme
    : window.matchMedia('(prefers-color-scheme: dark)').matches
      ? "midgar"
      : "default";

  const link = document.createElement('link');
  link.setAttribute("href", `${baseUrl}/css/themes/${theme}/${cssFile}?v=${version}`);
  link.setAttribute("media", "all");
  link.setAttribute("rel", "stylesheet");
  document.querySelector("head").appendChild(link);
})();
