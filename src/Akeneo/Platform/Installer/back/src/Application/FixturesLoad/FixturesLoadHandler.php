<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\FixturesLoad;

use _PHPStan_0f7d3d695\Symfony\Component\Console\Output\BufferedOutput;
use Akeneo\Platform\Installer\Domain\CommandExecutor\AkeneoBatchJobInterface;
use Akeneo\Platform\Installer\Domain\FixtureLoad\FixturePathResolver;
use Akeneo\Platform\Installer\Domain\FixtureLoad\JobInstanceConfigurator;
use Akeneo\Platform\Installer\Domain\FixtureLoad\JobOrderer;
use Akeneo\Platform\Installer\Domain\Query\Sql\RemoveJobInstanceInterface;
use Akeneo\Platform\Installer\Domain\Query\Yaml\ReadJobDefinitionInterface;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvent;
use Akeneo\Platform\Installer\Infrastructure\Event\InstallerEvents;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class FixturesLoadHandler
{
    /**
     * @param string[] $bundles
     * @param string[] $jobsFilePaths
     */
    public function __construct(
        private readonly AkeneoBatchJobInterface $akeneoBatchJob,
        private readonly BulkSaverInterface $jobInstanceSaver,
        private readonly ReadJobDefinitionInterface $readJobDefinition,
        private readonly RemoveJobInstanceInterface $removeJobInstance,
        private readonly ItemProcessorInterface $jobProcessor,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly array $bundles,
        private readonly array $jobsFilePaths,
    ) {
    }

    public function handle(FixtureLoadCommand $command): void
    {
        $io = $command->getIo();

        $io->title('Load fixture');

        $this->eventDispatcher->dispatch(
            new InstallerEvent(null, [
                'catalog' => $command->getOption('catalog'),
            ]),
            InstallerEvents::PRE_LOAD_FIXTURES,
        );

        // TODO check public api to create job instance
        $jobInstances = $this->createJobInstances($io, $command->getOptions());

        $this->loadFixtures($jobInstances, $io, $command->getOptions());

        $this->cleanJobInstances($io);

        $this->eventDispatcher->dispatch(
            new InstallerEvent(null, [
                'catalog' => $command->getOption('catalog'),
            ]),
            InstallerEvents::POST_LOAD_FIXTURES,
        );
    }

    /**
     * @param string[] $options
     *
     * @return JobInstance[]
     *
     * @throws InvalidItemException
     */
    private function createJobInstances(SymfonyStyle $io, array $options): array
    {
        $io->info(sprintf('Load jobs for fixtures. (data set: %s)', $options['catalog']));

        $normalizedJobs = [];
        $jobInstances = [];

        foreach ($this->jobsFilePaths as $jobsFilePath) {
            $normalizedJobs = $this->readJobDefinition->read($jobsFilePath);
            JobOrderer::order($normalizedJobs);

            foreach ($normalizedJobs as $normalizedJob) {
                unset($normalizedJob['order']);
                $jobInstances[] = $this->jobProcessor->process($normalizedJob);
            }
        }

        $installerPathData = FixturePathResolver::resolve($options['catalog'], $this->bundles);
        $configuredJobInstances = JobInstanceConfigurator::configure($installerPathData, $jobInstances);
        $this->jobInstanceSaver->saveAll($configuredJobInstances, ['is_installation' => true]);

        return $configuredJobInstances;
    }

    /**
     * @param JobInstance[] $configuredJobInstances
     * @param string[] $options
     */
    private function loadFixtures(array $configuredJobInstances, SymfonyStyle $io, array $options): void
    {
        foreach ($configuredJobInstances as $jobInstance) {
            $params = [
                'code' => $jobInstance->getCode(),
                '--no-debug' => true,
                '--no-log' => true,
                '-v' => true,
            ];

            $this->eventDispatcher->dispatch(
                new InstallerEvent($jobInstance->getCode(), [
                    'catalog' => $options['catalog'],
                ]),
                InstallerEvents::PRE_LOAD_FIXTURE,
            );

            $io->info(
                sprintf('Please wait, the "%s" are processing...', $jobInstance->getCode()),
            );

            /** @var BufferedOutput $output */
            $output = $this->akeneoBatchJob->execute($params, true);
            $io->success($output->fetch());
            //TODO listen in Job context for job deletion
            $this->eventDispatcher->dispatch(
                new InstallerEvent($jobInstance->getCode(), [
                    'job_name' => $jobInstance->getJobName(),
                    'catalog' => $options['catalog'],
                ]),
                InstallerEvents::POST_LOAD_FIXTURE,
            );
        }
    }

    private function cleanJobInstances(SymfonyStyle $io): void
    {
        $io->info('Start removing fixtures job instance');
        $this->removeJobInstance->remove();
    }
}
