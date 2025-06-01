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
    protected array|null $phpArray = [];
    protected BSONArray|array $bsonArray = [];
    protected BSONDocument|stdClass|array|null $phpObject = null;
    protected BSONDocument|stdClass|array|null $bsonObject = null;

    public function setAnId($v): void
    {
        $this->anId = $v;
    }

    public function setString($v): void
    {
        $this->string = $v;
    }

    public function setInt($v): void
    {
        $this->int = $v;
    }

    public function setNull($v): void
    {
        $this->null = $v;
    }

    public function setFloat($v): void
    {
        $this->float = $v;
    }

    public function setBool($v): void
    {
        $this->bool = $v;
    }

    public function setPhpArray($v): void
    {
        $this->phpArray = $v;
    }

    public function setPhpDate($v): void
    {
        $this->phpDate = $v;
    }

    public function setMongoDate($v): void
    {
        $this->mongoDate = $v;
    }

    public function setBsonArray($v): void
    {
        $this->bsonArray = $v;
    }

    public function setPhpObject($v): void
    {
        $this->phpObject = $v;
    }

    public function setBsonObject($v): void
    {
        $this->bsonObject = $v;
    }
}
