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

namespace District5\Mondoc\Db\Service\Traits;

use District5\Date\Date;
use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Model\Traits\MondocCreatedDateTrait;
use District5\Mondoc\Db\Model\Traits\MondocModifiedDateTrait;
use District5\Mondoc\Db\Model\Traits\MondocRevisionNumberTrait;
use District5\Mondoc\Db\Service\Traits\Persistence\InsertMultiTrait;
use District5\Mondoc\Db\Service\Traits\Persistence\InsertSingleTrait;
use District5\Mondoc\Db\Service\Traits\Persistence\UpdateMultiTrait;
use District5\Mondoc\Db\Service\Traits\Persistence\UpdateSingleTrait;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Exception\MondocServiceMapErrorException;
use District5\Mondoc\Helper\HasTraitHelper;

/**
 * Trait PersistenceTrait.
 *
 * @package District5\Mondoc\Db\Service\Traits
 */
trait PersistenceTrait
{
    use InsertSingleTrait;
    use InsertMultiTrait;
    use UpdateSingleTrait;
    use UpdateMultiTrait;

    /**
     * Save a model into the collection.
     *
     * @param MondocAbstractModel $model
     * @param array $insertOrUpdateOptions
     *
     * @return bool
     *
     * @throws MondocConfigConfigurationException
     * @throws MondocServiceMapErrorException
     * @throws MondocException
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-updateOne/
     * @see https://www.mongodb.com/docs/php-library/current/reference/method/MongoDBCollection-insertOne/
     */
    public static function saveModel(MondocAbstractModel $model, array $insertOrUpdateOptions = []): bool
    {
        $hasModified = HasTraitHelper::has($model, MondocModifiedDateTrait::class);
        $hasCreated = HasTraitHelper::has($model, MondocCreatedDateTrait::class);
        $hasRevision = $model->isRevisionNumberModel();
        if (null === $model->getObjectId()) {
            /* @var $model MondocCreatedDateTrait for PhpStorm purposes only */
            if ($hasCreated === true && $model->getCreatedDate() === null) {
                $model->touchCreatedDate();
            }
            /* @var $model MondocModifiedDateTrait for PhpStorm purposes only */
            if ($hasModified === true && $model->getModifiedDate() === null) {
                $model->touchModifiedDate();
            }
            /* @var $model MondocRevisionNumberTrait for PhpStorm purposes only */
            if ($hasRevision === true && $model->getRevisionNumber() === 0) {
                $model->incrementRevisionNumber();
            }
            return self::insert($model, $insertOrUpdateOptions);
        }

        if ($hasModified === true && $model->isDirty('md') === false) {
            /* @var $model MondocModifiedDateTrait for PhpStorm purposes only */
            $model->setModifiedDate(Date::nowUtc());
        }
        if ($hasRevision === true && $model->isDirty('_rn') === false) {
            /* @var $model MondocRevisionNumberTrait for PhpStorm purposes only */
            $model->incrementRevisionNumber();
        }
        return self::update($model, $insertOrUpdateOptions);
    }
}
