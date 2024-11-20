<?php
namespace District5Tests\MondocTests\TestObjects;

use District5Tests\MondocTests\TestObjects\Model\Subs\FoodSubModel;

class FlexibleControlTestConfigSingleton
{
    private static ?self $instance = null;

    private string $className;

    private function __construct()
    {
        $this->className = FoodSubModel::class;
    }

    public function setClassName(string $className): self
    {
        $this->className = $className;

        return $this;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}
