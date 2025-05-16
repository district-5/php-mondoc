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

namespace District5\Mondoc\Extensions\Retention;

use DateTime;
use District5\Date\Date;
use District5\Mondoc\Db\Model\MondocAbstractModel;
use District5\Mondoc\Db\Model\Traits\MondocCreatedDateTrait;
use District5\Mondoc\Db\Model\Traits\MondocRetentionTrait;
use District5\Mondoc\Exception\MondocConfigConfigurationException;
use District5\Mondoc\Exception\MondocException;
use District5\Mondoc\Helper\MondocTypes;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONDocument;

/**
 * Class MondocRetentionModel.
 *
 * @package District5\Mondoc\Extensions\Retention
 */
class MondocRetentionModel extends MondocAbstractModel
{
    use MondocCreatedDateTrait;

    /**
     * The array of the retained model data
     *
     * @var array
     */
    protected array $doc = [];

    /**
     * The retained model data ObjectID
     *
     * @var ObjectId|null
     */
    protected ObjectId|null $id = null;

    /**
     * The class name of the retained model, for example: \My\Namespace\Model\MyModel
     *
     * @var string|null
     */
    protected string|null $class = null;

    /**
     * The expiry date of the retained model data
     *
     * @var DateTime|UTCDateTime|null
     */
    protected DateTime|UTCDateTime|null $expiry = null;

    /**
     * @var array
     */
    protected array $retention = [];

    /**
     * @var MondocAbstractModel|null
     */
    private MondocAbstractModel|null $inflatedModel = null;

    /**
     * @return MondocAbstractModel
     * @throws MondocConfigConfigurationException
     * @throws MondocException
     */
    public function toOriginalModel(): MondocAbstractModel
    {
        if ($this->inflatedModel !== null) {
            return $this->inflatedModel;
        }

        $mName = $this->getSourceClassName();
        /* @var $mName MondocAbstractModel (for suppression of IDE warnings) */
        $m = $mName::inflateSingleArray($this->getSourceModelData());
        /* @var $m MondocRetentionTrait (for suppression of IDE warnings) */
        $m->setMondocRetentionChangeMeta($this->getRetentionData());
        /* @var $mName MondocAbstractModel (for suppression of IDE warnings) */

        $this->inflatedModel = $m;

        return $this->inflatedModel;
    }

    /**
     * @return array
     */
    public function getSourceModelData(): array
    {
        return MondocTypes::arrayToPhp(
            $this->doc,
        );
    }

    /**
     * @param array|BSONDocument $data
     * @return $this
     */
    public function setSourceModelData(array|BSONDocument $data): static
    {
        $this->doc = MondocTypes::arrayToPhp($data);

        return $this;
    }

    /**
     * @return ObjectId
     */
    public function getSourceObjectId(): ObjectId
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSourceObjectIdString(): string
    {
        return $this->getSourceObjectId()->__toString();
    }

    /**
     * @param ObjectId $id
     * @return $this
     */
    public function setSourceObjectId(ObjectId $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getSourceClassName(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setSourceClassName(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return array
     */
    public function getRetentionData(): array
    {
        return MondocTypes::arrayToPhp(
            $this->retention
        );
    }

    /**
     * @param array $retention
     * @return $this
     */
    public function setRetentionData(array $retention): static
    {
        $this->retention = MondocTypes::arrayToPhp($retention);

        return $this;
    }

    /**
     * Get the expiry date of this retained data model
     *
     * @param bool $asMongo
     * @return DateTime|UTCDateTime|null
     */
    public function getRetentionExpiry(bool $asMongo = false): DateTime|UTCDateTime|null
    {
        return $this->convertDateObject(
            $this->expiry,
            $asMongo
        );
    }

    /**
     * Set the expiry date of this retained data model. Use null to remove the expiry date entirely.
     *
     * @param DateTime|UTCDateTime|null $expiry
     * @return $this
     */
    public function setRetentionExpiry(DateTime|UTCDateTime|null $expiry): static
    {
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRetentionExpired(): bool
    {
        if ($this->getRetentionExpiry() === null) {
            return false;
        }
        return Date::validateObject(
            $this->getRetentionExpiry()
        )->isOlderThan(
            Date::nowUtc()
        );
    }

    /**
     * @param MondocAbstractModel $model
     * @return void
     * @throws MondocConfigConfigurationException
     */
    public function setSourceModel(MondocAbstractModel $model): void
    {
        $this->setSourceModelData($model->asArray(true));
        $this->setSourceObjectId($model->getObjectId());
        $this->setSourceClassName(get_class($model));
        /* @var $model MondocRetentionTrait (for suppression of IDE warnings) */
        $this->setRetentionData($model->getMondocRetentionChangeMeta());
        $this->setRetentionExpiry($model->getMondocRetentionExpiry());
        /* @var $model MondocAbstractModel (for suppression of IDE warnings) */
        $this->setCreatedDate(Date::nowUtc());
    }
}
