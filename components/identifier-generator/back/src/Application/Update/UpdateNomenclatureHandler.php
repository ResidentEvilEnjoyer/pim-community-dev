<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\CommandValidatorInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\ReferenceEntityProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UpdateNomenclatureHandler
{
    public function __construct(
        private readonly FamilyNomenclatureRepository $familyNomenclatureRepository,
        private readonly SimpleSelectNomenclatureRepository $simpleSelectNomenclatureRepository,
        private readonly ReferenceEntityNomenclatureRepository $refenceEntityNomenclatureRepository,
        private readonly CommandValidatorInterface $validator,
    ) {
    }

    public function __invoke(UpdateNomenclatureCommand $command): void
    {
        $this->validator->validate($command);

        $nomenclatureDefinition = match ($command->getPropertyCode()) {
            FamilyProperty::TYPE => $this->familyNomenclatureRepository->get() ?? new NomenclatureDefinition(),
            ReferenceEntityProperty::TYPE => $this->refenceEntityNomenclatureRepository->get($command->getPropertyCode()) ?? new NomenclatureDefinition(),
            default => $this->simpleSelectNomenclatureRepository->get($command->getPropertyCode()) ?? new NomenclatureDefinition(),
        };

        Assert::notNull($command->getOperator());
        Assert::notNull($command->getValue());
        Assert::notNull($command->getGenerateIfEmpty());

        $nomenclatureDefinition = $nomenclatureDefinition
            ->withOperator($command->getOperator())
            ->withValue($command->getValue())
            ->withGenerateIfEmpty($command->getGenerateIfEmpty())
            ->withValues($command->getValues());

        switch ($command->getPropertyCode()) {
            case FamilyProperty::TYPE:
                $this->familyNomenclatureRepository->update($nomenclatureDefinition);
                break;
            case ReferenceEntityProperty::TYPE:
                $this->refenceEntityNomenclatureRepository->update($command->getPropertyCode(), $nomenclatureDefinition);
                break;
            default:
                $this->simpleSelectNomenclatureRepository->update($command->getPropertyCode(), $nomenclatureDefinition);
        }
    }
}
