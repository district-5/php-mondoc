<?php
/**
 * District5 Mondoc Library
 *
 * @author      District5 <hello@district5.co.uk>
 * @copyright   District5 <hello@district5.co.uk>
 * @link        https://www.district5.co.uk
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace District5Tests\MondocTests;

use District5\Mondoc\MondocConfig;
use District5Tests\MondocTests\TestObjects\Model\AllTypesModel;
use District5Tests\MondocTests\TestObjects\Model\DateModel;
use District5Tests\MondocTests\TestObjects\Model\FinancialCandleModel;
use District5Tests\MondocTests\TestObjects\Model\MyModel;
use District5Tests\MondocTests\TestObjects\Model\SingleAndMultiNestedModel;
use District5Tests\MondocTests\TestObjects\Model\Subs\MyModelWithSub;
use District5Tests\MondocTests\TestObjects\Model\VersionedModel;
use District5Tests\MondocTests\TestObjects\Service\AllTypesService;
use District5Tests\MondocTests\TestObjects\Service\DateService;
use District5Tests\MondocTests\TestObjects\Service\FinancialCandleService;
use District5Tests\MondocTests\TestObjects\Service\MyService;
use District5Tests\MondocTests\TestObjects\Service\MySubService;
use District5Tests\MondocTests\TestObjects\Service\SingleAndMultiNestedService;
use District5Tests\MondocTests\TestObjects\Service\VersionedService;
use MondocTests\SingletonDbStore;
use MongoDB\Client;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;

/**
 * Class MondocBaseTest.
 *
 * @package District5Tests\MondocTests
 */
abstract class MondocBaseTest extends TestCase
{
    /**
     * @var null|string
     */
    protected string|null $uniqueKey = null;

    /**
     * @var null|MondocConfig
     */
    protected MondocConfig|null $mondoc = null;

    /**
     * @return null|string
     */
    public function getUniqueKey(): ?string
    {
        if (null === $this->uniqueKey) {
            /** @noinspection PhpRedundantOptionalArgumentInspection */
            $this->uniqueKey = uniqid() . microtime(false);
        }

        return $this->uniqueKey;
    }

    protected function tearDown(): void
    {
    }

    protected function setUp(): void
    {
    }

    /**
     * @return string
     */
    protected function getDatabaseName(): string
    {
        return getenv('MONGO_DATABASE') . php_uname('s');
    }
}
