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

namespace District5Tests\MondocTests\TestObjects\Model;

use DateTime;
use District5\Mondoc\Db\Model\MondocAbstractModel;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Model\BSONArray;
use MongoDB\Model\BSONDocument;
use stdClass;

/**
 * Class AllTypesModel
 *
 * @package District5Tests\MondocTests\TestObjects\Model
 */
class AllTypesModel extends MondocAbstractModel
{
    protected ObjectId|null $anId = null;
    protected string|null $string = null;
    protected int|null $int = null;
    protected mixed $null = null;
    protected float|null $float = null;
    protected bool|null $bool = null;
    protected object|null $object = null;
    protected DateTime|UTCDateTime|null $phpDate = null;
    protected UTCDateTime|DateTime|null $mongoDate = null;
    protected array|BSONArray|null $phpArray = [];
    protected BSONArray|array $bsonArray = [];
    protected BSONDocument|stdClass|array|null $phpObject = null;
    protected BSONDocument|stdClass|array|null $bsonObject = null;

    public function setAnId($v): void
    {
        $this->anId = $v;
        $this->addDirty('anId');
    }

    /**
     * This is just to expose the protected method and avoid reflection.
     *
     * @param array $data
     * @return array
     */
    public function exposeConvertArrayOfMongoIdsToMongoIds(array $data): array
    {
        return $this->convertArrayOfMongoIdsToMongoIds($data);
    }

    public function setString($v): void
    {
        $this->string = $v;
        $this->addDirty('string');
    }

    public function setInt($v): void
    {
        $this->int = $v;
        $this->addDirty('int');
    }

    public function setNull($v): void
    {
        $this->null = $v;
        $this->addDirty('null');
    }

    public function setFloat($v): void
    {
        $this->float = $v;
        $this->addDirty('float');
    }

    public function setBool($v): void
    {
        $this->bool = $v;
        $this->addDirty('bool');
    }

    public function setPhpArray($v): void
    {
        $this->phpArray = $v;
        $this->addDirty('phpArray');
    }

    public function setPhpDate($v): void
    {
        $this->phpDate = $v;
        $this->addDirty('phpDate');
    }

    public function setMongoDate($v): void
    {
        $this->mongoDate = $v;
        $this->addDirty('mongoDate');
    }

    public function setBsonArray($v): void
    {
        $this->bsonArray = $v;
        $this->addDirty('bsonArray');
    }

    public function setPhpObject($v): void
    {
        $this->phpObject = $v;
        $this->addDirty('phpObject');
    }

    public function setBsonObject($v): void
    {
        $this->bsonObject = $v;
        $this->addDirty('bsonObject');
    }
}
