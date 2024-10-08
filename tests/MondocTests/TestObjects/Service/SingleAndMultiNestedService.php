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

namespace District5Tests\MondocTests\TestObjects\Service;

use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Helper\MondocPaginationHelper;
use District5\MondocBuilder\QueryBuilder;
use District5Tests\MondocTests\TestObjects\Model\SingleAndMultiNestedModel;
use MongoDB\BSON\ObjectId;

/**
 * Class SingleAndMultiNestedService.
 *
 * @package District5Tests\MondocTests\TestObjects\Service
 * @method static SingleAndMultiNestedModel[] getMultiByCriteria(array $filter = [], array $options = [])
 * @method static SingleAndMultiNestedModel[] getMultiByQueryBuilder(QueryBuilder $builder)
 * @method static SingleAndMultiNestedModel[] getByIds(array $ids)
 * @method static SingleAndMultiNestedModel[] getMultiWhereKeyEqualsValue(string $key, $value)
 * @method static SingleAndMultiNestedModel[] getMultiWhereKeyDoesNotEqualValue(string $key, $value)
 * @method static SingleAndMultiNestedModel|null getOneByCriteria(array $filter = [], array $options = [])
 * @method static SingleAndMultiNestedModel|null getById(ObjectId|string $id)
 * @method static SingleAndMultiNestedModel|null getOneByQueryBuilder(QueryBuilder $builder)
 * @method static SingleAndMultiNestedModel|null getOneWhereKeyEqualsValue(string $key, mixed $value)
 * @method static SingleAndMultiNestedModel|null getOneWhereKeyDoesNotEqualValue(string $key, mixed $value)
 * @method static SingleAndMultiNestedModel[] getPage(MondocPaginationHelper $paginator, ?string $sortByField = '_id', int $sortDirection = -1): array
 * @method static SingleAndMultiNestedModel[] getPageByByObjectIdPagination(MondocPaginationHelper $paginator, ObjectId|string|null $currentId, int $sortDirection = -1, array $filter = [])
 */
class SingleAndMultiNestedService extends AbstractTestService
{
    /**
     * @return string
     */
    protected static function getCollectionName(): string
    {
        return parent::getCollectionName(); // Just an example, not needed here, but in reality, you'd just return 'my_collection_name'
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    public static function pullFriendById(ObjectId $id, string $name): void
    {
        self::pullFromArrayById($id, 'friends', $name);
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    public static function pushFriendById(ObjectId $id, string $name): void
    {
        self::pushIntoArrayById($id, 'friends', $name);
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    public static function pullFriendByFilter(array $filter, string $name): void
    {
        self::pullFromArrayWithFilter($filter, 'friends', $name);
    }

    /**
     * @throws MondocConfigConfigurationException
     */
    public static function pushFriendByFilter(array $filter, string $name): void
    {
        self::pushIntoArrayWithFilter($filter, 'friends', $name);
    }
}
