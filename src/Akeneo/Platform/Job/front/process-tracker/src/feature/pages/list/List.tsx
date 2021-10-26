import React from 'react';
import {Breadcrumb} from 'akeneo-design-system';
import {useTranslate, useRoute, PimView, PageHeader} from '@akeneo-pim-community/shared';
import {useJobSearchResult} from '../../hooks/useJobSearchResult';

const List = () => {
  const translate = useTranslate();
  const jobSearchResult = useJobSearchResult();
  const activityHref = useRoute('pim_dashboard_index');
  const jobMatchesCount = jobSearchResult === null ? 0 : jobSearchResult.matches_count;

  return (
    <>
      <PageHeader showPlaceholder={null === jobSearchResult}>
        <PageHeader.Breadcrumb>
          <Breadcrumb>
            <Breadcrumb.Step href={`#${activityHref}`}>{translate('pim_menu.tab.activity')}</Breadcrumb.Step>
            <Breadcrumb.Step>{translate('pim_menu.item.job_tracker')}</Breadcrumb.Step>
          </Breadcrumb>
        </PageHeader.Breadcrumb>
        <PageHeader.UserActions>
          <PimView
            className="AknTitleContainer-userMenuContainer AknTitleContainer-userMenu"
            viewName="pim-process-index-user-navigation"
          />
        </PageHeader.UserActions>
        <PageHeader.Title>
          {translate(
            'pim_enrich.entity.job_execution.page_title.index',
            {count: jobMatchesCount.toString()},
            jobMatchesCount
          )}
        </PageHeader.Title>
      </PageHeader>
    </>
  );
};

export {List};
