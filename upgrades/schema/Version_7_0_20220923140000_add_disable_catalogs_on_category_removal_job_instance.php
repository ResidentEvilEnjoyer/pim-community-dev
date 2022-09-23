<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version_7_0_20220923140000_add_disable_catalogs_on_category_removal_job_instance extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Adds the disable_catalogs_on_category_removal job instance';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            <<<SQL
            INSERT INTO akeneo_batch_job_instance (code, label, job_name, status, connector, raw_parameters, type)
            VALUES (:code, :label, :job_name, :status, :connector, :raw_parameters, :type)
            ON DUPLICATE KEY UPDATE code = code;
            SQL,
            [
                'code' => 'disable_catalogs_on_category_removal',
                'label' => 'Disable catalogs on category removal',
                'job_name' => 'disable_catalogs_on_category_removal',
                'status' => 0,
                'connector' => 'internal',
                'raw_parameters' => 'a:0:{}',
                'type' => 'disable_catalogs_on_category_removal',
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException();
    }
}
