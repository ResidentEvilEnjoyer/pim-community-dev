<?php


namespace Akeneo\Tool\Bundle\LoggingBundle\Domain\Model;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CustomReflectionAttribute
{
    private string $name;
    private array $arguments;
    public function __construct(\ReflectionAttribute $reflectionAttribute)
    {
        $this->name = $reflectionAttribute->getName();
        $this->arguments=$reflectionAttribute->getArguments();
    }
}