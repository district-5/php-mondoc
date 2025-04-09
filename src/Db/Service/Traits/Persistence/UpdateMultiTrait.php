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

namespace District5\Mondoc\Db\Service\Traits\Persistence;

use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Service\MondocAbstractService;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Helper\FilterFormatter;
use District5\Mondoc\MondocConfig;
use District5\MondocBuilder\QueryBuilder;

/**
 * Trait UpdateMultiTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits\Persistence
 */
trait UpdateMultiTrait
{
    /**
     * Update multiple documents by applying a filter and an update query. Any references to these
     * models, held in the code are not updated.
     *
     * @param array $filter
     * @param array $update
     * @param array $updateOptions
     *
     * @return bool
     *
     * @throws MondocConfigConfigurationException
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-updateOne/
     */
    public static function updateMany(array $filter, array $update, array $updateOptions = []): bool
    {
        $collection = self::getCollection(
            get_called_class()
        );
        $perform = $collection->updateMany(
            FilterFormatter::format($filter),
            FilterFormatter::format($update),
            $updateOptions
        );

        return $perform->isAcknowledged();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array $update
     * @return bool
     * @throws MondocConfigConfigurationException
     */
    public static function updateManyByQueryBuilder(QueryBuilder $queryBuilder, array $update): bool
    {
        $collection = self::getCollection(
            get_called_class()
        );

        $perform = $collection->updateMany(
            FilterFormatter::format($queryBuilder->getArrayCopy()),
            FilterFormatter::format($update),
            $queryBuilder->getOptions()->getArrayCopy()
        );

        return $perform->isAcknowledged();
    }

    /**
     * Updates many models in the collection. This does save each model individually.
     *
     * The return from this method is an array of booleans, each representing the success of the corresponding indexed
     * model in the $models array.
     *
     * @param MondocAbstractModel[] $models
     * @param array $updateOptions
     *
     * @return bool[]
     *
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-updateOne/
     */
    public static function updateManyModels(array $models, array $updateOptions = []): array
    {
        $results = [];
        $establishedServices = [];
        /* @var $establishedServices MondocAbstractService[] */
        foreach ($models as $model) {
            if ($model->hasObjectId() === false) {
                continue;
            }
            if ($model instanceof MondocAbstractModel) {
                $clz = get_class($model);
                if (!array_key_exists($clz, $establishedServices)) {
                    $establishedServices[$clz] = MondocConfig::getInstance()->getServiceForModel(
                        get_class($model)
                    );
                }

                $results[] = $establishedServices[$clz]::update($model, $updateOptions);
            }
        }

        return $results;
    }
}
