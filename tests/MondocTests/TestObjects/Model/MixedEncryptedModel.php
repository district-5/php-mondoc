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
use District5Tests\MondocTests\TestObjects\Model\Subs\PersonalDataModel;

/**
 * Class MixedEncryptedModel.
 *
 * @package District5Tests\MondocTests\TestObjects\Model
 */
class MixedEncryptedModel extends MondocAbstractModel
{
    /**
     * @var string
     */
    protected string $firstName;

    /**
     * @var string
     */
    protected string $lastName;

    /**
     * @var DateTime
     */
    protected DateTime $dateOfBirth;

    /**
     * @var string
     */
    protected string $socialSecurityNumber;

    /**
     * @var PersonalDataModel|null
     */
    protected PersonalDataModel|null $subModel = null;

    /**
     * @var string[]
     */
    protected array $mondocEncrypted = [
        'dateOfBirth',
        'socialSecurityNumber',
        //'subModel'
    ];

    protected function getMondocNestedModelMap(): array
    {
        return [
            'subModel' => PersonalDataModel::class,
        ];
    }

    /**
     * @return PersonalDataModel
     */
    public function getSubModel(): PersonalDataModel
    {
        if (null === $this->subModel) {
            $this->subModel = new PersonalDataModel();
        }

        return $this->subModel;
    }

    /**
     * @param PersonalDataModel $subModel
     * @return $this
     */
    public function setSubModel(PersonalDataModel $subModel): static
    {
        $this->subModel = $subModel;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getDateOfBirth(): DateTime
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(DateTime $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getSocialSecurityNumber(): string
    {
        return $this->socialSecurityNumber;
    }

    public function setSocialSecurityNumber(string $socialSecurityNumber): static
    {
        $this->socialSecurityNumber = $socialSecurityNumber;

        return $this;
    }
}
